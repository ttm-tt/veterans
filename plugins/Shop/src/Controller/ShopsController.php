<?php
namespace Shop\Controller;

use App\Model\Table\TypesTable;
use App\Model\Table\GroupsTable;
use App\Model\Table\UsersTable;

use Shop\Model\Table\OrderStatusTable;

use Cake\Mailer\Email;
use CakePdf\Pdf\CakePdf;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use Cake\I18n\FrozenDate;
use Cake\I18n\I18n;

use GeoIp2\Database\Reader;

class ShopsController extends ShopAppController {

	public function initialize() : void {
		parent::initialize();
		
		$this->loadComponent('Shop.Cart');
		$this->loadComponent('WelcomeMail');
		$this->loadComponent('RegistrationUpdate');
		$this->loadComponent('Shop.OrderUpdate');
		
		$this->loadComponent('Wizard.Wizard');
		// TODO: The following as parameter to the above call
		$this->Wizard->cancelUrl = '/register';
		$this->Wizard->autoAdvance = false;
		$this->Wizard->steps = array(
			'people', 
			['ITEMS' => ['buy']], 
			'address', 
			'review', 
			array(
				'PEND' => array(
					'payment_selection',
					array(
						'cc' => array('creditcard'),
						'bt' => array('banktransfer', 'success')
					),
				),
				'WAIT' => array('waiting_list', 'success')
			)
		);		
	}
	
