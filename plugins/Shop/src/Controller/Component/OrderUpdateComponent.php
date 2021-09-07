<?php

namespace Shop\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Text;

use App\Model\Table\TypesTable;
use Shop\Model\Table\OrderStatusTable;


class OrderUpdateComponent extends Component {
	
	public $components = ['MultipleFlash'];
	
	private $serializeWhitelist = array(
		'first_name',
		'last_name',
		'sex',
		'nation_id',
		'dob',
		'email',
		'phone',
		'type',         // Same as article, could be omitted
		'agb'           // doubtfull, could be omitted
	);

	public function initialize(array $config) : void {	
		$this->Orders = TableRegistry::get('Shop.Orders');
		$this->Articles = TableRegistry::get('Shop.Articles');
		$this->OrderArticles = TableRegistry::get('Shop.OrderArticles');
		$this->OrderStatus = TableRegistry::get('Shop.OrderStatus');
		$this->OrderSettings = TableRegistry::get('Shop.OrderSettings');
		$this->OrderAddresses = TableRegistry::get('Shop.OrderAddresses');
		
		$this->Registrations = TableRegistry::get('Registrations');
		$this->Users = TableRegistry::get('Users');
	}
	
	
	public function addParticipant($rid, $items) {		
		$registration = $this->Registrations->find('all', array(
			'contain' => array('People'),
			'conditions' => array('Registrations.id' => $rid)
		))->first();
		
		if (empty($registration)) {
			$this->MultipleFlash->setFlash(__d('user', 'Unknown registration'), 'error');
			return false;
		}
		
		$tid = $registration['tournament_id'];
		$pid = $registration['person']['id'];
		$uid = $registration['person']['user_id'];
		$type_id = $registration['type_id'];
		$ct = date('Y-m-d H:i:s');

		if ($type_id == TypesTable::getPlayerId())
			$type = 'PLA';
		else if ($type_id == TypesTable::getAccId())
			$type = 'ACC';
		else if ($type_id == TypesTable::getCoachId())
			$type = 'COA';
		else {
			$this->MultipleFlash->setFlash(__d('user', 'Illegal type'), 'error');
			return false;
		}
		
		$registration['person']['type'] = $type;
		
		$user = $this->Users->find('all', array(
			'conditions' => array('Users.id' => $uid)
		))->first();
		
		if (empty($user)) {
			$this->MultipleFlash->setFlash(__d('user', 'Unknown user'), 'error');
			return false;
		}
		
		$invoStatusId = OrderStatusTable::getInvoiceId();
		
		$tmp = $this->Articles->find('all', array(
			'conditions' => array(
				'tournament_id' => $tid
			)
		));
		
		$articles = array();
		
		foreach ($tmp as $a) {
			$articles[$a['name']] = $a;
		}
		
		$order = $this->Orders->find('all', array(
			'conditions' => array(
				'tournament_id' => $tid,
				'user_id' => $uid,
				'order_status_id' => $invoStatusId
			)
		))->first();
		
		if ($order === null) {
			$order = $this->_createNewOrder($tid, $user, $invoStatusId, 'Invoice');
		}
		
		$article = $articles[$type];
		
		$order['order_articles'] = [];
		
		$order['order_articles'][] = $this->OrderArticles->newEntity(array(
			'article_id' => $article['id'],
			'description' => $article['description'],
			'quantity' => 1,
			'price' => $article['price'],
			'total' => $article['price'],
			'detail' => serialize(
				array_intersect_key(
						$registration['person']->toArray(),
						array_flip($this->serializeWhitelist)
				)
			),
			'person_id' => $pid
		));
		
		$order['total'] += $article['price'];
			
		foreach ($items as $name => $value) {
			if (empty($value))
				continue;
			
			$article = $articles[$name];
			
			$order['order_articles'][] = $this->OrderArticles->newEntity(array(
				'article_id' => $article['id'],
				'description' => $article['description'],
				'quantity' => 1,
				'price' => $article['price'],
				'total' => $article['price'],
				'person_id' => $pid
			));			

			$order['total'] += $article['price'];
		}
		
		if (!$this->Orders->save($order, array('modified' => $ct))) {
			$this->MultipleFlash->setFlash(__d('user', 'The order could be be stored'), 'error');
			$this->log('Cannot store order', 'error');
			return false;
		}
		
		return true;
	}
	
	
	public function editParticipant($rid, $items) {
		$registration = $this->Registrations->find('all', array(
			'contain' => array('People'),
			'conditions' => array(
				'Registrations.id' => $rid
			)
		))->first();
		
		if (empty($registration)) {
			$this->MultipleFlash->setFlash(__d('user', 'Unknown registration'), 'error');
			return false;
		}
		
		// Person has no user, which could only mean no order
		if (empty($registration['person']['user_id']))
			return true;
		
		$tid = $this->getController()->getRequest()->getSession()->read('Tournaments.id');
		$pid = $registration['person']['id'];
		$uid = $registration['person']['user_id'];
		$ct = date('Y-m-d H:i:s');
		
		if ($registration['type_id'] == TypesTable::getPlayerId())
			$type = 'PLA';
		else if ($registration['type_id'] == TypesTable::getAccId())
			$type = 'ACC';
		else if ($registration['type_id'] == TypesTable::getCoachIdId())
			$type = 'COA';
		else
			return true;
		
		// $registration comes from Registrastions table and doesn't contain 'type'
		$registration['person']['type'] = $type;
		
		$user = $this->Users->find('all', array(
			'conditions' => array('id' => $uid )
		))->first();
		
		$invoStatusId = OrderStatusTable::getInvoiceId();
		$paidStatusId = OrderStatusTable::getPaidId();

		// Map article name to article
		$tmp = $this->Articles->find('all', array(
			'conditions' => array(
				'tournament_id' => $tid
			)
		));

		$articles = array();
		foreach ($tmp as $t) {
			$articles[$t['name']] = $t;
		}

		// Map order article name to order article
		// Find all orders beloging to them
		// Find an order in state INVO to add new articles, if necessary
		$tmp = $this->OrderArticles->find('all', array(
			'contain' => array('Articles'),
			'conditions' => array(
				'person_id' => $pid,
				'OR' => array(
					'cancelled IS NULL',
					'Articles.name' => $type
				)
			)
		));
		
		$orders = array();
		$invoiceOrder = null;
		
		$orderArticles = array();
		foreach ($tmp as $t) {
			$orderArticles[$t['article']['name']] = $t;
			
			if (!isset($orders[$t['order_id']])) {
				$order = $this->Orders->find('all', array(
					'conditions' => array('id' => $t['order_id'])
				))->first();
				
				$order['order_articles'] = array();
				
				$orders[$t['order_id']] = $order;
				
				if ($order['order_status_id'] == $invoStatusId)
					$invoiceOrder = $order;
			}
		}
		
		reset($orderArticles);				
		
		// Modify person detail in order article, if necessary
		// But don't cancel
		if (isset($orderArticles[$type])) 
			$personArticle = $orderArticles[$type];
		else
			return true;  // Person does not come from an order

		// Don't edit a cancelled and already cancelled person
		if ( !empty($registration['cancelled']) && !empty($personArticle['cancelled']) )
			return true;
		
		if (empty($orders[$personArticle['order_id']]))
			return true; // Should not happen
		
		if (empty($personArticle['detail']))
			return true; // Should not happen
		
		if (empty($invoiceOrder)) {
			// Not found for this person: find for user
			$invoiceOrder = $this->Orders->find('all', array(
				'conditions' => array(
					'user_id' => $uid,
					'order_status_id' => $invoStatusId
				)
			))->first();
		}
		
		if (empty($invoiceOrder)) {
			// Still not found: create a new one
			$invoiceOrder = $this->_createNewOrder($tid, $user, $invoStatusId, 'Invoice');
			
			$invoiceAddress = $this->OrderAddresses->find('all', array(
				'conditions' => array(
					'order_id' => $personArticle['order_id'],
					'type' => 'P'
				)
			))->first();
			
			if (!empty($invoiceAddress)) {
				unset($invoiceAddress['id']);
				unset($invoiceAddress['order_id']);
				
				$invoiceAddress->isNew(true);
				
				$invoiceOrder['invoice_address'] = $invoiceAddress;
			}
		}
		
		if (!isset($invoiceOrder['order_articles']))
			$invoiceOrder['order_articles'] = array();

		$orderPerson = unserialize($personArticle['detail']);

		$personOrderId = $personArticle['order_id'];
		
		$diff = false;
		foreach ($this->serializeWhitelist as $key) {
			if (!isset($registration['person'][$key]))
				continue;
			
			$diff |= $orderPerson[$key] != $registration['person'][$key];
			$orderPerson[$key] = $registration['person'][$key];
		}

		$newArticle = array(
			'id' => $personArticle['id'],
			'detail' => serialize(array_intersect_key(
						$orderPerson,
						array_flip($this->serializeWhitelist)
				)
			)
		);

		// If he is not cancelled but was, correct this
		if (empty($registration['cancelled']) && !empty($personArticle['cancelled'])) {
			if ($orders[$personOrderId]['order_status_id'] == $invoStatusId) {
				// This order is still in state INVO, so no problem
				$newArticle['cancelled'] = null;

				$orders[$personOrderId]['order_articles'][] = $newArticle;
				
				$orders[$personOrderId]['total'] += $personArticle['price'];
			} else if (
					$orders[$personOrderId]['order_status_id'] == $paidStatusId && 
					$personArticle['cancellation_fee'] == $personArticle['total'] && 
					$orders[$personOrderId]['cancellation_discount'] == 0 ) {
				// There was no refund made and the order is still valid, so set back to the original state
				$orders[$personOrderId]['cancellation_fee'] -= $personArticle['cancellation_fee'];
				
				$newArticle['cancelled'] = null;

				$orders[$personOrderId]['order_articles'][] = $newArticle;
				
				$orders[$personOrderId]['total'] += $personArticle['price'];
			} else {
				$orders[$personOrderId]['order_articles'][] = array(
					'id' => $personArticle['id'],
					'person_id' => null,
				);
				
				$invoiceOrder['order_articles'][] = array(
					'article_id' => $personArticle['article_id'],
					'article_variant_id' => $personArticle['article_variant_id'],
					'description' => $personArticle['description'],
					'quantity' => 1,
					'detail' => $newArticle['detail'],
					'person_id' => $personArticle['person_id'],
					'price' => $personArticle['price'],
					'total' => $personArticle['price'],
				);
				
				$invoiceOrder['total'] += $personArticle['price'];
			}
		} else if ($diff) {
			$orders[$personOrderId]['order_articles'][] = $newArticle;
		}

		// Check all articles if they are new (append to invoice) or cancelled
		foreach ($items as $name => $value) {
			$article = $articles[$name];
			if (empty($value)) {
				// Article cancelled; ignore if it was already cancelled, i.e. not in list				
				if (empty($orderArticles[$name]))
					continue;

				$oldArticle = $orderArticles[$name];
				$articleOrderId = $oldArticle['order_id'];
				
				// Only orders in state INVO can be partially cancelled this way
				if ($orders[$articleOrderId]['order_status_id'] != $invoStatusId)
					return false;

				$orders[$articleOrderId]['order_articles'][] = array(
					'id' => $oldArticle['id'],
					'total' => $oldArticle['total'],
					'cancelled' => $ct
				);
				$orders[$articleOrderId]['total'] -= $oldArticle['total'];
			} else {
				// Article not cancelled; ignore if it was not cancelled, i.e. not in list
				if (!empty($orderArticles[$name]))
					continue;

				// Create a new article with exactly one item
				$newArticle = array(
					'order_id' => $order['id'],
					'article_id' => $article['id'],
					'person_id' => $pid,
					'description' => $article['description'],
					'quantity' => 1,
					'price' => $article['price'],
					'total' => $article['price'],
					'cancelled' => null,
				);

				if (!empty($orderArticles[$name]['id']))
					$newArticle['id'] = $orderArticles[$name]['id'];

				$invoiceOrder['total'] += $newArticle['total'];

				$invoiceOrder['order_articles'][] = $newArticle;
			}
		}
		
		$options = ['modified' => $ct];
		
		foreach ($orders as $data) {
			if (count($data['order_articles']) === 0)
				continue;
			
			$order = $this->Orders->get($data['id']);
			$order = $this->Orders->patchEntity($order, $data->toArray());
			if (!$this->Orders->save($order, $options)) {
				return false;
			}
		}
		
		if (count($invoiceOrder['order_articles']) !== 0) {
			if (!$this->Orders->save($invoiceOrder, $options))
				return false;
		}

		return true;
	}
	
	
	public function deleteParticipant($pid) {
		$orderItems = $this->OrderArticles->find('all', array(
			'conditions' => array(
				'OrderArticles.person_id' => $pid
			)
		))->toArray();
		
		if (empty($orderItems))
			return true;
		
		$order = $this->Orders->find('all', array(
			'contain' => array('OrderStatus'),
			'conditions' => array(
				'Orders.id' => $orderItems[0]['order_id']
			)
		))->first();
		
		if (empty($order))
			return true;

		if ($order['order_status']['name'] == 'PAID') {
			$this->MultipleFlash->setFlash(__('To apply refunds you shall cancel within order {0} directly', $order['invoice']), 'error');
			return false;			
		}
		
		if ($order['order_status']['name'] != 'INVO') {
			$this->MultipleFlash->setFlash(__('Order is in the wrong state {0} to cancel people', $order['order_status']['description']), 'error');
			return false;
		}
		
		unset($order['order_status']);

		$ct = date('Y-m-d H:i:s');
		
		$order['order_articles'] = array();
		
		foreach ($orderItems as $item) {
			if (!empty($item['cancelled']))
				continue;
			
			$item['cancelled'] = $ct;
			$order['total'] -= $item['total'];
			$order['order_articles'][] = $item;
		}
		
		return $this->Orders->save($order, array('modified' => $ct));		
	}
	
