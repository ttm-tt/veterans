<?php
namespace Shop\Controller;

use Cake\Utility\Hash;
use Cake\ORM\TableRegistry;
use Cake\Event\EventInterface;

use Shop\Model\Table\OrderStatusTable;

class ArticlesController extends ShopAppController {

	function beforeFilter(EventInterface $event) {
		parent::beforeFilter($event);
		
		// We add variants dynamically in add / edit, but Security would complain
		// about form tampering. So disable it, unless there is a better way.
		// We don't use forms outside add / edit, so we can disable it for the 
		// entire controller.
		$this->Security->setConfig('validatePost', false);		
	}
		
	function index() {
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		}
			
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$this->paginate = array(
			'conditions' => array('Articles.tournament_id' => $tid),
			'order' => array('Articles.sort_order' => 'ASC')
		);

		$articles = $this->paginate();
		
		$this->loadModel('Shop.OrderStatus');
		$paidId = $this->OrderStatus->fieldByConditions('id', array('OrderStatus.name' => 'PAID'));
		$invoId = $this->OrderStatus->fieldByConditions('id', array('OrderStatus.name' => 'INVO'));
		$pendId = $this->OrderStatus->fieldByConditions('id', array('OrderStatus.name' => 'PEND'));
		$waitId = $this->OrderStatus->fieldByConditions('id', array('OrderStatus.name' => 'WAIT'));
		$delId  = $this->OrderStatus->fieldByConditions('id', array('OrderStatus.name' => 'DEL'));
		
		// If only limited supply count sold items
		$this->loadModel('Shop.OrderArticles');
		$this->loadModel('Shop.Allotments');
		
		$tmp = $this->OrderArticles->find('all', array(
			'fields' => array('article_id', 'sold' => 'SUM(OrderArticles.quantity)'),
			'contain' => array('Articles', 'Orders'),
			'conditions' => array(
				'OrderArticles.cancelled IS NULL',
				'Articles.tournament_id' => $tid,
				// 'Order.order_status_id' => $paidId
				'Orders.order_status_id IN' => array($paidId, $invoId)
			),
			'group' => array('OrderArticles.article_id')
		));
		
		$sold = Hash::combine($tmp->toArray(), '{n}.article_id', '{n}.sold');
		
		$tmp = $this->OrderArticles->find('all', array(
			'fields' => array('article_id', 'pend' => 'SUM(OrderArticles.quantity)'),
			'contain' => array('Articles', 'Orders'),
			'conditions' => array(
				'OrderArticles.cancelled IS NULL',
				'Articles.tournament_id' => $tid,
				// 'Order.order_status_id' => $paidId
				'Orders.order_status_id IN' => [$pendId, $delId]
			),
			'group' => array('OrderArticles.article_id')
		));
		
		$pend = Hash::combine($tmp->toArray(), '{n}.article_id', '{n}.pend');
		
		$tmp = $this->OrderArticles->find('all', array(
			'fields' => array('article_id', 'wait' => 'SUM(OrderArticles.quantity)'),
			'contain' => array('Articles', 'Orders'),
			'conditions' => array(
				'OrderArticles.cancelled IS NULL',
				'Articles.tournament_id' => $tid,
				// 'Order.order_status_id' => $paidId
				'Orders.order_status_id' => $waitId
			),
			'group' => array('OrderArticles.article_id')
		));
		
		$wait = Hash::combine($tmp->toArray(), '{n}.article_id', '{n}.wait');
		
		$allotted = array();
		
		$allotments = $this->Allotments->find('all', array(
			'contain' => ['Articles'],
			'conditions' => [
				'Articles.tournament_id' => $tid
			]
		));
		
		foreach ($allotments as $a) {
			if (!isset($allotted[$a['article_id']]))
				$allotted[$a['article_id']] = 0;
					
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
			
			// Query returns null if no row matches
			if (($sum['count'] ?? 0) < $a['allotment']) {
				$allotted[$a['article_id']] += ($a['allotment'] - $sum['count']);
			}
		}
		
		
		