	public function beforeFilter(EventInterface $event) {
		// Security settings must come before parent::beforeFilter,
		$this->Auth->allow();
		
		// TODO: Remove from ShopsController
		$this->Auth->deny([
			'import',
			'setDelayed',
			'setPaid',
			'setPending'
		]);
		
		if (!Configure::read('debug'))
			$this->Auth->deny(['testPayment']);

		$this->Security->setConfig('unlockedActions', [
			'onAddItem',
			'onRemoveItem',
			'onChangeQuantity',
			'onPrepareCreditcard',
			'payment_complete',
			'payment_error',
			'payment_success',
			'testPayment',
			'wizard',
			'add_person',
			'remove_person',
			'count_participants',
			'unsubscribe'
		]);
				
		parent::beforeFilter($event);	
		
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->loadModel('Tournaments');
			// Table::field returns the id of the first record
			$this->request->getSession()->write('Tournaments.id', $this->Tournaments->fieldByConditions('id'));
		}
	}
	
	// Called by the wizard if processing is cancelled
	public function _beforeCancel($expectedStep = null) {
		$this->loadModel('Shop.OrderStatus');
		$this->loadModel('Shop.Orders');

		$oldOrderId = $this->Cart->getOrderId();
		
		if (!empty($oldOrderId)) {
			$order = $this->Orders->get($oldOrderId);
			$status = $order->order_status_id;
			$payment = $order->payment_method;
			if (empty($payment) && $status == OrderStatusTable::getPendingId())
				$this->Orders->save($this->Orders->patchEntity($order, [
					'order_status_id' => OrderStatusTable::getCancelledId()
				]));
		}
		
		$this->Wizard->reset();
		$this->Cart->clear();
		
		return true;
	}
	
	
	public function unsubscribe($email = null) {
		if ($email === null)
			$email = $this->request->getQuery('email');
		
		$this->set('email', $email);
		
		if ($email !== null) {
			$this->loadModel('Users');
			$this->loadModel('People');
			
			$this->Users->updateAll(
					['newsletter' => false],
					['email' => $email]
			);
			$this->People->updateAll(
					['newsletter' => false],
					['email' => $email]
			);
			
			$this->MultipleFlash->setFlash(__('Unsubscribe successful'), 'success');
		}			
	}


	public function wizard($step = null) {
		return $this->Wizard->process($step);
	}


	public function add_person() {
		if ($this->request->getData('cancel') !== null) {
			return $this->redirect(array('action' => 'wizard'));
		} 
		
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$this->loadModel('Tournaments');
		$tournament = $this->Tournaments->get($tid, ['contain' => ['Organizers']]);
		$enter_before = $tournament->url;
		
		$this->loadModel('Competitions');
		$maxYear = $this->Competitions->fieldByConditions(
			'born', 
			array('tournament_id' => $tid), 
			['order' => ['born' =>  'DESC']]
		);
		
		$this->loadModel('Nations');

		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'description'), 
			'conditions' => ['enabled' => 1],
			'order' => ['description' => 'ASC']
		))->toArray());
		
		$this->loadModel('Shop.Articles');
					
		$available_from = $this->Articles->fieldByConditions('available_from', [
			'tournament_id' => $tid,
			'name' => 'PLA'
		]);

		$available_until = $this->Articles->fieldByConditions('available_until', [
			'tournament_id' => $tid,
			'name' => 'PLA'
		]);

		$articleList = Hash::combine($this->Articles->find('all', array(
			'conditions' => ['tournament_id' => $tid]
		))->toArray(), '{n}.name', '{n}');
				
		$types = array();
		if (isset($articleList['PLA']))
			$types['PLA'] = __d('user', 'Player');
		if (isset($articleList['ACC']))
			$types['ACC'] = __d('user', 'Accompanying Person');
		if (isset($articleList['COA']))
			$types['COA'] = __d('user', 'Coach');
		

		// Calculate what is overbooked
		$waiting = $this->_calculateWaiting();
		
		// We need also the count
		$this->loadModel('Shop.OrderArticles');
		
		$waitingCount = Hash::combine($this->OrderArticles->find('all', array(
			'contain' => array('Orders', 'Articles'),
			'fields' => array('count' => 'SUM(OrderArticles.quantity)', 'Articles.name'),
			'group' => array('Articles.name'),
			'conditions' => array(
				'OrderArticles.cancelled IS NULL',
				'Orders.order_status_id' => OrderStatusTable::getWaitingListId()
			)
		))->toArray(), '{n}.article.name', '{n}.count');
		
		// Make sure we have an entry for PLA and ACC
		$waitingCount += ['PLA' => 0, 'ACC' => 0, 'COA' => 0];
		
		if (!UsersTable::hasRootPrivileges($this->_user)) {
			if (!empty($enter_before) && $enter_before < date('Y-m-d')) {
				unset($types['PLA']);
				$waiting['PLA'] = 0;
				
				$this->MultipleFlash->setFlash(
					__d('user', 'You cannot register players after registration closed for players on {0}', $enter_before->format('jS F Y')), 'info'
				);
			} else if (!empty($available_from && $available_from > date('Y-m-d'))) {
				unset($types['PLA']);
				$waiting['PLA'] = 0;

				if ($available_from > date('Y-m-d', strtotime('+4 weeks')))
					$this->MultipleFlash->setFlash(
						__d('user', 
							"You cannot register players at the moment. Registration will open soon. Please visit <a href=\"{0}\" target=\"_blank\">{0}</a> for more information.", $tournament->organizer->url), 'info'
					);
				else 
					$this->MultipleFlash->setFlash(
						__d('user', 'You cannot register players before registration opens for players on {0}', $available_from->format('jS F Y')), 'info'
					);
			} else if (!empty($available_until && $available_until < date('Y-m-d'))) {
				unset($types['PLA']);
				$waiting['PLA'] = 0;

				$this->MultipleFlash->setFlash(
					__d('user', 'You cannot register players after registration closed for players on {0}', $available_until->format('jS F Y')), 'info'
				);
			}
		}		
		
		// If players are no longer available $waiting['PLA'] will be empty		
		if (isset($types['PLA']) && $waiting['PLA'] && !empty($articleList['PLA']['waitinglist_limit_enabled'])) {
			$playerArticle = $articleList['PLA'];
			if ($playerArticle['waitinglist_limit_enabled'] && $waitingCount['PLA'] >= $playerArticle['waitinglist_limit_max']) {
				$this->MultipleFlash->setFlash(
					__d('user', 'The registration for players has been closed'), 'warning'
				);
				
				unset($types['PLA']);
			}
		} 
		
		if (isset($types['PLA']) && $waiting['PLA']) {
			$this->MultipleFlash->setFlash(
				__d('user', 'Players are already sold out and adding more will put your registration on the waiting list'), 'warning'
			);
		}
		
		// Dto. for ACC, but we don't evaluate a limit here
		if (isset($types['ACC']) && $waiting['ACC'] && !empty($articleList['ACC']['waitinglist_limit_enabled'])) {
			$accArticle = $articleList['ACC'];
			if ($accArticle['waitinglist_limit_enabled'] && $waitingCount['ACC'] >= $accArticle['waitinglist_limit_max']) {
				$this->MultipleFlash->setFlash(
					__d('user', 'The registration for accompanying persons has been closed'), 'warning'
				);
				
				unset($types['ACC']);
			}
		} 
		
		if (isset($types['ACC']) && !empty($waiting['ACC'])) {
			$this->MultipleFlash->setFlash(
				__d('user', 'Accompanying persons are already sold out and adding more will put your registration on the waiting list'), 'warning'
			);
		}
		
		// Dto. for COA, but we don't evaluate a limit here
		if (isset($types['COA']) && $waiting['COA'] && !empty($articleList['COA']['waitinglist_limit_enabled'])) {
			$coaArticle = $articleList['COA'];
			if ($coaArticle['waitinglist_limit_enabled'] && $waitingCount['COA'] >= $coaArticle['waitinglist_limit_max']) {
				$this->MultipleFlash->setFlash(
					__d('user', 'The registration for accompanying persons has been closed'), 'warning'
				);
				
				unset($types['COA']);
			}
		} 
		
		if (isset($types['COA']) && !empty($waiting['COA'])) {
			$this->MultipleFlash->setFlash(
				__d('user', 'Coaches are already sold out and adding more will put your registration on the waiting list'), 'warning'
			);
		}
		
		$this->set('types', $types);
		
		$this->set('waiting', $waiting);

		if ($maxYear > 0)
			$this->set('maxYear', $maxYear);

		// Set variants
		$variants = array();
		$this->loadModel('Shop.ArticleVariants');
		$variants['PLA'] = $this->ArticleVariants->find('list', array(
			'keyField' => 'id', 
			'valueField' =>	'description',
			'groupField' =>	'variant_type',
			'contain' => array('Articles'),
			'conditions' => array(
				'ArticleVariants.visible' => true,
				'Articles.name' => 'PLA',
				'Articles.tournament_id' => $tid
			),
			'order' => array('ArticleVariants.sort_order' => 'ASC'),
			'fields' => array(
				'ArticleVariants.id',
				'ArticleVariants.description',
				'ArticleVariants.variant_type'
			)
		))->toArray();

		$variants['ACC'] = $this->ArticleVariants->find('list', array(
			'keyField' => 'id', 
			'valueField' =>	'description',
			'groupField' =>	'variant_type',
			'contain' => array('Articles'),
			'conditions' => array(
				'ArticleVariants.visible' => true,
				'Articles.name' => 'ACC',
				'Articles.tournament_id' => $tid
			),
			'order' => array('ArticleVariants.sort_order' => 'ASC'),
			'fields' => array(
				'ArticleVariants.id',
				'ArticleVariants.description',
				'ArticleVariants.variant_type'
			)
		))->toArray();

		$variants['COA'] = $this->ArticleVariants->find('list', array(
			'keyField' => 'id', 
			'valueField' =>	'description',
			'groupField' =>	'variant_type',
			'contain' => array('Articles'),
			'conditions' => array(
				'ArticleVariants.visible' => true,
				'Articles.name' => 'COA',
				'Articles.tournament_id' => $tid
			),
			'order' => array('ArticleVariants.sort_order' => 'ASC'),
			'fields' => array(
				'ArticleVariants.id',
				'ArticleVariants.description',
				'ArticleVariants.variant_type'
			)
		))->toArray();

		$this->set('variants', $variants);
		
		$cc = '';
		
		// $this->set('countryCode', geoip_country_code_by_name($_SERVER['REMOTE_ADDR']));
		if (file_exists('/usr/local/share/GeoIP/GeoLite2-Country.mmdb'))
			$reader = new Reader('/usr/local/share/GeoIP/GeoLite2-Country.mmdb');
		else if (file_exists('/usr/share/GeoIP/GeoLite2-Country.mmdb'))
			$reader = new Reader('/usr/share/GeoIP/GeoLite2-Country.mmdb');
		else
			$reader = null;
		if ($reader)
		{
			try {
				$cc = $reader->country($_SERVER['REMOTE_ADDR'])->country->isoCode;
				$reader->close();
			} catch (\Exception $ex) {

			}
		}
		
		$this->set('countryCode', $cc);

		$this->loadModel('Shop.Countries');
		$this->set('countryCodes', $this->Countries->find('list', array(
			'fields' => array('id', 'iso_code_2'),
			'order' => array('iso_code_2' => 'ASC')
		))->toArray());
		
		if ($this->request->is(['put', 'post'])) {
			$person = $this->request->getData();
		
			// We don't need Submit, _Token
			unset($person['Submit']);
			unset($person['_Token']);

			if ( empty($person['first_name']) || empty($person['last_name']) ||
				 empty($person['sex']) || empty($person['nation_id']) ) {
				$this->MultipleFlash->setFlash(__d('user', 'You must fill out all required fields'), 'error');
				return;
			}
			
			if (is_array($person['dob'])) {
				$person['dob'] = 
						$person['dob']['year'] . '-' . $person['dob']['month'] . '-' . $person['dob']['day'];
			}
				
			if ($person['type'] == 'PLA') {
				if (empty($person['dob']) || $person['dob'] === '---') {
					$this->MultipleFlash->setFlash(__d('user', 'You must enter the date of birth for players'), 'error');
					return;
				}
				
				if (strtotime($person['dob']) === false) {
					$this->MultipleFlash->setFlash(__d('user', 'The date of birth is not valid'), 'error');
					return;					
				}
				
				if ($maxYear > 0 && date('Y', strtotime($person['dob'])) > $maxYear) {
					$this->MultipleFlash->setFlash(sprintf(__d('user', 'You must be born in %d or earlier'), $maxYear), 'error');
					// return $this->redirect(array('action' => 'wizard'));
					return;
				}
				
				if (date('Y') - date('Y', strtotime($person['dob'])) > 140) {
					$this->MultipleFlash->setFlash(__d('user', 'Wrong birthday given'), 'error');
					// return $this->redirect(array('action' => 'wizard'));
					return;
				}
				
				// Check para settings
				if (($person['is_para'] ?? 0) == 0) {
					$person['ptt_class'] = 0;
					$person['wchc'] = 0;
				}
				
				if ($person['ptt_class'] > 10) {
					$person['ptt_class'] = 10;
					$person['wchc'] = 0;
				} else if ($person['ptt_class'] > 5) {
					$person['wchc'] = 0;
				} else if ($person['ptt_class'] > 0) {
					if ($person['wchc'] == 0) {
						$this->MultipleFlash->setFlash(__('You must select the wheel chair requirement'));
						return;
					}
				} else {
					$person['ptt_class'] = 0;
					$person['wchc'] = 0;
				}
			} else {
				// Not player, reset some fields
				unset($person['dob']);
				unset($person['ptt_class']);
				unset($person['wchc']);
			}
			
			$person['phone'] = str_replace(" ()./", "", $person['phone']);
			
			if ( (!empty($person['email']) || !empty($person['phone'])) && !$person['privacy'] ) {
				$this->MultipleFlash->setFlash(__d('user', 'You must agree to the privacy policy when entering an email address or a phone number'), 'error');
				// return $this->redirect(array('action' => 'wizard'));
				return;
			}
			
			if ($person['type'] != 'PLA') {
				$person['dob'] = null;
			}
			
			$naName = $this->Nations->fieldByConditions('name', ['id' => $person['nation_id']]);
			
			// Add discount for para players, if no other is set
			if (($person['ptt_class'] ?? 0) > 0 && empty($person['variant_id'])) {
				// Nation-specific para variant
				$person['variant_id'] = 
						$this->ArticleVariants->fieldByConditions('id', [
								'article_id' => $articleList[$person['type']]['id'],
								'variant_type' => 'Para',
								'name' => 'PARA-' . $naName
							])
				;
				
				// General para variant
				if ($person['variant_id'] === null)
					$person['variant_id'] = 
							$this->ArticleVariants->fieldByConditions('id', [
									'article_id' => $articleList[$person['type']]['id'],
									'variant_type' => 'Para',
									'name' => 'PARA'
								])
					;
			}
			
			// Add discount for nationality, if no other is set
			if (empty($person['variant_id'])) {
				$person['variant_id'] = 
						$this->ArticleVariants->fieldByConditions('id', [
								'article_id' => $articleList[$person['type']]['id'],
								'variant_type' => 'Nationality',
								'name' => $naName
							])
				;
			}
			
			if (!empty($person['phone'])) {
				if (!preg_match('/\+[0-9]+$/ui', $person['phone'])) {
					$this->MultipleFlash->setFlash(__d('user', 'The phone number is invalid'), 'error');
					// return $this->redirect(array('action' => 'wizard'));				
					return;
				}
			}
			
			if (!empty($person['email'])) {
				if (!filter_var($person['email'], FILTER_VALIDATE_EMAIL)) {
					$this->MultipleFlash->setFlash(__d('user', 'The email address has an invalid format'), 'error');					
					// return $this->redirect(array('action' => 'wizard'));				
					return;
				}
			}
				
			$this->Cart->addPerson($person);

			return $this->redirect(array('action' => 'wizard', 'people'));	
		}
		
		$this->loadModel('Competitions');
		$havePara = $this->Competitions->find()
				->where([
					'tournament_id' => $tid,
					'ptt_class > 0'
				]) 
				->count()
			> 0;
		
		$this->set('havePara', $havePara);
	}


	public function remove_person($idx = null) {
		if ($idx !== null) {
			$this->Cart->removePerson($idx);
		}

		return $this->redirect(array('action' => 'wizard', 'people'));
	}


	public function _preparePeople() {
		$this->loadModel('Tournaments');
		$this->loadModel('Competitions');
		$this->loadModel('Nations');
		$this->loadModel('Types');
		$this->loadModel('Users');
		$this->loadModel('Shop.Articles');
		
		if (!$this->request->getSession()->read('Tournaments.id')) {
			$this->MultipleFlash->setFlash(
				__('No tournaments defined yet'), 'error'
			);
			
			return $this->redirect('/');
		}
				
		$tid = $this->request->getSession()->read('Tournaments.id');

		$tournament = $this->Tournaments->get($tid, ['contain' => ['Organizers']]);		
		$enter_before = $tournament->enter_before;	
			
		$available_from = $this->Articles->fieldByConditions('available_from', [
			'tournament_id' => $tid,
			'name' => 'PLA'
		]);
		
		$available_until = $this->Articles->fieldByConditions('available_until', [
			'tournament_id' => $tid,
			'name' => 'PLA'
		]);
		
		if (!UsersTable::hasRootPrivileges($this->_user)) {
			$open_from = $this->_shopSettings['open_from'];
			$open_until = $this->_shopSettings['open_until'];
			if ( !empty($open_from) && $open_from > date('Y-m-d') ) {
				if ($open_from > date('Y-m-d', strtotime('+4 weeks')))
					$this->MultipleFlash->setFlash(
						__d('user', 
							"You cannot register people at the moment. Registration will open soon. Please visit <a href=\"{0}\" target=\"_blank\">{0}</a> for more information.", $tournament->organizer->url), 'info'
					);
				else 
					$this->MultipleFlash->setFlash(
						__d('user', 'You cannot register people before registration opens on {0}', $open_from->format('jS F Y')), 'info'
					);
				
				return $this->redirect('/');				
			} else if ( !empty($open_until) && $open_until < date('Y-m-d') ) {
				$this->MultipleFlash->setFlash(
					__d('user', 'You cannot register people after registration closed on {0}', $open_until->format('jS F Y')), 'info'
				);
				
				return $this->redirect('/');				
			} else if (!empty($enter_before) && $enter_before < date('Y-m-d')) {
				$this->MultipleFlash->setFlash(
				__d('user', 'You cannot register players after registration closed for players on {0}', $enter_before->format('jS F Y')), 'info'
				);
			} else if (!empty($available_from) && $available_from > date('Y-m-d')) {
				if ($available_from > date('Y-m-d', strtotime('+4 weeks')))
					$this->MultipleFlash->setFlash(
						__d('user', 
							"You cannot register players at the moment. Registration will open soon. Please visit <a href=\"{0}\" target=\"_blank\">{0}</a> for more information.", $tournament->organizer->url), 'info'
					);
				else 
					$this->MultipleFlash->setFlash(
					__d('user', 'You cannot register players before registration opens for players on {0}', $available_from->format('jS F Y')), 'info'
					);
			} else if (!empty($available_until) && $available_until < date('Y-m-d')) {
				$this->MultipleFlash->setFlash(
				__d('user', 'You cannot register players after registration closed for players on {0}', $available_until->format('jS F Y')), 'info'
				);
			}
		}

		$tmp = $this->Competitions->find('all', array(
			'fields' => array('id', 'name', 'type_of'),
			'conditions' => array('tournament_id' => $tid)
		));
		
		$competitions = array();
		foreach ($tmp as $t) {
			$competitions[$t['id']] = $t;
		}
		
		$this->set('competitions', $competitions);
		
		$people = $this->Cart->getPeople();
		
		// Find the youngest player here: That player determines the team they will play.
		$teamPerson = null;
		
		foreach ($people as $person) {
			if ($person['type'] != 'PLA')
				continue;
			
			if ($teamPerson == null || $teamPerson['dob'] < $person['dob'])
				$teamPerson = $person;
		}
		
		$this->loadModel('Shop.ArticleVariants');
		$variants = $this->ArticleVariants->find('list', array(
			'fields' => array('id', 'name')
		))->toArray();		

		foreach ($people as $k => $v) {
			if ($v['type'] != 'PLA')
				continue;
			
			$types = array('S', 'D', 'X', 'T');
			if (!empty($v['variant_id'])) {
				if (!empty($variants[$v['variant_id']]))
					$types = explode(',', $variants[$v['variant_id']]);
			}
			
			$people[$k]['single_id'] = in_array('S', $types) ? $this->_selectEvent($v, $tid, 'S') : null;
			$people[$k]['double_id'] = in_array('D', $types) ? $this->_selectEvent($v, $tid, 'D') : null;
			$people[$k]['mixed_id'] = in_array('X', $types) ? $this->_selectEvent($v, $tid, 'X') : null;
			$people[$k]['team_id'] = in_array('T', $types) ? $this->_selectEvent($teamPerson, $tid, 'T') : null;
		}
		
		$this->set('people', $people);
		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'description')
		))->toArray());
	}


	public function _processPeople() {
		$this->loadModel('Shop.Articles');
		$items = 
				$this->Articles->find()
					->where(['visible' => true])
		;
		
		// If no items are available (and never were), skip buy
		if ($items->count() === 0) {
			if (count($this->Cart->getPeople() ?? []	) == 0) {
				$this->MultipleFlash->setFlash(__d('user', 'You have to register people'), 'error');
				return false;
			}
			$this->Wizard->branch('ITEMS', true);
		}
				
		return true;
	}
	
	public function _prepareBuy() {
		$this->loadModel('Shop.OrderStatus');
		$status_id = $this->OrderStatus->fieldByConditions('id', array('name' => 'PAID'));
		
		$this->loadModel('Shop.Articles');
		$articles = $this->Articles->find('all', array(
			'contain' => array(
				'ArticleVariants' => array('sort' => ['sort_order' => 'ASC']),
			),
			'conditions' => array(
				'tournament_id' => $this->request->getSession()->read('Tournaments.id'),
				'visible' => true,
				'OR' => array(
					'available_from IS NULL',
					'available_from <=' => date('Y-m-d')
				),
				'OR' => array(
					'available_until IS NULL',
					'available_until >=' => date('Y-m-d')
				)
			),
			'order' => ['sort_order' => 'ASC']
		));
		
		// Count sold items
		$tid = $this->request->getSession()->read('Tournaments.id');
		$this->loadModel('Shop.OrderArticles');
		$tmp = $this->OrderArticles->find('all', array(
			'fields' => array('Articles.id', 'sold' => 'SUM(OrderArticles.quantity)'),
			'contain' => array(
				'Articles', 'Orders'
			),
			'group' => array('Articles.id'),
			'conditions' => array(
				'Articles.tournament_id' => $tid,
				'Articles.available IS NOT NULL',
				'OrderArticles.cancelled IS NULL',
				'Orders.order_status_id' => $status_id
			)
		));
			
		$sold = Hash::combine($tmp->toArray(), '{n}.Articles.id', '{n}.sold');

		$this->set('articles', Hash::combine($articles->toArray(), '{n}.id', '{n}'));
		
		$this->set('sold', $sold);

		$items = $this->Cart->getItems();

		$this->set('items', $items);	
		
		$people = $this->Cart->getPeople();
		
		$this->set('people', $people);
	}


	public function _processBuy() {		
		$this->loadModel('Shop.Articles');
		$items = 
				$this->Articles->find()
					->where(['visible' => true])
		;
		
		$people = $this->Cart->getPeople();
		$items = $this->Cart->getItems();
		
		if (count($people) === 0 && count($items) === 0) {
				$this->MultipleFlash->setFlash(__d('user', 'You have to register people or buy something'), 'error');
			return false;
		}
		
		return true;
	}


	// Called by "buy.ctp" when an Item is added
	public function onAddItem($key = null, $variant = null) {
		$data = $this->request->getData();
		
		if ($key === null && isset($data['key']))
			$key = $data['key'];
		
		// null may be transfered as 'none'
		if ($key === 'none')
			$key = null;
		
		// key is a required field
		if ($key === null)
			return;
		
		// Get variant id from data if not explicitly given
		if ($variant === null && isset($data['variant']))
			$variant = $data['variant'];

		if ($variant === 'none' || strlen($variant) === 0)
			$variant = null;
		
		if ($variant === null) {
			$this->loadModel('Shop.Articles');
			$newArticle = $this->Articles->find('all', array(
				'conditions' => array(
					'Articles.id' => $key
				)
			))->first();
		} else {
			$this->loadModel('Shop.Articles');
			$newArticle = $this->Articles->find('all', array(
				'contain' => array(
					'ArticleVariants' => array(
						'conditions' => array(
							'ArticleVariants.id' => $variant
						)
					)
				),
				'conditions' => array(
					'Articles.id' => $key
				)
			))->first();
		}

		$this->Cart->addArticle($newArticle->toArray());

		$items = $this->Cart->getItems();

		$this->autoRender = false;
		$this->set('json_object', $items);
		$this->render('json');
	}


	public function onChangeQuantity($key = null, $quantity = null) {
		$data = $this->request->getData();
		
		if ($key === null)
			$key = $data['key'];
		if ($quantity === null)
			$quantity = $data['quantity'];

		if (intval($quantity) <= 0)
			$this->Cart->removeArticle($key);
		else 
			$this->Cart->setQuantity($key, $quantity);

		$items = $this->Cart->getItems();

		$this->autoRender = false;

		$this->set('json_object', $items);
		$this->render('json');
	}


	public function onRemoveItem($key = null) {
		$data = $this->request->getData();
		
		if ($key === null)
			$key = $data['key'];

		// null may be transfered as 'none';
		if ($key === 'none')
			$key = null;
		
		if ($key != null)
			$this->Cart->removeArticle($key);

		$items = $this->Cart->getItems();

		$this->autoRender = false;

		$this->set('json_object', $items);
		$this->render('json');
	}


	public function _prepareAddress() {
		$this->loadModel('Shop.Countries');

		$address = $this->Cart->getAddress();

		$this->set('countries', $this->Countries->find('list', array(
			'fields' => array('id', 'name'),
			'order' => ['name' => 'ASC']
		))->toArray());
		
		$cc = '';
		
		// $this->set('countryCode', geoip_country_code_by_name($_SERVER['REMOTE_ADDR']));
		if (file_exists('/usr/local/share/GeoIP/GeoLite2-Country.mmdb'))
			$reader = new Reader('/usr/local/share/GeoIP/GeoLite2-Country.mmdb');
		else if (file_exists('/usr/share/GeoIP/GeoLite2-Country.mmdb'))
			$reader = new Reader('/usr/share/GeoIP/GeoLite2-Country.mmdb');
		else
			$reader = null;
		if ($reader)
		{
			try {
				$cc = $reader->country($_SERVER['REMOTE_ADDR'])->country->isoCode;
				$reader->close();
			} catch (\Exception $ex) {

			}
		}
		
		$this->set('address', $address);		
		$this->set('countryCode', $cc);
		$this->set('countryCodes', $this->Countries->find('list', array(
			'fields' => array('id', 'iso_code_2'),
			'order' => array('iso_code_2' => 'ASC')
		))->toArray());		
	}


	public function _processAddress() {
		$data = $this->request->getData();
		
		if (empty($data['first_name'])) {
			$this->MultipleFlash->setFlash(__d('user', 'Given name cannot be empty'), 'error');
			return false;
		}
		
		if (empty($data['last_name'])) {
			$this->MultipleFlash->setFlash(__d('user', 'Family name cannot be empty'), 'error');
			return false;
		}
		
		if (empty($data['street'])) {
			$this->MultipleFlash->setFlash(__d('user', 'Street name cannot be empty'), 'error');
			return false;
		}
		
		if (empty($data['zip_code'])) {
			$this->MultipleFlash->setFlash(__d('user', 'Zip code cannot be empty'), 'error');
			return false;
		}
		
		if (empty($data['city'])) {
			$this->MultipleFlash->setFlash(__d('user', 'City cannot be empty'), 'error');
			return false;
		}
		
		if (empty($data['country_id'])) {
			$this->MultipleFlash->setFlash(__d('user', 'Country cannot be empty'), 'error');
			return false;
		}
		
		if (empty($data['email'])) {
			$this->MultipleFlash->setFlash(__d('user', 'Email address cannot be empty'), 'error');
			return false;
		}

		$address = $data;
		
		// We don't need Submit, _Token
		unset($address['Submit']);
		unset($address['_Token']);
		
		$this->Cart->setAddress($address);
		return true;
	}


	public function _prepareReview() {
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$this->set('people', $this->Cart->getPeople());
		$this->set('items', $this->Cart->getItems());
		$this->set('address', $this->Cart->getAddress());

		$this->loadModel('Shop.Countries');
		$this->set('countries', $this->Countries->find('list', array('fields' => array('id', 'name')))->toArray());
		
		$this->loadModel('Types');
		$this->set('types', $this->Types->find('list', array('fields' => array('name', 'description')))->toArray());
		
		$this->loadModel('Nations');
		$this->set('nations', $this->Nations->find('list', array('fields' => array('id', 'description')))->toArray());		
		
		$this->loadModel('Shop.Articles');
		$this->set('articles', Hash::combine($this->Articles->find('all', array(
			'tournament_id' => $tid
		))->toArray(), '{n}.id', '{n}'));
	}


	public function _processReview() {
		$data = $this->request->getData();
		
		if (!$data['agb']) {
			$this->MultipleFlash->setFlash(__d('user', 'You must agree to the terms and conditions'), 'error');
			return false;
		}
		
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$this->loadModel('Shop.Articles');
		$articles = $this->Articles->find('list', array(
			'fields' => array('id', 'name'),
			'conditions' => array('tournament_id' => $tid)
		))->toArray();
		
		// Based on if we are doing a waitig list or a real order, branch.
		// Once one branch was selected we would always got there, even if we decide
		// otherwise later. So skip all branches ...
		$this->Wizard->branch('WAIT', true);
		$this->Wizard->branch('PEND', true);
		
		$waiting = $this->_calculateWaiting();
		$waitingList = false;
		
		$tmp = $this->Cart->getItems();
		foreach ($tmp as $t) {
			$name = $articles[$t['article_id']];
			if (empty($waiting[$name]))
				continue;
			
			$waitingList |= $waiting[$name];
			
		}		
		
		if ($waitingList)
			$this->Wizard->branch('WAIT');
		else
			$this->Wizard->branch('PEND');
				
		return true;
	}
	
	public function _prepareWaitingList() {
		
	}
	
	public function _processWaitingList() {
		$statusId = OrderStatusTable::getWaitingListId();
		
		$orderId = $this->_storeOrder($statusId);
		if (empty($orderId)) {
			// TODO: Fehlermeldung
			return false;
		}
		
		$this->request->getSession()->write('Orders.id', $orderId);
		
		return true;
	}
	
	public function _preparePaymentSelection() {
		$this->set('payment_method', $this->Cart->getPaymentMethod());
		
		// Default logos
		/*
		 * Icon: http://www.m-worx.net/kostenlose-shop-icons-fur-ec-oder-lastschrift-und-uberweisung/
		 */
		$invoiceLogo = 'moneyorder.png';
		
		/*
		 * Icon: http://www.infomerchant.net/creditcardprocessing/credit_card_images.html
		 */		
		$creditcardLogo = 'creditcard.png';
		
		if ($this->_getPayment()->getPaymentLogo() !== null)
			$creditcardLogo = $this->_getPayment()->getPaymentLogo();
		
		$this->set('paymentLogos', array(
			'bt' => $invoiceLogo, 
			'cc' => $creditcardLogo
		));
		
	}
	
	public function _processPaymentSelection() {
		$data = $this->request->getData();
		
		$this->Cart->setPaymentMethod($data['PaymentSelection']['payment_method']);

		// Once the first branch (bt) was selected we would always go there,
		// even if we later choose another branch. So:
		// skip all payment branches ...
		$this->Wizard->branch('bt', true);
		$this->Wizard->branch('cc', true);
		
		// ... and then branch to where we want to go
		$this->Wizard->branch($this->Cart->getPaymentMethod());
		
		return true;
	}


	public function _prepareBanktransfer() {
		
	}
	
	
	public function _processBanktransfer() {
		$orderId = $this->_storeOrder();
		if (empty($orderId)) {
			// TODO: Fehlermeldung
			return false;
		}
		
		$this->request->getSession()->write('Orders.id', $orderId);
		
		return true;
	}
	
	
	public function _prepareSuccess() {
		$this->Wizard->reset();
		$this->Cart->clear();
				
		$order_id = $this->request->getSession()->read('Orders.id');
		$this->_setVarsForOrder($order_id);
		
		$this->_sendConfirmationMail($order_id);		
	}


	public function _prepareCreditcard() {
		$this->autoRender = false;
		
		$amount = $this->Cart->getTotal();
		
		$payment = $this->_getPayment();
		$payment->prepare($amount);
	}
	
	
	public function _processCreditCard() {
		$this->autoRender = false;
		
		$payment = $this->_getPayment();
		$payment->process();
		
	}
	
	public function onPrepareCreditcard() {
		if (!$this->request->is('ajax'))
			return;
		
		$this->autoRender = false;		

		$initStatusId = OrderStatusTable::getInitiateId();
		
		$this->loadModel('Shop.Orders');
		
		if ($this->request->getData('ticket') !== null)
			$orderId = $this->Orders->fieldByConditions('id', ['ticket' => $this->request->getData('ticket')]);
		else
			$orderId = $this->_storeOrder($initStatusId, Configure::read('Shop.payment'));
		
		if (empty($orderId)) {
			// TODO: Fehlermeldung
			return;
		}
		
		$payment = $this->_getPayment();
		$payment->confirm($orderId);
		
		// Don't reset Wizard or clear cart:
		// In case the payment fails the user can go back and try again
	}
	
	
	// Called when transaction was not successful
	// IPayment: silent_error
	public function payment_error() {
		$this->autoRender = false;
		
		$payment = $this->_getPayment();
		$payment->error($this->request);
	}


	// Called when transaction was successful
	// Redirect client to success page
	public function payment_complete() {		
		$this->autoRender = false;
		
		// $request = unserialize(file_get_contents('/home/ettu/Downloads/xxxserialize-20170622-172205'));
		
		$payment = $this->_getPayment();
		$payment->completed($this->request);
	}
	
	public function testPayment() {
		$this->Wizard->reset();
		$this->Cart->clear();
		
		// Add one player
		$this->loadModel('Nations');
		
		$this->Cart->addPerson(array(
			'type' => 'PLA',
			'first_name' => 'Christoph',
			'last_name' => 'Theis',
			'sex' => 'M',
			'nation_id' => $this->Nations->fieldByConditions('id', ['name' => 'GER']),
			'dob' => '1962-06-24',			
		));

		// Add random items
		$this->loadModel('Shop.Articles');
		$article = $this->Articles->find('all', array(
			'conditions' => array(
				'tournament_id' => $this->request->getSession()->read('Tournaments.id'),
				'visible' => 1
			)
		))->first();
		
		if (!empty($article))
			$this->Cart->addArticle($article->toArray());
		
		$this->loadModel('Shop.Countries');
		
		$this->Cart->setAddress(array(
			'type' => 'P',
			'title' => 'Mr',
			'first_name' => 'Christoph',
			'last_name' => 'Theis',
			'street' => 'St. Antonistr. 7',
			'zip_code' => '7000',
			'city' => 'Eisenstadt',
			'country_id' => $this->Countries->fieldByConditions('id', ['iso_code_3' => 'AUT']),
			'email' => 'theis@gmx.at'
		));
		
		$this->set('test', true);
		$this->_getPayment()->prepare($this->Cart->getTotal());
	}
	
	public function test($id = null) {
/*		
		$this->_setupVars($id);
		$this->_sendConfirmationMail($id);
		$this->render('success');* 
 */
/*		
		$html=
			'<html>' .
				'<head>' .
					'<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>' .
					'<style>' .
						'html{padding:-30px;}' .
						'body { font-family: "dejavu sans"; }' .
					'</style>' .
				'</head>' .
				'<body>';
		$html.='ı İ Ş ş ç Ç ö Ö ü Ü ğ Ğ ÿ þ ð ê ß ã Ù Ñ È »  ¿ İsa Şahintürk';
		$html.='</body></html>';
		
		// dompdf has problems with large lists
		ini_set('xdebug.max_nesting_level', -1);
		
		$cakepdf = new CakePdf();
		$pdf = $cakepdf->output($html);
		
		$this->response = $this->response->withType('application/pdf');
		$this->response->body($pdf);
		$this->response->download('test.pdf');
		
		$this->autoRender = false;
		return $this->response;
 */
		$this->render('Shops/Payment/redsystest');
	}
	
	
	private function _sendConfirmationMail($id) {
		$this->_setVarsForOrder($id);

		// Set default values for reminder and waiting list
		if ($this->viewBuilder()->getVar('reminder') === null)
			$this->set('reminder', false);
		if ($this->viewBuilder()->getVar('processWaitingList') === null)
			$this->set('processWaitingList', false);
		if ($this->viewBuilder()->getVar('until') === null)
			$this->set('until', date('Y-m-d', strtotime('+7 days')));
		
		$this->loadModel('Tournaments');
		$this->loadModel('Users');
		$this->loadModel('Shop.Orders');
		$this->loadModel('Shop.OrderSettings');
		
		// $this->set('reminder', $reminder);
		
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$tournament = $this->Tournaments->find('all', array(
			'conditions' => array('Tournaments.id' => $tid)
		))->first();
		$this->set('tournament', $tournament);
		
		$order = $this->Orders->get($id);
		
		$invoice = $order['invoice'];
		$orderDate = $order['created'];
		if (empty($orderDate))
			$orderDate = date('Y-m-d H:i:s');

		// Detect language
		$lang = $order['language'] ?? 'eng';
		
		$oldLang = I18n::getLocale();
		I18n::setLocale($lang);
		
		// dompdf has problems with large lists
		ini_set('xdebug.max_nesting_level', -1);
		
		// And increase the max memory size
		ini_set('memory_limit', '1024M');
		
		// And give lot of time
		set_time_limit(0);
		
		$tname = $tournament['name'];
		
		$replyTo = $this->OrderSettings->fieldByConditions('email', array('tournament_id' => $tid));
		$to = $this->Orders->fieldByConditions('email', array('id' => $id));
		$bcc = $this->Users->fieldByConditions('email', array('username' => 'theis'));
		
		// Send Mail
		$email = new Email('default');
		if (!empty($replyTo))
			$email->setReplyTo($replyTo);

		$email
			->viewBuilder()->setTemplate('Shop.order', 'default');

		$email
			->setEmailFormat('both')
			->addHeaders(array(
				'X-Tournament' => $tournament['name'],
				'X-Type' => 'Order',
				'X-' . $tournament['name'] . '-Type' => 'Order'
			))
			->setTo($to)
			->setBcc($bcc)
			->setSubject(__d('user', '[{0}] Your registration {1} from {2}', $tname, $invoice, date('Y-m-d', strtotime($orderDate))))
			->setViewVars($this->viewBuilder()->getVars())
		;
		
		$wantReceipt = 
				$order['order_status_id'] === OrderStatusTable::getPendingId() ||
				$order['order_status_id'] === OrderStatusTable::getPaidId() ||
				$order['order_status_id'] === OrderStatusTable::getInvoiceId()
		;
			
		if ($wantReceipt) {
			// Receipt as Attachment
			$cakepdf = new CakePdf();
			$cakepdf->template('Shop.receipt');
			$cakepdf->viewVars($this->viewBuilder()->getVars());
			$pdf = $cakepdf->output();
		
			$email->setAttachments(array(
				'receipt.pdf' => array(
					'data' => $pdf,
					'mimetype' => 'application/pdf'
				)
			));
		}

		
		if (!empty($replyTo))
			$email->addBcc($replyTo);
		
		// free.fr returns 550 Spam detected
		if (false && strpos($to, '@free.fr') > 0)
			$email->addBcc('nospam@nospam.proxad.net');
		
		$email->send();		
		
		if (!empty($oldLang))
			I18n::setLocale($oldLang);
	}
	
	
	private function _sendVoucherMail($id) {
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$this->_setVarsForOrder($id);
		
		$this->loadModel('Tournaments');
		$this->loadModel('Registrations');
		$this->loadModel('Shop.Orders');
		$this->loadModel('Shop.OrderArticles');
		$this->loadModel('Shop.OrderSettings');
		
		// Replace people with real people
		$pids = $this->OrderArticles->find('all', array(
			'conditions' => array(
				'order_id' => $id,
				'person_id IS NOT NULL'
			),
			'fields' => array('person_id' => 'DISTINCT(person_id)', 'order_id')
		));
		$registrations = $this->Registrations->find('all', array(
			'contain' => ['People'],
			'conditions' => array(
				'Registrations.tournament_id' => $tid,
				'People.id IN' => Hash::extract($pids->toArray() + [0], '{n}.person_id') + [0]
			)
		));
		
		$this->set('registrations', $registrations);
		
		$tournament = $this->Tournaments->find('all', array(
			'conditions' => array('Tournaments.id' => $tid)
		))->first();
		$this->set('tournament', $tournament);
		
		$order = $this->Orders->find('all', array(
			'conditions' => array('Orders.id' => $id)
		))->first();
		
		$invoice = $order['invoice'];
		
		$lang = $order['language'];
		if (empty($lang))
			$lang = 'eng';
		
		$oldLang = I18n::getLocale();
		I18n::setLocale($lang);
	
		// dompdf has problems with large lists
		ini_set('xdebug.max_nesting_level', -1);
		
		// And increase the max memory size
		ini_set('memory_limit', '1024M');
		
		// And give lot of time
		set_time_limit(60);
		
		// Voucher as Attachment
		$cakepdf = new CakePdf();
		$cakepdf->template('Shop.voucher');
		$cakepdf->viewVars($this->viewBuilder()->getVars());
		$pdf = $cakepdf->output();
		
		$replyTo = $this->OrderSettings->fieldByConditions('email', array('tournament_id' => $tid));
		$tname = $tournament['name'];
		$to = $order['email'];
		
		$this->loadModel('Users');
		$bcc = $this->Users->fieldByConditions('email', array('username' => 'theis'));
		
		// Send Mail
		$email = new Email('default');
		
		if (!empty($replyTo))
			$email->setReplyTo($replyTo);
		
		$email
			->viewBuilder()->setTemplate('Shop.voucher', 'default');
		
		$email
			->setEmailFormat('both')
			->addHeaders(array(
				'X-Tournament' => $tournament['name'],
				'X-Type' => 'Voucher',
				'X-' . $tournament['name'] . '-Type' => 'Voucher'
			))
			->setTo($to)
			->setBcc($bcc)
			->setSubject(__d('user', '[{0}] Your voucher for your registration {1} from {2}', $tname, $invoice, date('Y-m-d', strtotime($order['created']))))
			->setViewVars($this->viewBuilder()->getVars())
			->setAttachments(array(
				'voucher.pdf' => array(
					'data' => $pdf,
					'mimetype' => 'application/pdf'
				)
			))
		;
		
		if (!empty($replyTo))
			$email->addBcc($replyTo);
		
		$email->send();	
		
		if (!empty($oldLang))
			I18n::setLocale($oldLang);
	}
	
	public function viewVoucher($id) {
		$this->_voucher($id, false);
	}
	
	public function sendVoucher($id = null) {
		if (empty($id)) {
			$this->loadModel('Shop.OrderArticles');
			$ids = $this->OrderArticles->find('list', array(
				'fields' => array(
					'order_id', 'id'
				),
				'contain' => array(
					'Orders', 'Articles'
				),
				'conditions' => array(
					'Orders.order_status_id' => OrderStatusTable::getPaidId(),
					'Articles.visible' => true
				),
				'order' => array('OrderArticles.id' => 'ASC')
			))->toArray();

			foreach (array_keys($ids) as $oid) {
				$this->_voucher($oid, true);
			}
			$this->MultipleFlash->setFlash(__('{0} Mails have been sent', count($ids)), 'info');
		} else {
			$this->_voucher($id, true);
			$this->MultipleFlash->setFlash(__('Mail has been sent'), 'info');
		}

		return $this->redirect($this->referer());		
	}
	
	private function _voucher($id, $mail = false) {
		if (empty($id)) {
			$this->MultipleFlash->setFlash(__('Invalid order id given', 'error'));
			return;
		}
		
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$this->_setVarsForOrder($id);
		
		// Replace people with real people
		$this->loadModel('Registrations');
		$this->loadModel('Shop.OrderArticles');
		$pids = $this->OrderArticles->find('all', array(
			'conditions' => array(
				'OrderArticles.person_id IS NOT NULL',
				'OrderArticles.order_id' => $id
			),
			'fields' => array('person_id' => 'DISTINCT(OrderArticles.person_id)', 'OrderArticles.id')
		));
		
		$count = $pids->count();
		$arr = $pids->toArray();

		$registrations = $this->Registrations->find('all', array(
			'contain' => ['People'],
			'conditions' => array(
				'Registrations.tournament_id' => $tid,
				'People.id IN' => Hash::extract($pids->toArray(), '{n}.person_id') + [0]
			)
		));
		
		$this->set('registrations', $registrations);
		
		if ($mail) {
			$this->_sendVoucherMail($id);
			return;
		}
		
		$this->autoRender = false;
		
		// dompdf has problems with large lists
		ini_set('xdebug.max_nesting_level', -1);
		
		if (true) {
			if ($this->request->getParam('_ext') !== false)
				$this->render('/pdf/voucher', 'default');		
			else
				$this->render('/pdf/voucher', 'default');		
	
		} else {
			// Configure::write('debug', 0);
			$this->response = $this->response->withType('application/pdf');
			$this->pdfConfig = array(
				'fullbase' => true,
				'filename' => 'voucher.pdf',
				'download' => true			
			); 

			$this->render('/pdf/voucher', 'default');		
		}
	}
	
	public function viewInvoice($id) {
		$this->_invoice($id, false);
	}
	
	public function sendInvoice($id) {
		$this->_invoice($id, true);		
	}
	
	public function send_reminder($ids = false) {
		if ($this->request->getData('cancel') !== null) {
			return $this->redirect(array('controller' => 'orders', 'action' => 'index'));
		}

		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		}
		
		$this->set('ids', $ids);
		
		if ($this->request->is('get'))
			return;
		
		if (empty($ids)) {
			$tid = $this->request->getSession()->read('Tournaments.id');
			$date = date('Y-m-d 00:00:00', strtotime('-15 days'));
			
			if ($this->request->getData('date')) {
				$date = $this->request->getData('date');
				if ($date && is_array($date))
					$date = new FrozenDate($date['year'] . '-' . $date['month'] . '-' . $date['day']);
			}
			
			$this->loadModel('Shop.Orders');
			$this->loadModel('Shop.OrderArticles');
			
			$conditions = [
				'Orders.tournament_id' => $tid,
				'Orders.order_status_id' => OrderStatusTable::getPendingId(),
				'Orders.created <' => $date				
			];
			
			// Filter by Country
			if ($this->request->getSession()->check('Shop.InvoiceAddresses.country_id'))
				$conditions['InvoiceAddresses.country_id'] = $this->request->getSession()->read('Shop.InvoiceAddresses.country_id');
			
			// Filter by Article
			if ($this->request->getSession()->check('Shop.Articles.id')) {
				$conditions['Orders.id IN'] = 
						$this->OrderArticles->find()
							->select('order_id')
							->distinct()
							->where(['article_id IN' => explode(',', $this->request->getSession()->read('Shop.Articles.id'))])
				;
			}
		
			$tmp = $this->Orders->find('all', array(
				'fields' => array('Orders.id'),
				'contain' => array('InvoiceAddresses'),
				'conditions' => $conditions
			));
			
			$ids = Hash::extract($tmp->toArray(), '{n}.id');
			
			$this->MultipleFlash->setFlash(__('Send reminders to registrations older than {0}', $date), 'info');
		}

		if (!is_array($ids))
			$ids = array($ids);
		
		$this->set('reminder', $this->request->getData('reminder'));
		
		$until = date('Y-m-d', strtotime('+7 days'));
		if ($this->request->getData('until')) {
			$until = $this->request->getData('until');
			// $until was an array and should be a string now
			if (is_array($until))
				$until = $until['year'] . '-' . $until['month'] . '-' . $until['day'];
		}
		
		$this->set('until', $until);
			
		foreach ($ids as $id) {
			// Restart timer
			set_time_limit(60);
			
			$this->_setVarsForOrder($id);	
			$this->_sendConfirmationMail($id);
		}
		
		$this->MultipleFlash->setFlash(__('{0} reminders sent', count($ids)), 'info');
		
		// TODO: return to referer, but that seems to be the configuration page
		if (count($ids) === 1)
			return $this->redirect(['controller' => 'orders', 'action' => 'view', $ids[0]]);
		else
			return $this->redirect(['controller' => 'orders', 'action' => 'index']);
	}
	
	private function _invoice($id, $mail = false) {
		if (empty($id)) {
			$this->MultipleFlash->setFlash(__('Invalid order id given', 'error'));
			return;
		}
		
		$this->_setVarsForOrder($id);
		
		if ($mail) {
			$this->_sendConfirmationMail($id);
			$this->MultipleFlash->setFlash(__('Mail has been sent'), 'info');
			return $this->redirect($this->referer());
		}
		
		$this->autoRender = false;
		
		// dompdf has problems with large lists
		ini_set('xdebug.max_nesting_level', -1);
		
		if (true) {
			if ($this->request->getParam('_ext') !== false)
				$this->render('/pdf/receipt', 'default');		
			else
				$this->render('/pdf/receipt', 'pdf/default');		
				
		} else {
			$this->response = $this->response->withType('application/pdf');
			$this->viewBuilder()->setClassName('CakePdf.Pdf');
			$this->viewBuilder()->setOptions(array('pdfConfig' => [
				'fullbase' => true,
				'filename' => 'invoice.pdf',
				'download' => true			
			])); 
			
			$this->render('/pdf/receipt', 'default');		
		}
	}
	
	private function _setVarsForOrder($id) {
		$this->OrderUpdate->setVarsForOrder($id);
	}
	
	
	// Registration was successful
	public function _success($id) {
		if (empty($id)) {
			$this->MultipleFlash->setFlash(__d('user', 'The order was not found'), 'error');
			return $this->redirect('/register');
		}
		
		// Reset wizard and clear shopping cart
		$this->Wizard->reset();
		$this->Cart->clear();
		
		$this->_setVarsForOrder($id);
		
		$this->autoRender = false;
		
		$this->render('success', 'default');
	}
	
	
	// Registration was not successful
	public function _failure($id, $msg) {
		$this->MultipleFlash->setFlash($msg, 'error');
		
		$this->autoRender = false;
		
		// $this->set('msg', $msg);
		// $this->render('failure', 'default');
		return $this->Wizard->redirect('payment_selection');
	}
	
	
	public function receipt($ticket = null) {	
		if ($ticket === null)
			$ticket = $this->request->getQuery('ticket');
		
		if ($ticket === null) {
			$this->MultipleFlash->setFlash(__('Invalid receipt id given', 'error'));
			return;
		}
		
		$this->loadModel('Shop.Orders');
		
		$id = $this->Orders->fieldByConditions('id', array('ticket' => $ticket));
		if (empty($id)) {
			$this->MultipleFlash->setFlash(__('Invalid receipt id given', 'error'));
			return;
		}
		
		$this->_setVarsForOrder($id);
		
		$this->autoRender = false;
		
		// Configure::write('debug', 0);
		$this->response = $this->response->withType('application/pdf');
		$this->viewBuilder()->setClassName('CakePdf.Pdf');
		$this->viewBuilder()->setOptions(array('pdfConfig' => [
			'fullbase' => true,
			'filename' => 'invoice.pdf',
			'download' => true			
		])); 
		
		$this->render('/pdf/receipt', 'default');		
	}
	
	
	public function pay($ticket = null) {
		if ($this->request->getData('cancel') !== null) {
			return $this->redirect(array('plugin' => false, 'controller' => 'Users', 'action' => 'login'));
		}		
		
		if ($ticket === null)
			$ticket = $this->request->getQuery('ticket');
		
		if ($ticket === null) {
			$this->MultipleFlash->setFlash(__d('user', 'Invalid registration', 'error'));
			return $this->redirect(array('plugin' => false, 'controller' => 'Users', 'action' => 'login'));
		}
		
		$this->loadModel('Shop.Orders');
		
		$order = $this->Orders->find('all', array('conditions' => array('ticket' => $ticket)))->first();
		if (empty($order)) {
			$this->MultipleFlash->setFlash(__d('user', 'Invalid registration', 'error'));
			return $this->redirect(array('plugin' => false, 'controller' => 'Users', 'action' => 'login'));
		}
		
		if ($order['order_status_id'] == OrderStatusTable::getPaidId()) {
			$this->MultipleFlash->setFlash(__d('user', 'The registration was already paid for'), 'error');
			return $this->redirect(array('plugin' => false, 'controller' => 'Users', 'action' => 'login'));			
		}
		
		if (!in_array($order['order_status_id'], [
				OrderStatusTable::getPendingId(),
				OrderStatusTable::getDelayedId(),
				OrderStatusTable::getInvoiceId()
			])) {
			$this->MultipleFlash->setFlash(__d('user', 'You cannot pay for this registration at this moment'), 'error');
			return $this->redirect(array('plugin' => false, 'controller' => 'Users', 'action' => 'login'));						
		}

		$payment = $this->_getPayment();
		
		$this->_setVarsForOrder($order->id);
		
		$this->set('amount', $order->outstanding);
		$this->set('paymentName', $payment->getPaymentName());
		$this->set('submitUrl', $payment->getSubmitUrl());
		$this->set('ticket', $ticket);
	}
	
	
	// Set order to "pays later"
	public function setDelayed($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect($this->referer());
		} 
		
		$this->loadModel('Shop.Orders');
		$this->loadModel('Shop.OrderStatus');
		$stati = $this->OrderStatus->find('list', array('fields' => array('name', 'id')))->toArray();

		$order = $this->Orders->get($id);

		$statusId = $order->order_status_id;

		if ($statusId == $stati['PEND']) {
			$order->order_status_id = $stati['DEL'];
			$this->Orders->save($order);

			$this->MultipleFlash->setFlash(__('Order set to "Payment delayed"'), 'success');
		} else {
			$this->MultipleFlash->setFlash(__('Invalid order status'), 'error');				
		}

		return $this->redirect($this->referer());
	}
	
	
	public function process_waiting_list() {
		$tid = $this->request->getSession()->read('Tournaments.id');
		$this->loadModel('Shop.Orders');
		
		$conditions = [
			'tournament_id' => $tid,
			'order_status_id' => OrderStatusTable::getWaitingListId()
		];
		
		if ($this->request->getSession()->check('Shop.InvoiceAddresses.country_id'))
			$conditions['InvoiceAddresses.country_id'] = $this->request->getSession()->read('Shop.InvoiceAddresses.country_id');
		
		// Filter by Article
		if ($this->request->getSession()->check('Shop.Articles.id')) {
			$conditions['Orders.id IN'] = 
					$this->OrderArticles->find()
						->select('order_id')
						->distinct()
						->where(['article_id IN' => explode(',', $this->request->getSession()->read('Shop.Articles.id'))])
			;
		}
		
		$orders = $this->Orders->find('all', [
			'order' => array('Orders.created' => 'ASC'),
			'limit' => 50,
			'conditions' => $conditions,
			'contain' => [
				'InvoiceAddresses'				
			]
		]);
		
		$count = 0;
		
		foreach ($orders as $order) {
			++$count;
			$order->order_status_id = OrderStatusTable::getPendingId();
			$order->accepted = date('Y-m-d H:i:s');
			$this->Orders->save($order);

			$this->set('processWaitingList', true);
			$this->_sendConfirmationMail($order->id);
		}
		
		$this->MultipleFlash->setFlash(__('{0} orders from waiting list set to pending', $count), 'info');
		
		return $this->redirect($this->referer());
	}
	
	
	public function setPending($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect($this->referer());
		} 
		
		$this->loadModel('Shop.Orders');
		$this->loadModel('Shop.OrderStatus');
		$stati = $this->OrderStatus->find('list', array('fields' => array('name', 'id')))->toArray();

		$order = $this->Orders->get($id);

		$statusId = $order->order_status_id;

		if ($statusId == $stati['ERR']) {
			$order->order_status_id = $stati['PEND'];
			$this->Orders->save($order);

			// $this->_sendConfirmationMail($id);

			$this->MultipleFlash->setFlash(__('Order reset to "Pending" and invoice sent'), 'success');
		} else if ($statusId == $stati['DEL']) {
			$order->order_status_id = $stati['PEND'];
			$this->Orders->save($order);

			$this->MultipleFlash->setFlash(__('Order reset to "Pending"'), 'success');			
		} else if ($statusId == $stati['INIT']) {
			$order->order_status_id = $stati['PEND'];
			$this->Orders->save($order);

			// $this->_sendConfirmationMail($id);

			$this->MultipleFlash->setFlash(__('Order reset to "Pending" and invoice sent'), 'success');
		} else if ($statusId == $stati['WAIT']) {
			$order->order_status_id = $stati['PEND'];
			$order->accepted = date('Y-m-d H:i:s');
			$this->Orders->save($order);

			$this->set('processWaitingList', true);
			$this->_sendConfirmationMail($id);

			$this->MultipleFlash->setFlash(__('Order set to "Pending" and invoice sent'), 'success');
		} else {
			$this->MultipleFlash->setFlash(__('Invalid order status'), 'error');				
		}

		return $this->redirect($this->referer());
	}
	
	
	public function setPaid($id = null) {
		if ($this->request->getData('cancel') !== null) {
			return $this->redirect(array('controller' => 'Orders', 'action' => 'index'));
		}		
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect($this->referer());
		} 
		
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect($this->referer());
		}

		$this->loadModel('Shop.Orders');
		$this->loadModel('Shop.OrderStatus');
		$stati = $this->OrderStatus->find('list', array('fields' => array('name', 'id')))->toArray();

		// Allow for discount and change of status
		$order = $this->Orders->get($id, array(
			'contain' => array('OrderComments' => array('Users'))
		));
				
		$this->set('order', $order);

		$order_status_id = $order->order_status_id;
		
		if ($this->request->is(['get'])) {
			if ($order_status_id === $stati['INVO']) {
				$this->set('stati', $this->OrderStatus->find('list', array(
					'fields' => array('id', 'description'),
					'conditions' => array(
						'OR' => array(
							array('id' => $order_status_id),
							array('name' => 'PAID')							
						)
					)
				))->toArray());
				
				$this->render('payment_received');
				return;
			}
			
			if ( $order_status_id !== $stati['PEND'] &&
				 $order_status_id !== $stati['INIT'] &&
				 $order_status_id !== $stati['DEL']  &&
				 $order_status_id !== $stati['FRD'] ) {
				$this->MultipleFlash->setFlash(__('Invalid order status'), 'error');
				return $this->redirect($this->referer());
			}
			
			$total = $order->total;
			$order = $this->Orders->patchEntity($order, array(
				'id' => $id,
				'order_status_id' => $stati['PAID'],
				'paid' => $total,
				'invoice_paid' => date('Y-m-d H:i:s')
			));

			if ($this->_saveCart($order)) {
				$this->MultipleFlash->setFlash(__('Order updated'), 'info');
			} else {
				$this->MultipleFlash->setFlash(__('Could not save cart'), 'error');
			}
			
			return $this->redirect($this->referer());
		} else {
			if ($order_status_id !== $stati['INVO']) {
				$this->MultipleFlash(__('Invalid order status'), 'warning');
				return $this->redirect(array('action' => 'index'));
			}
			
			$data = $this->request->getData();
			
			$order = $this->Orders->patchEntity($order, array(
				'id' => $data['id'],
				'discount' => $data['discount'],
				'paid' => $data['paid'],
				'order_status_id' => $order_status_id,  // replace by the choosen ID below
				'order_comments' => $data['order_comments']
			));
			
			if (!empty($data['order_status_id'])) {
				$order['order_status_id'] = $data['order_status_id'];
			}
					
			$count = count($order['order_comments']);

			if ($count > 0) {
				if (!empty($order['order_comments'][$count-1]['comment']) && empty($order['order_comments'][$count-1]['id']))
					$order['order_comments'][$count-1]['user_id'] = $this->_user->id;
				else
					unset($order['order_comments'][$count-1]);
			}
			
			$this->set('order', $order);

			if ($order['order_status_id'] == $stati['PAID']) {
				$ct = date('Y-m-d H:i:s');
				if (empty($order['invoice_paid']))
					$order['invoice_paid'] = $ct;
			}

			if ($this->Orders->save($order)) {
				$this->MultipleFlash->setFlash(__('Order updated'), 'info');
				return $this->redirect(array('controller' => 'Orders', 'action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('Could not update order'), 'error');
				return $this->redirect(array('controller' => 'Orders', 'action' => 'index'));
			}			
		}
	}
	
	
	public function setInvoice($id = null) {
		if ($this->request->getData('cancel') !== null)
			return $this->redirect($this->referer());
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect($this->referer());
		} 
		
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect($this->referer());
		}

		$this->loadModel('Shop.Orders');
		$this->loadModel('Shop.OrderStatus');
		$stati = $this->OrderStatus->find('list', array('fields' => array('name', 'id')))->toArray();

		// Allow for discount and change of status
		$order = $this->Orders->get($id, array(
			'contain' => array('OrderComments' => array('Users'))
		));
				
		$this->set('order', $order);

		$order_status_id = $order->order_status_id;
		
		if ( $order_status_id !== $stati['WAIT'] &&
			 $order_status_id !== $stati['PEND'] &&
			 $order_status_id !== $stati['DEL'] ) {
			$this->MultipleFlash->setFlash(__('Invalid order status'), 'error');
			return $this->redirect($this->referer());
		}

		$order = $this->Orders->patchEntity($order, array(
			'order_status_id' => $stati['INVO']
		));

		if ($this->_saveCart($order)) {
			$this->MultipleFlash->setFlash(__('Order converted to invoice'), 'info');
		} else {
			$this->MultipleFlash->setFlash(__('Could not save cart'), 'error');
		}

		return $this->redirect($this->referer());
	}
	
	
	// Callback from ipayment when the payment was successful
	public function payment_success() {
		$this->autoRender = false;
		
		$payment = $this->_getPayment();
		$payment->success($this->request);
	}
	
	// Called from payment engine when payment was successful
	public function _onSuccess($orderId, $status = 'PAID') {
		$this->loadModel('Shop.OrderStatus');
		
		$stati = $this->OrderStatus->find('list', array(
			'fields' => array('name', 'id')
		))->toArray();
		
		$this->loadModel('Shop.Orders');
		
		$order = $this->Orders->get($orderId);
		$oldOrderStatusId = $order->order_status_id;
		$order->order_status_id = $stati[$status];
		$order->invoice_paid = date('Y-m-d H:i:s');
		$order->paid = $order->total;
		
		$this->Orders->save($order);		
		
		// Save people and items, if not done yet (e.g. Invoice)
		if ( $status === 'PAID' && !in_array($oldOrderStatusId, [
				$stati['PAID'], $stati['INVO'] ]) ) {
			$this->_saveCart($order);
		}			
		
		$this->_sendConfirmationMail($orderId);	
	}
	
	// Called from payment engine when payment was not successful
	public function _onError($orderId, $err) {
		$this->loadModel('Shop.OrderStatus');
		
		$stati = $this->OrderStatus->find('list', array(
			'fields' => array('name', 'id')
		))->toArray();
		
		$code = (empty($stati[$err]) ? $stati['ERR'] : $stati[$err]);

		$this->loadModel('Shop.Orders');
		$order = $this->Orders->get($orderId);
		
		// Keep status pending if it is already in that state and new state is ERR
		if ( $code == $stati['ERR'] && in_array($order->order_status_id, [
				$stati['PEND'], $stati['INVO'], $stati['DEL']
			]) ) {
			; // Nothing
		} else {
			$order->order_status_id = $code;
		}
		
		$this->Orders->save($order);
	}
	
	
	// Persist order data. No records are created for people or user yet.
	private function _storeOrder($order_status_id = null, $payment_method = null) {
		// Store order in database as 'Pending'
		$this->loadModel('Shop.Orders');
		$this->loadModel('Shop.OrderAddresses');
		$this->loadModel('Shop.OrderStatus');
		$this->loadModel('Shop.OrderArticles');
		$this->loadModel('Shop.OrderSettings');
		$this->loadModel('Users');
		$this->loadModel('Tournaments');
		
		$people = array('PLA' => array(), 'ACC' => array(), 'COA' => array());

		foreach ($this->Cart->getPeople() as $person) {
			$people[$person['type']][] = $person;
		}
		
		$tid = $this->request->getSession()->read('Tournaments.id');
		$stati = $this->OrderStatus->find('list', array('fields' => array('name', 'id')))->toArray();
		
		
		$oldOrderId = $this->Cart->getOrderId();
		if (!empty($oldOrderId)) {
			$oldStatusId = $this->Orders->fieldByConditions('order_status_id', ['id' => $oldOrderId]);
			
			if ($oldStatusId == $stati['INIT']) {
				$oldOrder = $this->Orders->get($oldOrderId);
				$oldOrder->order_status_id = $stati['CANC'];
				$this->Orders->save($oldOrder);				
			}
		}

		$this->Cart->setOrderId(null);
		
		$address = $this->Cart->getAddress();
		
		if (empty($address) || empty($address['email'])) {
			file_put_contents('/tmp/xxxnoaddress-' . time(), print_r($this->Cart, true));
		}		
		
		$invoice_prefix = strftime($this->_shopSettings['invoice_no_prefix'], time());
		$invoice_postfix = strftime($this->_shopSettings['invoice_no_postfix'], time());

		$invoice_no = $this->Orders->find()
				->select(['invoice_no' => 'MAX(invoice_no)'])
				->where([
					'tournament_id' => $tid,
					'invoice LIKE' => $invoice_prefix . '%' . $invoice_postfix
				])
				->first()
				->invoice_no
		;
		if ($invoice_no === null)
			$invoice_no = 1;
		else
			$invoice_no += 1;
		
		$data = array(
			'email' => $address['email'],
			'tournament_id' => $tid,
			'order_status_id' => empty($order_status_id) ? $stati['PEND'] : $order_status_id,
			'total' => 0,
			'invoice_no' => $invoice_no,
			'invoice' => $invoice_prefix . sprintf("%05d", $invoice_no) . $invoice_postfix,
			'ticket' => md5(Text::uuid()),
			'language' => $this->_getLanguage()				
		);
		
		if (!empty($payment_method))
			$data['payment_method'] = $payment_method;
		
		$data['invoice_address'] = $address;

		$data['order_articles'] = array();

		foreach ($this->Cart->getItems() as $item) {
			$detail = null;

			switch ($item['name']) {
				case 'PLA' :
					$detail = $people['PLA'];
					break;

				case 'ACC' :
					$detail = $people['ACC'];
					break;

				case 'COA' :
					$detail = $people['COA'];
					break;
			}
			
			// Filter detail
			if (!empty($detail)) {
				foreach ($detail as $k => $d) {
					if (
							empty($item['article_variant_id']) &&
							empty($d['variant_id']) )
						continue;

					if (
							$item['article_variant_id'] == 
							$d['variant_id'])
						continue;

					unset($detail[$k]);
				}

				// Reset array keys
				$detail = array_values($detail);
			}
			
			if ($detail === null) {
				$data['order_articles'][] = array(
					'article_id' => $item['article_id'],
					'article_variant_id' => $item['article_variant_id'],
					'detail' => null,
					'description' => $item['description'],
					'quantity' => $item['quantity'],
					'price' => $item['price'],
					'total' => $item['total'],
				);				
			} else {
				foreach ($detail as $d) {
					$data['order_articles'][] = array(
						'article_id' => $item['article_id'],
						'article_variant_id' => $item['article_variant_id'],
						'detail' => serialize($d),
						'description' => $item['description'],
						'quantity' => 1,
						'price' => $item['price'],
						'total' => $item['price'],
					);
				}
			}

			$data['total'] += $item['total'];
		}
		
		$order = $this->Orders->newEntity($data);
		
		if (count($order['order_articles']) == 0)
			file_put_contents('/tmp/xxxemptyorder-' . time() , print_r([
					'Cart' => $this->Cart->getItems(), 
					'Data' => $data, 'Order' => $order
				], true)
			);	

		try {
			if (!$this->Orders->save($order)) {
				file_put_contents('/tmp/xxxfailedorder-' . time() , print_r($order, true));	

				// TODO: Fehlermeldung
				return false;
			}
		} catch (\Exception $ex) {
			file_put_contents('/tmp/xxxfailedorder-' . time() , print_r(['Exception' => $ex, 'Order' => $order], true));	

			// TODO: Fehlermeldung
			return false;			
		}

		
		$this->Cart->setOrderId($order->id);
		
		return $order->id;
	}
	
	
	public function saveCart($id) {		
		$this->loadModel('Shop.Orders');
		$order = $this->Orders->get($id);
		
		$this->_saveCart($order);
		
		return $this->redirect(array('controller' => 'orders', 'action' => 'index'));
	}
	
	
	// Persist User and People (Person, Registration, Participant) of what is in the cart
	private function _saveCart($order) {
		// Read order from database: we might not have the cart anymore
		
		$this->loadModel('Users');
		$this->loadModel('People');
		$this->loadModel('Tournaments');
		$this->loadModel('Registrations');
		$this->loadModel('Shop.Orders');
		$this->loadModel('Shop.Articles');
		$this->loadModel('Shop.OrderArticles');
		$this->loadModel('Shop.ArticleVariants');
		$this->loadModel('Languages');
				
		$id = $order->id;
		
		$orderArticles = $this->OrderArticles->find('all', [
			'conditions' => ['order_id' => $id]
		]);
		
		$tid = $order['tournament_id'];
		
		$uid = $this->Users->fieldByConditions('id', array('username' => $order['email']));
		$sendWelcomeMail = empty($uid);
		
		$sendVoucher = ($order->order_status_id === OrderStatusTable::getPaidId());
		
		// Start transaction. The model argument is a dummy, hopefully ...
		$db = $this->Orders->getConnection();
		$db->begin();
		
		if (empty($uid)) {
			$prefix_people = $this->Users->find()
					->select(['prefix_people' => 'MAX(prefix_people)'])
					->first()
					->prefix_people;
			
			if (empty($prefix_people) || $prefix_people == 0)
				$prefix_people = 10100;

			$prefix_people += 1;
			
			$user = $this->Users->newEntity(array(
				'username' => $order['email'],
				'password' => '', 
				'email' => $order['email'],
				'group_id' => GroupsTable::getParticipantId(),
				'enabled' => true,
				'tournament_id' => $tid,
				'language_id' => $this->Languages->fieldByConditions('id', array('name' => $order['language'])),
				'prefix_people' => $prefix_people
			));
			
			if (empty($user['language_id']))
				unset($user['language_id']);

			// Return error if we can't create the user
			if (!$this->Users->save($user)) {
				// TODO: Fehlermeldung
				$this->log('Cannot save user', 'error');
				$this->log(print_r($user), 'error');
				
				$db->rollback();
				return;
			}

			$uid = $user->id;
		}
		
		if (empty($uid)) {
			$this->log('No user id', 'error');
			$db->rollback();
			return false;
		}
		
		$order['user_id'] = $uid;
		
		if (!$this->Orders->save($order)) {
			$this->log('Cannot update order', 'error');
			$db->rollback();
			return false;
		}
		
		$allArticles = $this->Articles->find('list', array(
			'fields' => array('name', 'id'),
			'conditions' => array('tournament_id' => $tid)
		))->toArray();

		$registrations = array();
		
		foreach ($orderArticles as $article) {
			if (!empty($article['cancelled']))
				continue;
			
			if ( !empty($allArticles['PLA']) && $article['article_id'] == $allArticles['PLA'] ) {
				$p = unserialize($article['detail']);
				
				if (isset($p['ptt_class']) && !is_numeric($p['ptt_class']))
					unset($p['ptt_class']);
				if (isset($p['wchc']) && !is_numeric($p['wchc']))
					unset($p['wchc']);
					
				$registration = array('person' => $p);
				$registration['person']['user_id'] = $uid;
				$registration['type_id'] = TypesTable::getPlayerId();
				$registration['OrderArticle'] = $article;
				if (!empty($article['person_id'])) {					
					$registration['person']['id'] = $article['person_id'];
					$registration['person_id'] = $article['person_id'];
				}

				$registrations[] = $registration;
			} else if ( !empty($allArticles['ACC']) && $article['article_id'] == $allArticles['ACC'] ) {
				$a = unserialize($article['detail']);
				
				if (isset($a['ptt_class']) && !is_numeric($a['ptt_class']))
					unset($a['ptt_class']);
				if (isset($a['wchc']) && !is_numeric($a['wchc']))
					unset($a['wchc']);
					
				$registration = array('person' => $a);
				$registration['person']['user_id'] = $uid;
				$registration['type_id'] = TypesTable::getAccId();
				$registration['OrderArticle'] = $article;
				if (!empty($article['person_id'])) {					
					$registration['person']['id'] = $article['person_id'];
					$registration['person_id'] = $article['person_id'];
				}

				$registrations[] = $registration;
			} else if ( !empty($allArticles['COA']) && $article['article_id'] == $allArticles['COA'] ) {
				$a = unserialize($article['detail']);
				
				if (isset($a['ptt_class']) && !is_numeric($a['ptt_class']))
					unset($a['ptt_class']);
				if (isset($a['wchc']) && !is_numeric($a['wchc']))
					unset($a['wchc']);
					
				$registration = array('person' => $a);
				$registration['person']['user_id'] = $uid;
				$registration['type_id'] = TypesTable::getCoachId();
				$registration['OrderArticle'] = $article;
				if (!empty($article['person_id'])) {					
					$registration['person']['id'] = $article['person_id'];
					$registration['person_id'] = $article['person_id'];
				}

				$registrations[] = $registration;
			}
		}
		
		$variants = $this->ArticleVariants->find('list', array(
			'fields' => array('id', 'name'),
			'conditions' => ['variant_type' => 'Events']
		))->toArray();		

		foreach ($registrations as $registration) {
			$events = array('S', 'D', 'X', 'T');
			if ( !empty($registration['person']['variant_id']) && 
				 !empty($variants[$registration['person']['variant_id']]) )
				$events = explode(',', $variants[$registration['person']['variant_id']]);
				
			$article = $registration['OrderArticle'];
			
			$rid = $this->RegistrationUpdate->addParticipant($registration, $events);
			if (empty($rid)) {
				$db->rollback();
				return false;
			}
			
			$pid = $this->Registrations->fieldByConditions('person_id', array('id' => $rid));
			$oa = $this->OrderArticles->get($registration['OrderArticle']['id']);
			$oa->person_id = $pid;
			if (!$this->OrderArticles->save($oa)) {
				$this->log('Cannot update OrderArticle.person_id', 'error');
				$this->log(print_r($oa->getErrors(), true));
				
				$db->rollback();
				return false;
			}
		}
					
		if (!$db->commit()) {
			$this->log('Cannot commit', 'error');
			return false;
		}
		
		if ($this->Tournaments->fieldByConditions('enter_after', array('id' => $tid)) <= date('Y-m-d')) {
			if ($sendVoucher)
				$this->_sendVoucherMail($id);
			
			if ($sendWelcomeMail) {				
				// Set language based on order
				$lang = $order['language'];
				if (empty($lang))
					$lang = 'eng';

				$oldLang = I18n::getLocale();
				I18n::setLocale($lang);
		
				$this->WelcomeMail->sendWelcomeMail($uid);
				
				if (!empty($oldLang))
					I18n::setLocale($oldLang);
			}
		}
		
		return true;
	}
	
	
	// Choose an appropriate event for $person according to age category
	function _selectEvent($person, $tid, $type) {
		return $this->RegistrationUpdate->_selectEvent($person, $type, $tid);
	}
	
	public function import() {
		if ($this->request->getData('cancel') !== null) {
			return $this->redirect(array('controller' => 'orders', 'action' => 'index'));
			
		} 
		
		$data = $this->request->getData();
		
		if ($this->request->is(['post', 'put']) && is_uploaded_file($data['File']['tmp_name'])) {
			$file = $this->_openFile($data['File']['tmp_name'], 'rt', 'CP437');

			$this->_doImport($file, $data['Order']['email']);

			fclose($file);
			
			return $this->redirect(array('controller' => 'orders', 'action' => 'index'));
		}		
	}
	
	private function _doImport($file, $email) {
		$this->loadModel('Shop.Order');
		$this->loadModel('Shop.Article');
		$this->loadModel('Shop.OrderStatus');
		$this->loadModel('Shop.OrderArticle');
		$this->loadModel('User');
		$this->loadModel('Nation');
		$this->loadModel('Tournament');
				
		$tid = $this->request->getSession()->read('Tournament.id');
		$stati = $this->OrderStatus->find('list', array('fields' => array('name', 'id')))->toArray();
		$nations = $this->Nation->find('list', array('fields' => array('name', 'id')))->toArray();
		
		$articles = array();
		$articles['PLA'] = $this->Article->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'tournament_id' => $tid,
				'name' => 'PLA'
			)
		))->first();
		
		$articles['ACC'] = $this->Article->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'tournament_id' => $tid,
				'name' => 'ACC'
			)
		))->first();
		
		$articles['COA'] = $this->Article->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'tournament_id' => $tid,
				'name' => 'COA'
			)
		))->first();
		
		$articles['GALA'] = $this->Article->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'tournament_id' => $tid,
				'name' => 'GALA'
			)
		))->first();
		
		// Skip first line
		// fgets($file);
		
		$invoice_prefix = strftime($this->_shopSettings['invoice_no_prefix'], time());
		$invoice_postfix = strftime($this->_shopSettings['invoice_no_postfix'], time());

		$invoice_no = $this->Orders->find()
			->where([
				'tournament_id' => $tid,
				'invoice LIKE' => $invoice_prefix . '%' . $invoice_postfix
			])
			->select(['max' => 'MAX(invoice_no) + 1'])
			->first()
			->get('max')
		;
		
		if ($invoice_no === null)
			$invoice_no = 1;

		$order = array(
			'Order' => array(
				'email' => $email,
				'tournament_id' => $tid,
				'total' => 0,
				'invoice_no' => $invoice_no,
				'invoice' => $invoice_prefix . sprintf("%05d", $invoice_no) . $invoice_postfix,
				'ticket' => md5(Text::uuid()),
				'language' => 'eng',
				'order_status_id' => $stati['PEND']
			),
			'OrderArticle' => array()
		);

		$people = array('PLA' => array(), 'ACC' => array(), 'COA' => array());
		
		$count = array(
			'GALA' => 0,			
		);
				
		while (!feof($file)) {
			// Restart execution timer
			set_time_limit(60);

			// XXX utf8_encode
			$line = fgets($file);
			$line = str_replace("\n", "", $line);
			$line = str_replace("\t", ";", $line);

			if (strpos($line, '#', 0) === 0)
				continue;

			$this->log($line, 'debug');

			$fields = explode(";", $line);

			if (count($fields) < 8)
				continue;

			$idx = 0;
			$surname = trim($fields[$idx++]);
			$firstname = trim($fields[$idx++]);
			$sex = trim($fields[$idx++]);
			$birth = trim($fields[$idx++]);
			$country = trim($fields[$idx++]);
			$type = trim($fields[$idx++]);
			$player_email = trim($fields[$idx++]);
			$farewell_party = trim($fields[$idx++]) !== '';
			// $busticket = trim($fields[$idx++]) !== '';
			
			// Check for empty line
			if (empty($surname))
				continue;
			
			if ($sex === 'W')
				$sex = 'F';
			
			if (strpos($birth, '.') !== false) {
				$tmp = explode('.', $birth);
				$birth = sprintf('%04d-%02d-%02d', $tmp[2], $tmp[1], $tmp[0]);
			}
			
			if (!empty($birth) && strpos($birth, '-') === false)
				$birth .= '-00-00';
			
			$detail = array(
				'last_name' => mb_convert_case($surname, MB_CASE_UPPER, "UTF-8"),
				'first_name' => implode('-', array_map(function($a) {return mb_convert_case($a, MB_CASE_TITLE, "UTF-8");}, explode('-', mb_strtolower($firstname)))),
				'sex' => $sex,
				'nation_id' => $nations[$country]
			);
			
			if (!empty($birth))
				$detail['dob'] = $birth;
			else
				$detail['dob'] = null;
			
			if ($type === 'PLA') {
				if (empty($birth)) {
					$this->MultipleFlash->setFlash('Missing dob for ' . $surname . ', ' . $firstname, 'error');
					continue;
				}
				
				if (!empty($player_email))
					$detail['email'] = $player_email;
				
				$detail['type'] = 'PLA';
				
				$people['PLA'][] = $detail;
				
			} else if ($type === 'ACC') {				
				$detail['type'] = 'ACC';
				
				$people['ACC'][] = $detail;				
			} else if ($type === 'COA') {				
				$detail['type'] = 'COA';
				
				$people['COA'][] = $detail;				
			} 
			
			if ($farewell_party)
				++$count['GALA'];			
		}
		
		if (count($people['PLA']) > 0) {
			foreach ($people['PLA'] as $p) {
				$order['order_articles'][] = array(
					'article_id' => $articles['PLA']['id'],
					'description' => $articles['PLA']['description'],
					'price' => $articles['PLA']['price'],
					'total' => $articles['PLA']['price'],
					'quantity' => 1,
					'detail' => serialize($p)
				);

				$order['total'] +=  $articles['PLA']['price'];
			}
		}

		if (count($people['ACC']) > 0) {
			foreach ($people['ACC'] as $a) {
				$order['order_articles'][] = array(
					'article_id' => $articles['ACC']['id'],
					'description' => $articles['ACC']['description'],
					'price' => $articles['ACC']['price'],
					'total' =>  $articles['ACC']['price'],
					'quantity' => 1,
					'detail' => serialize($a)
				);				

				$order['total'] += $articles['ACC']['price'];
			}
		}

		if (count($people['COA']) > 0) {
			foreach ($people['COA'] as $a) {
				$order['order_articles'][] = array(
					'article_id' => $articles['COA']['id'],
					'description' => $articles['COA']['description'],
					'price' => $articles['COA']['price'],
					'total' =>  $articles['COA']['price'],
					'quantity' => 1,
					'detail' => serialize($a)
				);				

				$order['total'] += $articles['COA']['price'];
			}
		}
		
		if ($count['GALA'] > 0) {
			$num = $count['GALA'];
			
			$order['order_articles'][] = array(
				'article_id' => $articles['GALA']['id'],
				'description' => $articles['GALA']['description'],
				'price' => $articles['GALA']['price'],
				'total' => $num * $articles['GALA']['price'],
				'quantity' => $num,
				'detail' => null
			);				

			$order['total'] += $num * $articles['GALA']['price'];
		}
				
		if ($order['total'] > 0) {
			if (!$this->Order->save($this->Orders->newEntity($order))) {
				// TODO: Fehlermeldung
				return false;
			}	
		}
		
		return true;
	}
	
	
	// Calculate, which articles will put this order on the waiting list
	private function _calculateWaiting() {
		return $this->OrderUpdate->calculateWaiting();
	}
	
	// Count participants grouped by type
	public function count_participants() {
		if (!$this->request->is(['post', 'get'])) {
			// No POST, return empty body
			$this->autoRender = false;
			echo '';
			return;
		}
		
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		// $this->autoRender = false;
		$this->RequestHandler->renderAs($this, 'json');
		
		$this->loadModel('Shop.Articles');
		$articles = $this->Articles->find('list', array(
			'fields' => ['id', 'name'],
			'conditions' => ['tournament_id' => $tid]
		))->toArray();
		
		$counts = array();
		
		foreach ($articles as $id => $name)
			$counts[$name] = ['paid' => 0, 'pend' => 0, 'allotted' => 0, 'wait' => 0];
		
		$this->loadModel('Shop.OrderArticles');
		$this->loadModel('Shop.Allotments');
		
		$query = $this->OrderArticles->find();
		
		$paid = $query
			->select([
				'sum' => $query->func()->sum('OrderArticles.quantity'),
				'name' => 'Articles.name'
			])
			->contain(['Articles', 'Orders'])
			->where([
				'Orders.tournament_id' => $tid,
				'OrderArticles.cancelled IS NULL',
				'Orders.order_status_id IN' => [
					OrderStatusTable::getPaidId(),
					OrderStatusTable::getInvoiceId()
				]
			])
			->group('name')
			->toArray();
		
		foreach ($paid as $p)
			$counts[$p['name']]['paid'] = $p['sum'] ?? 0;
	
		$query = $this->OrderArticles->find();
		
		$pend = $query
			->select([
				'sum' => $query->func()->sum('OrderArticles.quantity'),
				'name' => 'Articles.name'
			])
			->contain(['Articles', 'Orders'])
			->where([
				'Orders.tournament_id' => $tid,
				'OrderArticles.cancelled IS NULL',
				'Orders.order_status_id IN' => [
					OrderStatusTable::getPendingId(),
					OrderStatusTable::getDelayedId()
				]
			])
			->group('name')
			->toArray();
		
		foreach ($pend as $p)
			$counts[$p['name']]['pend'] = $p['sum'] ?? 0;
				
		$query = $this->OrderArticles->find();
		
		$wait = $query
			->select([
				'sum' => $query->func()->sum('OrderArticles.quantity'),
				'name' => 'Articles.name'
			])
			->contain(['Articles', 'Orders'])
			->where([
				'Orders.tournament_id' => $tid,
				'OrderArticles.cancelled IS NULL',
				'Orders.order_status_id IN' => [
					OrderStatusTable::getWaitingListId()
				]
			])
			->group('name')
			->toArray();
		
		foreach ($wait as $w)
			$counts[$w['name']]['wait'] = $w['sum'] ?? 0;
				
		$allotments = $this->Allotments->find('all', array(
			'contain' => ['Articles'],
			'conditions' => [
				'tournament_id' => $tid
			]
		));
		
		foreach ($allotments as $a) {
			$sum = $this->OrderArticles->find()
				->contain(['Orders'])
				->where([
					'article_id' => $a['article_id'],
					'Orders.user_id' => $a['user_id'],
					'cancelled IS NULL'
				])
				->select(['count' => 'SUM(quantity)'])
				->group(['Orders.user_id', 'article_id'])
				->first()
			;
			
			if ($sum !== null && $sum['count'] < $a['allotment']) {
				$counts[$a['article']['name']]['allotted'] += ($a['allotment'] - $sum['count']);
			}
		}
		
		$this->set('counts', $counts);
		$this->set('_serialize', 'counts');
	}	
	
	
	public function article_image($id) {
		$this->autoRender = false;
		
		$this->loadModel('Shop.Articles');
		$article = $this->Articles->get($id);
		
		if ($article->article_image === null)
			return $this->response->withStatus (404);
		
		return $this->response
			->withType('jpeg')
			// laminas/laminas-diactoros comes with cakephp
			->withBody(new \Laminas\Diactoros\Stream($article->article_image))
		;		
	}
}