	public function setVarsForRegistration($pid = null) {		
		$tid = $this->getController()->getRequest()->getSession()->read('Tournaments.id');
		
		$orderArticles = array();
		
		if ($pid !== null) {
			$tmp = $this->OrderArticles->find('all', array(
				'conditions' => array(
					'OrderArticles.person_id' => $pid,
					'OrderArticles.cancelled IS NULL',
				),
				'fields' => array(
					'OrderArticles.article_id', 
					'sum' => 'SUM(OrderArticles.quantity)'
				),
				'group' => array('article_id')
			))->toArray();
			
			$orderArticles = 
				Hash::combine($tmp, 
						'{n}.article_id', 
						'{n}.sum'
				)
			;
			
			$this->getController()->set('orderarticles', $orderArticles);
		}

		$this->getController()->set('articles', $this->Articles->find('all', array(
			'conditions' => array(
				'tournament_id' => $tid,
				'visible' => 1,
				'OR' => array(
					'available_from IS NULL',
					'available_from <=' => date('Y-m-d'),
					'Articles.id IN' => array_keys($orderArticles) + [0]
				),
				'OR' => array(
					'available_until IS NULL',
					'available_until >=' => date('Y-m-d'),
					'Articles.id IN' => array_keys($orderArticles) + [0]
				)
			),
			'order' => array('sort_order' => 'ASC')
		)));

	}
	