		$this->set('articles', $articles);
		$this->set('sold', $sold);
		$this->set('pend', $pend);
		$this->set('wait', $wait);
		$this->set('allocated', $allotted);
	}

	function view($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid article'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		// Retrieve fields in English
		$this->Articles->setLocale('en');
		$this->set('article', $this->Articles->find('translations', [
				'conditions' => ['Articles.id' => $id], 
				'contain' => ['ArticleVariants']
		])->first());
	}

	function add() {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$article = $this->Articles->newEmptyEntity();
		$article->tournament_id = $tid;
		
		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->getData();
			
			$artOrder = $this->Articles->find()
					->select(['sort_order' => 'MAX(sort_order) + 1'])
					->where(['tournament_id' => $tid])
					->first()
					->sort_order;

			$data['sort_order'] = $artOrder;

			$artVarOrder = 0;
			foreach ($data['article_variants'] as $k => $variant) {
				if (empty($variant['description'])) {
					unset($data['article_variants'][$k]);
				} else {
					$data['article_variants'][$k]['sort_order'] = ++$artVarOrder;
				}
			}

			reset($data['article_variants']);
					
			// Remove or save photo into record
			if ($data['photo_remove']) {
				$data['article_image'] = null;
			} else if ( !empty($data['photo_upload']) ) {
				$this->_savePhoto($data);
			} else {
				unset($data['article_image']);
			}

			unset($data['photo_upload']);
			unset($data['photo_remove']);

			$article = $this->Articles->patchEntity($article, $data);
			
			if ($this->Articles->save($article)) {
				$this->MultipleFlash->setFlash(__('The article has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The article could not be saved. Please, try again.'), 'error');
			}
		}
		
		$this->set('article', $article);
	}

	function edit($id = null) {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		if (!$id ) {
			$this->MultipleFlash->setFlash(__('Invalid article'), 'error');
			return $this->redirect(array('action' => 'index'));
		} 
		
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		$article = $this->Articles->find('translations', [
			'contain' => ['ArticleVariants'],
			'conditions' => [
				'Articles.id' => $id
			]
		])->first();

		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->getData();
			
			$toDelete = array();
			
			foreach ($data['article_variants'] as $k => $variant) {
				if (empty($variant['description'])) {
					if (!empty($variant['id']))
						$toDelete[] = $variant['id'];
					unset($data['article_variants'][$k]);
				}
			}
			
			// Remove or save photo into record
			if ($data['photo_remove']) {
				$data['article_image'] = null;
			} else if ( !empty($data['photo_upload']) ) {
				$this->_savePhoto($data);
			} else {
				unset($data['article_image']);
			}

			unset($data['photo_upload']);
			unset($data['photo_remove']);

			$article = $this->Articles->patchEntity($article, $data);
			$translations = $article->_translations;
			
			if ($this->Articles->save($article)) {
				$this->loadModel('Shop.ArticleVariants');
				$this->ArticleVariants->deleteAll(array(
						'ArticleVariants.id IN' => $toDelete + [0]
				));
				
				// And remove all empty translations
				foreach (($translations ?: []) as $locale => $translation) {
					foreach ($this->Articles->behaviors()->get('Translate')->getConfig('fields') as $field) {
						if (strlen($translation[$field]) === 0) {
							TableRegistry::get('I18n')->deleteAll([
								'locale' => $locale,
								'foreign_key' => $article->id,
								'field' => $field
							]);
						}
					}
					
				}
				$this->MultipleFlash->setFlash(__('The article has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The article could not be saved. Please, try again.'), 'error');
			}
		}
		
		$this->set('article', $article);
	}

	function delete($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid id for article'), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		
		$article = $this->Articles->get($id);
		
		if ($this->Articles->delete($article)) {
			$this->MultipleFlash->setFlash(__('Article deleted'), 'success');
			return $this->redirect(array('action'=>'index'));
		}
		$this->MultipleFlash->setFlash(__('Article was not deleted'), 'error');
		return $this->redirect(array('action' => 'index'));
	}
	
	
	public function chart($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid id for article'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		}
			
		$this->loadModel('Shop.OrderStatus');		
		$this->loadModel('Shop.OrderArticles');
		
		$tmp = $this->OrderArticles->find('all', array(
			'fields' => array(
				// 'YEARWEEK(Order.created) AS week',
				'week' => 'DATE(Orders.created)',
				'sold' => 'SUM(OrderArticles.quantity)',
				'status_id' => 'CASE Orders.order_status_id ' .
				' WHEN ' . OrderStatusTable::getPaidId() . ' THEN ' .
				'	IF(ISNULL(OrderArticles.cancelled), ' . OrderStatusTable::getPaidId() . ', ' . OrderStatusTable::getCancelledId() . ') ' .
				' ELSE ' .
				'	Orders.order_status_id ' .
				' END'				
			),
			'contain' => array('Articles', 'Orders'),
			'conditions' => array(
				'OrderArticles.article_id' => $id,
				array(
					'OR' => array(
						// Ignore those which were cancelled immediately
						// Which means, cancelled at least one day later
						'Orders.invoice_cancelled IS NULL',
						'DATE(Orders.invoice_cancelled) >= ADDDATE(DATE(Orders.created), 1)'
					),
					'OR' => array(
						// Ignore partial cancelled orders if not yet paid
						'Orders.order_status_id = ' . OrderStatusTable::getPaidId(), 
						'ISNULL(OrderArticles.cancelled)'
					)
				)
			),
			'group' => array('week', 'status_id', 'cancelled')
		));
		
		$data = Hash::combine($tmp->toArray(), '{n}.status_id', '{n}.sold', '{n}.week');

		// debug($data); die();
		$this->set('data', json_encode($data));
		
		$this->set('article', $this->Articles->get($id));
	}
		
	// -------------------------------------------------------------------
	// -------------------------------------------------------------------
	function _savePhoto(&$formdata) {
		$upload = $formdata['photo_upload'];
		
		if($upload->getError() !== UPLOAD_ERR_OK)
			return null;
		
		$file = tempnam('/tmp', 'upld');
		$upload->moveTo($file);

		list($width, $height, $imagetype) = getimagesize($file);

		switch ($imagetype) {
			case 1 :
				$src = imagecreatefromgif($file);
				break;

			case 2 :
				$src = imagecreatefromjpeg($file);
				break;

			case 3 :
				$src = imagecreatefrompng($file);
				break;

			default :
				unlink($file);

				$this->MultipleFlash->setFlash(__('Only JPG, GIF and PNG images are allowed.'), 'error');
				return;
		}

		$dstHeight = 400;
		$dstWidth = round(($width * $dstHeight) / $height); 

		$dst = imagecreatetruecolor($dstWidth, $dstHeight);  // Photos are 3 : 2

		// Resize image to 600 x 400
		imagecopyresized($dst, $src, 0, 0, 0, 0, $dstWidth, $dstHeight, $width, $height);

		// Stream to buffer and capture data
		ob_start();
		imagejpeg($dst, null);

		$data = ob_get_clean();

		$formdata['article_image'] = $data;

		unlink($file);
	}
}