		// Calculate, which articles will put this order on the waiting list
	public function calculateWaiting() {
		$controller = $this->getController();
		$controller->loadModel('Shop.OrderStatus');
		$controller->loadModel('Shop.OrderArticles');
		$controller->loadModel('Shop.Articles');
		$controller->loadModel('Shop.Allotments');
		$controller->loadModel('Registrations');
		
		$tid = $this->getController()->getRequest()->getSession()->read('Tournaments.id');
		
		$stati = $controller->OrderStatus->find('list', array(
			'fields' => array('name', 'id')
		))->toArray();
		$articles = $controller->Articles->find('list', array(
			'fields' => array('id', 'name'),
			'conditions' => array('Articles.tournament_id' => $tid)
		))->toArray();
		
		$available = $controller->Articles->find('list', array(
			'fields' => array('name', 'available'),
			'conditions' => array('Articles.tournament_id' => $tid)
		))->toArray();
		
		$sold = array();
		$pending = array();
		$allotted = array();
		$waiting = array();
		$cart = array();
		$override = array();
		
		foreach ($articles as $k => $v) {
			$sold[$v] = 0;
			$pending[$v] = 0;
			$allotted[$v] = 0;
			$waiting[$v] = 0;
			$cart[$v] = 0;
			$override[$v] = false;
		}	
		
		$allotments = $controller->Allotments->find('all', array(
			'contain' => ['Articles'],
			'conditions' => [
				'Articles.tournament_id' => $tid
			]
		));
		
		foreach ($allotments as $a) {
			$sum = $controller->OrderArticles->find()
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
			
			if ($sum['count'] < $a['allotment']) {
				$allotted[$a['article']['name']] += ($a['allotment'] - $sum['count']);
				
				if ($controller->Auth->user('id') == $a['user_id'])
					$override[$a['article']['name']] = true;
			}
		}
		
		$tmp = $controller->OrderArticles->find('all', array(
			'contain' => array('Orders', 'Articles'),
			'fields' => array('count' => 'SUM(OrderArticles.quantity)', 'Articles.name'),
			'group' => array('Articles.name'),
			'conditions' => array(
				'OrderArticles.cancelled IS NULL',
				'Articles.tournament_id' => $tid,
				'Orders.order_status_id IN' => array($stati['PAID'], $stati['INVO'])
			)
		));
		
		foreach ($tmp as $v) {
			$sold[$v['article']['name']] = (int) $v['count'];
		}
		
		$tmp = $controller->OrderArticles->find('all', array(
			'contain' => array('Orders', 'Articles'),
			'fields' => array('count' => 'SUM(OrderArticles.quantity)', 'Articles.name'),
			'group' => array('Articles.name'),
			'conditions' => array(
				'OrderArticles.cancelled IS NULL',
				'Orders.order_status_id' => $stati['PEND']
			)
		));
		
		foreach ($tmp as $v) {
			$pending[$v['article']['name']] = (int) $v['count'];
		}
		
		$tmp = $controller->OrderArticles->find('all', array(
			'contain' => array('Orders', 'Articles'),
			'fields' => array('count' => 'SUM(OrderArticles.quantity)', 'Articles.name'),
			'group' => array('Articles.name'),
			'conditions' => array(
				'OrderArticles.cancelled IS NULL',
				'Orders.order_status_id' => $stati['WAIT']
			)
		));
		
		foreach ($tmp as $v) {
			$waiting[$v['article']['name']] = $v['count'];
		}
		
		if ( $controller->components()->has('Cart') ) {
			$tmp = $this->getController()->Cart->getItems();
			foreach ($articles as $k => $v) {
				foreach ($tmp as $oa) {
					if ($oa['article_id'] == $k)
						$cart[$v] += $oa['quantity'];
				}
			}
		}
		
		$ret = array();
		
		foreach ($articles as $name) {			
			$ret[$name] =
					!empty($available[$name]) && !$override[$name] &&
					(
						!empty($waiting[$name]) ||
						($sold[$name] + $pending[$name] + $allotted[$name] + $cart[$name]) >= $available[$name]
					)
			;
		}

		return $ret;
	}

	public function setVarsForOrder($id) {
		$tid = $this->getController()->getRequest()->getSession()->read('Tournaments.id');
		
		$controller = $this->getController();
		
		$controller->loadModel('Shop.Orders');
		$controller->loadModel('Shop.OrderArticles');
		$controller->loadModel('Shop.Articles');
		$controller->loadModel('Shop.Countries');
		$controller->loadModel('People');
		$controller->loadModel('Types');
		$controller->loadModel('Nations');
		$controller->loadModel('Competitions');
		
		$order = $controller->Orders->find('all', array(
			'contain' => array('OrderStatus', 'InvoiceAddresses', 'Users'),
			'conditions' => array('Orders.id' => $id)			
		))->first();
		
		$tid = $order['tournament_id'];
		
		$items = $controller->OrderArticles->find('all', array(
			'contain' => array('Articles'),
			'conditions' => array('order_id' => $id),
			'order' => array('Articles.sort_order')
		));
		
		$articles = $controller->Articles->find('all', array(
			'tournament_id' => $tid
		));
		
		$countries = $controller->Countries->find('list', array('fields' => array('id', 'name')))->toArray();
		
		$people = array();
		foreach ($items as $item) {
			if (empty($item['detail']))
				continue;
			
			$detail = unserialize($item['detail']);	
			
			if (empty($detail))
				continue;

			if (!empty($item['cancelled']))
				$detail['cancelled'] = $item['cancelled'];
			
			// 'type' may be missing
			if (!isset($detail['type']))
				$detail['type'] = $item['article']['name'];

			switch ($detail['type']) {
				case 'PLA' :
				case 'ACC' :
				case 'COA' :
					$people[] = $detail;
					break;
			}
		}
		
		// Find the youngest player here: That player determines the team they will play.
		$teamPerson = null;
		
		foreach ($people as $person) {
			if ($person['type'] != 'PLA')
				continue;
			
			if ($teamPerson == null || $teamPerson['dob'] < $person['dob'])
				$teamPerson = $person;
		}
		
		$controller->loadModel('Shop.ArticleVariants');
		$variants = $controller->ArticleVariants->find('list', array(
			'fields' => array('id', 'name')
		))->toArray();		
		
		$controller->loadModel('Competitions');

		foreach ($people as $k => $v) {
			if ($v['type'] != 'PLA')
				continue;
			
			$types = array('S', 'D', 'X', 'T');
			if (!empty($v['variant_id'])) {
				if (!empty($variants[$v['variant_id']]))
					$types = explode(',', $variants[$v['variant_id']]);
			}
			
			$people[$k]['single_id'] = in_array('S', $types) ? $controller->Competitions->findEventForPerson($v, 'S', $tid) : null;
			$people[$k]['double_id'] = in_array('D', $types) ? $controller->Competitions->findEventForPerson($v, 'D', $tid) : null;
			$people[$k]['mixed_id'] = in_array('X', $types) ? $controller->Competitions->findEventForPerson($v, 'X', $tid) : null;
			$people[$k]['team_id'] = in_array('T', $types) ? $controller->Competitions->findEventForPerson($teamPerson, 'T', $tid) : null;
		}
		
		$types = $controller->Types->find('list', array('fields' => array('id', 'name')))->toArray();
		
		$nations = $controller->Nations->find('list', array('fields' => array('id', 'name')))->toArray();
		
		$tmp = $controller->Competitions->find('all', array(
			'fields' => array('id', 'name', 'type_of'),
			'conditions' => array('tournament_id' => $tid)
		));
		
		$competitions = array();
		foreach ($tmp as $t) {
			$competitions[$t['id']] = $t;
		}
		
		$address = $order->invoice_address;
		if ($address !== null)
		  $address->email = $order->email;
				
		$controller->set('order', $order);
		$controller->set('address', $address);
		$controller->set('items', $items);
		$controller->set('articles', Hash::combine($articles->toArray(), '{n}.id', '{n}'));
		$controller->set('countries', $countries);
		$controller->set('people', $people);
		$controller->set('types', $types);
		$controller->set('nations', $nations);
		$controller->set('competitions', $competitions);
	}
	
	
	private function _createNewOrder($tid, $user, $order_status_id, $payment_method = null) {
		$tmp = $this->OrderSettings->find('all', array(
			'conditions' => array(
				'tournament_id' => $tid
			)
		))->first();

		if (empty($tmp))
			$tmp = array();
		if (empty($tmp))
			$tmp = array(
				'invoice_no_prefix' => '', 
				'invoice_no_postfix' => '', 
				'currency' => 'EUR'
			);

		$invoice_prefix = strftime($tmp['invoice_no_prefix'], time());
		$invoice_postfix = strftime($tmp['invoice_no_postfix'], time());

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

		$order = $this->Orders->newEntity(array(
			'user_id' => $user['id'],
			'email' => $user['email'],
			'tournament_id' => $tid,
			'order_status_id' => $order_status_id,
			'total' => 0.,
			'language' => 'en',
			'invoice_no' => $invoice_no,
			'invoice' => $invoice_prefix . sprintf("%05d", $invoice_no) . $invoice_postfix,
			'payment_method' => $payment_method,
			'ticket' => md5(Text::uuid()),
			'order_articles' => array()
		));	

		return $order;		
	}
}
