<?php
namespace Shop\Controller;

use Cake\Utility\Hash;
use Cake\Mailer\Email;
use Cake\I18n\Date;
use Cake\I18n\I18n;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\TableRegistry;
use Cake\ORM\Query;

use GeoIp2\Database\Reader;

use Shop\Model\Table\OrderStatusTable;

use App\Model\Table\UsersTable;


class OrdersController extends ShopAppController {

	function initialize() : void {
		parent::initialize();
		
		$this->loadComponent('RegistrationUpdate');
		$this->loadComponent('Shop.OrderUpdate');
	}

	function index() {
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		}
		
		// Convert people details from old format to new
		if (!empty($this->request->getQuery('convert')) && UsersTable::hasRootPrivileges($this->_user)) {
			set_time_limit(0);
			
			$this->loadModel('Shop.OrderArticles');
			$oa = $this->OrderArticles->find('all', array(
				'conditions' => ['detail IS NOT NULL']
			));
			
			foreach ($oa as $a) {
				$detail = unserialize($a->detail);
				if (isset($detail[0]) && count($detail) == 1) {
					$detail = $detail[0];
					if (isset($detail['Person']))
						$detail = $detail['Person'];
					
					$a->detail = serialize($detail);
					$this->OrderArticles->save($a);
				}
			}
			
			return $this->redirect(['action' => 'index']);
		}
		
		$this->loadModel('Shop.OrderArticles');

		if ($this->request->getQuery('order_status_id') !== null) {
			if ($this->request->getQuery('order_status_id') == 'all')
				$this->request->getSession()->delete('Shop.OrderStatus.id');
			else {
				$ids = [];
				if ($this->request->getSession()->check('Shop.OrderStatus.id'))
					$ids = explode(",", $this->request->getSession()->read('Shop.OrderStatus.id'));
				$newId = $this->request->getQuery('order_status_id');
				if (!in_array($newId, $ids))
					$ids[] = $newId;
				$this->request->getSession()->write('Shop.OrderStatus.id', implode(",", $ids));
			}
		}	
		
		if ($this->request->getQuery('payment_method') !== null) {
			if ($this->request->getQuery('payment_method') == 'all')
				$this->request->getSession()->delete('Shop.Orders.payment_method');
			else 
				$this->request->getSession()->write('Shop.Orders.payment_method', $this->request->getQuery('payment_method'));
		}
		
		if ($this->request->getQuery('refund_status') !== null) {
			if ($this->request->getQuery('refund_status') == 'all')
				$this->request->getSession()->delete('Shop.Orders.refund_status');
			else 
				$this->request->getSession()->write('Shop.Orders.refund_status', $this->request->getQuery('refund_status'));
		}
		
		if ($this->request->getQuery('article_id') !== null) {
			if ($this->request->getQuery('article_id') == 'all')
				$this->request->getSession()->delete('Shop.Articles.id');
			else {
				$ids = [];
				if ($this->request->getSession()->check('Shop.Articles.id'))
					$ids = explode(",", $this->request->getSession()->read('Shop.Articles.id'));
				$newId = $this->request->getQuery('article_id');
				if (!in_array($newId, $ids))
					$ids[] = $newId;
				$this->request->getSession()->write('Shop.Articles.id', implode(",", $ids));
			}
		}	
		
		if ($this->request->getQuery('last_name') !== null) {
			$last_name = urldecode($this->request->getQuery('last_name'));

			if ($last_name == '*')
				$this->request->getSession()->delete('Shop.InvoiceAddresses.last_name');
			else if ($last_name == 'none')
				$this->request->getSession()->write('Shop.InvoiceAddresses.last_name', 'none');
			else
				$this->request->getSession()->write('Shop.InvoiceAddresses.last_name', str_replace('_', ' ', $last_name));
		}

		if ($this->request->getQuery('country_id') !== null) {
			if ($this->request->getQuery('country_id') == 'all')
				$this->request->getSession()->delete('Shop.InvoiceAddresses.country_id');
			else
				$this->request->getSession()->write('Shop.InvoiceAddresses.country_id', $this->request->getQuery('country_id'));
		}	
		
		if ($this->request->getQuery('duplicates') !== null) {
			if ($this->request->getQuery('duplicates') === 'all')
				$this->request->getSession()->delete('Shop.Orders.duplicates');
			else
				$this->request->getSession()->write('Shop.Orders.duplicates', $this->request->getQuery('duplicates'));
		}	
		
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$conditions = array();
		$conditions['Orders.tournament_id'] = $tid;
		
		if ($this->request->getSession()->check('Shop.OrderStatus.id'))
			$conditions['order_status_id IN'] = explode(',', $this->request->getSession()->read('Shop.OrderStatus.id'));
		
		if ($this->request->getSession()->check('Shop.InvoiceAddresses.country_id'))
			$conditions['InvoiceAddresses.country_id'] = $this->request->getSession()->read('Shop.InvoiceAddresses.country_id');
		
		if ($this->request->getSession()->check('Shop.InvoiceAddresses.last_name')) {
			if ($this->request->getSession()->read('Shop.InvoiceAddresses.last_name') == 'none')
				$conditions[] = 'UPPER(InvoiceAddresses.last_name) COLLATE utf8_bin IS NULL';
			else
				$conditions[] = 'UPPER(InvoiceAddresses.last_name) COLLATE utf8_bin LIKE \'' . $this->request->getSession()->read('Shop.InvoiceAddresses.last_name') . '%\'';
		}
		
		// Filter by payment method
		if ($this->request->getSession()->check('Shop.Orders.payment_method')) {
			if ($this->request->getSession()->read('Shop.Orders.payment_method') === 'none')
				$conditions[] = 'Orders.payment_method IS NULL';
			else
				$conditions['Orders.payment_method'] = $this->request->getSession()->read('Shop.Orders.payment_method');
		}
		
		// Filter by refund status
		if ($this->request->getSession()->check('Shop.Orders.refund_status')) {
			if ($this->request->getSession()->read('Shop.Orders.refund_status') === 'refunding')
				$conditions[] = 
					'Orders.invoice_paid IS NOT NULL AND ' .
					'Orders.refund < Orders.paid - Orders.total - Orders.discount - ' .
					'	Orders.cancellation_fee + Orders.cancellation_discount ';
			if ($this->request->getSession()->read('Shop.Orders.refund_status') === 'refunded')
				$conditions[] = 'Orders.refund > 0';
		}
		
		// Filter by Article
		if ($this->request->getSession()->check('Shop.Articles.id')) {
			$conditions['Orders.id IN'] = 
					$this->OrderArticles->find()
						->select('order_id')
						->distinct()
						->where(['article_id IN' => explode(',', $this->request->getSession()->read('Shop.Articles.id'))])
			;
		}
		
		$this->paginate = array(
			'order' => array('Orders.created' => 'DESC'),
			'conditions' => $conditions,
			'contain' => [
				'OrderStatus',
				'InvoiceAddresses',
				'Users',
			],
			'sortWhitelist' => [
				'Orders.invoice',
				'OrderStatus.description',
				'Orders.email',
				'InvoiceAddresses.last_name',
				'Orders.total',
				'Orders.created',
				'Orders.accepted',
				'Orders.invoice_paid',
				'Orders.invoice_cancelled',
				'Orders.refund'
			]
		);
		
		if ($this->request->getSession()->read('Shop.Orders.duplicates') === 'duplicates') {
			$this->paginate['join'] = array(
				[
					'type' => 'INNER',
					'alias' => 'Duplicates',
					'table' => $this->Orders->find()
						->select(['dup_email' => 'email', 'dup_total' => 'total'])
						->where([
							'Orders.tournament_id' => $tid,
							'Orders.order_status_id IN' => [
								OrderStatusTable::getPaidId(),
								OrderStatusTable::getPendingId(),
								OrderStatusTable::getDelayedId(),
								OrderStatusTable::getWaitingListId()
							]
						])
						->group(['email', 'total'])
						->having(['COUNT(email) >' => 1, 'COUNT(total) >' => 1]),
					'conditions' => [
						'Orders.email = dup_email',
						'Orders.total = dup_total'
					]
				]
			);
		}

		$orders = $this->paginate();
		$this->set('orders', $orders);
		
		$this->set('order_status_id', $this->request->getSession()->read('Shop.OrderStatus.id'));
		
		$this->loadModel('Shop.OrderStatus');
		$this->set('orderstatus', $this->OrderStatus->find('list', array(
			'order' => 'description',
			'fields' => array('id', 'description')
		))->toArray());
		
		$this->set('article_id', $this->request->getSession()->read('Shop.Articles.id'));
		
		$this->loadModel('Shop.Articles');
		$this->set('articles', $this->Articles->find('list', array(
			'fields' => array('id', 'name'),
			'conditions' => array('tournament_id' => $tid),
			'order' => array('sort_order' => 'ASC')
		))->toArray());
		
		$this->loadModel('Shop.OrderAddresses');
		$this->loadModel('Shop.Countries');
		$this->set('countries', $this->Countries->find('list', array(
			'fields' => array('id', 'iso_code_3'),
			'conditions' => array(
				'id IN' => $this->OrderAddresses->find()->select(['country_id' => 'DISTINCT country_id'])->where(['type' => 'P'])
			),
			'order' => array('Countries.iso_code_3')
		))->toArray());
		$this->set('country_id', $this->request->getSession()->read('Shop.InvoiceAddresses.country_id'));
		
		$last_name = $this->request->getSession()->read('Shop.InvoiceAddresses.last_name');
		$allchars = array();

		if (!$last_name)
			$last_name = '';
		if ($last_name == 'none')
			$last_name = null;

		for ($count = 0; $count <= mb_strlen($last_name); $count++) {
			$conditions = array();
			if ($this->request->getSession()->check('Shop.OrderStatus.id'))
				$conditions['OrderStatus.id IN'] = explode(',', $this->request->getSession()->read('Shop.OrderStatus.id'));

			if ($count > 0)
				$conditions[] = 'UPPER(InvoiceAddresses.last_name) COLLATE utf8_bin LIKE \''. mb_substr($last_name, 0, $count) . '%\'';

			$tmp = $this->Orders->find('all', array(
				'contain' => ['InvoiceAddresses', 'OrderStatus'],
				'fields' => ['firstchar'=> 'DISTINCT LEFT(UPPER(InvoiceAddresses.last_name) COLLATE utf8_bin, ' . ($count + 1) . ')'],
				'conditions' => $conditions,
				'order' => ['firstchar COLLATE utf8_unicode_ci' => 'ASC']
			));

			$tmp = Hash::extract($tmp->toArray(), '{n}.firstchar');
			
			// Set::extract may return null
			if ($tmp === null)
				$tmp = array();

			// Make sure any selected sequence of characters is in the list
			if (mb_strlen($last_name) > $count && !in_array(mb_substr($last_name, 0, $count + 1), $tmp))
				$tmp[] = mb_substr($last_name, 0, $count + 1);

			// sort($tmp);

			$allchars[] = $tmp;
		}

		$this->set('allchars', $allchars);

		$this->set('last_name', $this->request->getSession()->read('Shop.InvoiceAddresses.last_name'));
		
		$this->set('duplicates', $this->request->getSession()->read('Shop.Orders.duplicates'));
		
		$tmp = $this->Orders->find('all', array(
			'fields' => ['method' => 'DISTINCT payment_method'],
			'conditions' => ['payment_method IS NOT NULL', 'payment_method <>' => ''],
			'order' => ['payment_method' => 'ASC']
		));
		$this->set('payment_methods', Hash::combine($tmp->toArray(), '{n}.method', '{n}.method'));
		$this->set('payment_method', $this->request->getSession()->read('Shop.Orders.payment_method'));
		
		$this->set('refund_statuses', [
			'refunding' => __('Pending'),
			'refunded' => __('Done')
		]);
		$this->set('refund_status', $this->request->getSession()->read('Shop.Orders.refund_status'));
	}
	
	
	function search() {
		$invoice = $this->request->getQuery('invoice');
		if (!$invoice) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		$id = $this->Orders->fieldByconditions('id', array(
			'invoice' => trim($invoice)
		));
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		return $this->redirect(array('action' => 'view', $id));
	}
	
	
	function view($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		$this->loadModel('Shop.Articles');
		$this->loadModel('Shop.OrderStatus');
		$this->loadModel('Shop.OrderArticles');
		$this->loadModel('Shop.Countries');
		$this->loadModel('Types');
		$this->loadModel('Nations');
		$this->loadModel('Registrations');
		$this->loadModel('Users');
		
		$order = $this->Orders->find('all', array(
			'contain' => array('OrderStatus', 'OrderComments' => array('Users')),
			'conditions' => array('Orders.id' => $id)
		))->first();
		
		$this->set('order', $order);
		
		if (empty($order['user_id']))
			$this->set('username', $order['email']);
		else
			$this->set('username', $this->Users->fieldByConditions('username', array('id' => $order['user_id'])));
		
		$items= $this->OrderArticles->find('all', array(
			'contain' => array('Articles'),
			'conditions' => array('order_id' => $id),
			'order' => array(
				'OrderArticles.cancelled' => 'ASC', 
				'Articles.sort_order' => 'ASC'
			)
		));
		
		// Repair wrong arrays: append ?repair=1
		if ($this->request->getQuery('repair')) {
			foreach ($items as $item) {
				if (empty($item['detail']))
					continue;
				$detail = unserialize($item['detail']);
				if (count($detail) === 1 && !empty($detail[0]) && !empty($detail[0]['Person'])) {
					$this->OrderArticles->updateAll(
						['detail' => serialize($detail[0]['Person'])],
						['id' => $item['id']]
					);
				}
			}
			
			$items= $this->OrderArticles->find('all', array(
				'contain' => array('Articles'),
				'conditions' => array('order_id' => $id),
				'order' => array(
					'OrderArticles.cancelled' => 'ASC', 
					'Articles.sort_order' => 'ASC'
				)
			));
		}
		
		$this->set('items', $items);
		
		$address = $this->Orders->find('all', array(
			'contain' => array('InvoiceAddresses'),
			'conditions' => array('Orders.id' => $id),
			// 'fields' => array('InvoiceAddress.*')
		))->first()->invoice_address;
		$address['email'] = $order->email;
		$this->set('address', $address);
		
		$this->set('countries', $this->Countries->find('list', array(
			'fields' => array('id', 'name')
		))->toArray());
		
		$this->set('id', $id);
		
		$payment = $this->_getPayment($order['payment_method']);
		// If still in state Invoice try default payments for any tries
		if ($order->payment_method === 'Invoice' && $order->order_status_id == OrderStatusTable::getInvoiceId())
			$payment = $this->_getPayment();
		$orderDetails = $payment->getOrderPayment($id);
		$this->set('orderDetails', $orderDetails);
		
		$this->set('regids', $this->Registrations->find('list', array(
			'fields' => array('person_id', 'id'),
			'conditions' => array(
				'tournament_id' => $order['tournament_id'],
				'person_id IN' => Hash::extract($items->toArray(), '{n}.person_id') + [0],
			),
		))->toArray());
		
		$this->set('types', $this->Types->find('list', array(
			'fields' => array('id', 'name')
		))->toArray());
		
		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'name')
		))->toArray());
		
		$this->set('articles', Hash::combine($this->Articles->find('all', array(
			'conditions' => array('tournament_id' => $this->request->getSession()->read('Tournaments.id'))
		))->toArray(), '{n}.id', '{n}'));		
	}
	
	
	function history($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		$this->loadModel('Shop.OrderHistories');

		$order = $this->Orders->find('all', array(
			'conditions' => array('Orders.id' => $id)
		))->first();

		$this->set('order', $order);
		
		$this->loadModel('Shop.OrderStatus');
		$stati = $this->OrderStatus->find('list', array(
			'fields' => array('id', 'description')
		))->toArray();
		
		$this->loadModel('Users');

		$histories = $this->OrderHistories->find('all', array(
			'contain' => array('Users'),
			'conditions' => array('order_id' => $order['id']),
			'order' => ['OrderHistories.created' => 'DESC']
		))->toArray();

		foreach ($histories as $history) {
			// $when = $history['OrderHistory']['created'];
			$field_name = $history['field_name'];
			$old_value = $history['old_value'];
			$new_value = $history['new_value'];

			$history['old_name'] = $old_value;
			$history['new_name'] = $new_value;

			if ($field_name == 'order_status_id') {
				if (!empty($old_value))
					$history['old_name'] = $stati[$old_value];

				if (!empty($new_value))
					$history['new_name'] = $stati[$new_value];
			} 

			if ($field_name == 'user_id') {
				if (!empty($old_value))
					$history['old_name'] = $this->Users->fieldByConditions('username', array('id' => $old_value));	

				if (!empty($new_value))
					$history['new_name'] = $this->Users->fieldByConditions('username', array('id' => $new_value));	

			}
		}

		$this->set('histories', $histories);
	}


	function revision($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		if (empty($this->request->getQuery('date'))) {
			$this->MultipleFlash->setFlash(__('Invalid date'), 'error');
			return $this->redirect(array('action' => 'history', $id));
		}

		$when = $this->request->getQuery('date');

		$this->loadModel('Shop.OrderHistories');
		$this->loadModel('Shop.OrderStatus');
		$this->loadModel('Shop.OrderComments');
		$this->loadModel('Shop.OrderArticles');
		$this->loadModel('Shop.OrderArticleHistories');
		$this->loadModel('Shop.Countries');
		$this->loadModel('Shop.Articles');
		$this->loadModel('Types');
		$this->loadModel('Nations');
		$this->loadModel('People');
		$this->loadModel('Users');	
		
		$order = $this->Orders->find('all', array(			
			'conditions' => array('Orders.id' => $id),
		))->first();
		
		$histories = $this->OrderHistories->find('all', array(
			'conditions' => array(
				'OrderHistories.order_id' => $id,
				'OrderHistories.created <=' => $when
			),
			'order' => ['OrderHistories.created' => 'ASC']
		))->toArray();

		foreach ($histories as $history) {
			$field_name = $history['field_name'];
			$old_value  = $history['old_value'];
			$new_value  = $history['new_value'];

			if ($field_name == 'created') {
				$order = unserialize($history['new_value']);
			} else {
				$order[$field_name] = $new_value;
			}
		}

		// added fields
		$order = array_merge($order, array(
			'paid' => 0.,
			'cancellation_discount' => 0.,
			'refund' => 0.
		));
		
		$articles = $this->OrderArticles->find('all', array(
			'conditions' => array(
				'OrderArticles.order_id' => $id,
				'OrderArticles.created <= ' => $when
			)
		))->toArray();
				
		foreach ($articles as &$article) {
			$histories = $this->OrderArticleHistories->find('all', array(
				'conditions' => array(
					'OrderArticleHistories.order_article_id' => $article['id'],
					'OrderArticleHistories.created > ' => $when					
				),
				'order' => ['OrderArticleHistories.created' => 'DESC']
			));
			
			foreach ($histories as $history) {
				$field_name = $history['field_name'];
				$old_value  = $history['old_value'];
				$new_value  = $history['new_value'];

				if ($article->has($field_name))
					$article[$field_name] = $old_value;				
			}
		}
		
		if (!empty($order['order_status_id'])) {
			$status = $this->OrderStatus->find('all', array(
				'conditions' => array('id' => $order['order_status_id'])
			))->first();
			
			$order['order_status'] = $status;
		}

		if (!empty($order['user_id'])) {
			$order['user']['username'] = 
				$this->Users->fieldByConditions('username', array(
					'id' => $order['user_id']
				));
		}
		
		$comments = $this->OrderComments->find('all', [
			'conditions' => [
				'OrderComments.order_id' => $id,
				'OrderComments.created <=' => $when
			]
		]);
		
		// XXX Is it singular or plural?
		$order['order_comments'] = $comments->toArray();
		
		$this->set('order', $order);

		$address = $this->Orders->find('all', array(
			'contain' => array('InvoiceAddresses'),
			'conditions' => array('Orders.id' => $id),
			// 'fields' => array('InvoiceAddress.*')
		))->first()->invoice_address;
		$this->set('address', $address);
		
		$this->set('revision', $when);
		
		if (empty($order['user_id']))
			$this->set('username', $order['email']);
		else
			$this->set('username', $this->Users->fieldByConditions('username', array('id' => $order['user_id'])));
		
		$this->set('items', $articles);
		
		$this->set('countries', $this->Countries->find('list', array(
			'fields' => array('id', 'name')
		))->toArray());
		
		$this->set('id', $id);
		
		$payment = $this->_getPayment($order['payment_method']);
		$orderDetails = $payment->getOrderPayment($id);
		$this->set('orderDetails', $orderDetails);
		
		$order_article_ids = $this->OrderArticles->find('all', array(
			'conditions' => array('order_id' => $id),
			'fields' => array('id', 'order_id')
		))->toArray();
		
		$this->set('people', $this->People->find('all', array(
			'contain' => array('Users', 'Registrations'),
			'conditions' => array('People.id' => Hash::extract($order_article_ids, '{n.id')),
			'order' => array('Person.extern_id ASC')
		)));
		
		$this->set('types', $this->Types->find('list', array(
			'fields' => array('id', 'name')
		))->toArray());
		
		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'name')
		))->toArray());		
		
		$this->set('articles', Hash::combine($this->Articles->find('all', array(
			'conditions' => array('tournament_id' => $order['tournament_id'])
		))->toArray(), '{n}.id', '{n}'));		
		
		$this->render('view');
	}
	

	public function storno($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect(array('action' => 'index'));
		} 
		
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		if ($this->request->is(['get'])) {
			$this->request->getSession()->write('referer', $this->referer());
		}
		
		$referer = $this->request->getSession()->read('referer') ?: ['action' => 'index'];
		
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		if ($this->request->getData('cancel') !== null) {
			return $this->redirect($referer);
		}
		
		$this->loadModel('Shop.Articles');
		$this->loadModel('Shop.OrderStatus');

		$tmp = $this->Articles->find('all', [
			'conditions' => [
				'tournament_id' => $tid
			]
		]);
		
		$articles = array();
		foreach ($tmp as $t) {
			$articles[$t->id] = $t;
		}
		
		$this->set('articles', $articles);
			
		$order = $this->Orders->find('all', array(
			'contain' => array(
				'OrderArticles'
			),
			'conditions' => array(
				'Orders.id' => $id
			)
		))->first();

		$stati = $this->OrderStatus->find('list', array(
			'fields' => array('name', 'id')
		))->toArray();
		
		$this->set('stati', $stati);
		
		$allowedStatus = [
			$stati['INIT'],
			$stati['PEND'],
			$stati['PAID'],
			$stati['WAIT'],
			$stati['INVO'],
			$stati['DEL'],
		];
		
		if (!in_array($order['order_status_id'], $allowedStatus)) {
			$this->MultipleFlash->setFlash(__('Invalid order status'), 'error');
			return $this->redirect($referer);
		}

		if ($this->request->is(['post', 'put'])) {
			if ($this->_doStorno($id, $this->request->getData())) {
				$this->MultipleFlash->setFlash(__('Order cancelled'), 'success');			
			
				return $this->redirect($referer);
			} else {
				$this->MultipleFlash->setFlash(__('Could not cancel order'), 'error');				
			}
		}
			
		$this->set('order', $order);

		$payment = $this->_getPayment($order['payment_method']);
		$orderDetails = $payment->getOrderPayment($id);
		$this->set('orderDetails', $orderDetails);

		$this->loadModel('Nations');
		$this->set('nations', $this->Nations->find('list', array('fields' => array('id', 'name')))->toArray());

		$this->loadModel('Types');
		$this->set('types', $this->Types->find('list', array('fields' => array('id', 'name')))->toArray());
	}
	
	
	public function storno_pending() {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		if ($this->request->is(['get']))
			return;

		$date = $this->request->getData('date');
		if (is_array($date))
			$date = new Date($date['year'] . '-' . $date['month'] . '-' . $date['day']);
		else
			$data = new Date($date);
		
		$data = [
			'Storno' => [
				'all' => true,
				'sendMail' => true
			],
			'cancellation_discount' => 0
		];
		
		// All pending orders older than 14 days
		// Take 15 days from now so we don't have to look at the hour
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
		
		$ids = $this->Orders->find('all', array(
			'contain' => ['InvoiceAddresses'],
			'fields' => 'Orders.id',
			'conditions' => $conditions
		))->disableHydration()->toArray();
					
		$success = 0; 
		$failed = 0;
		
		foreach ($ids as $id) {
			set_time_limit(0);
			
			if ($this->_doStorno($id['id'], $data))
				$success++;
			else
				$failed++;
		}
		
		$this->MultipleFlash->setFlash(__('{0} Orders cancelled', $success), 'success');
		if ($failed > 0)
			$this->MultipleFlash->setFlash(__('Cancellation of {0} orders failed', $failed), 'error');
		
		return $this->redirect(['action' => 'index']);
	}
	
			
	private function _doStorno($id, $data) {
		$this->loadModel('Shop.Articles');
		$this->loadModel('Shop.OrderArticles');
		$this->loadModel('Shop.OrderStatus');

		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$order = $this->Orders->find('all', array(
			'contain' => array(
				'OrderArticles'
			),
			'conditions' => array(
				'Orders.id' => $id
			)
		))->first();

		$tmp = $this->Articles->find('all', [
			'conditions' => [
				'tournament_id' => $tid
			]
		]);
		
		$articles = array();
		foreach ($tmp as $t) {
			$articles[$t->id] = $t;
		}
		
		$stati = $this->OrderStatus->find('list', array(
			'fields' => array('name', 'id')
		))->toArray();
		
		$isPaid = $order['order_status_id'] === $stati['PAID'];
		$isWait = $order['order_status_id'] === $stati['WAIT'];
		$sendMail = 
				!empty($data['Storno']['sendMail']) && (
					$order['order_status_id'] === $stati['PAID'] ||
					$order['order_status_id'] === $stati['PEND'] ||
					$order['order_status_id'] === $stati['WAIT']
				);
		$cancelPeople = 
				$isPaid || 
				$order['order_status_id'] === $stati['INVO'];

		$all = $data['Storno']['all'];

		$refund = 0;
		$fee = 0;
		$discount = (float) $data['cancellation_discount'];

		if (empty($data['Player']))
			$data['Player'] = array();
		if (empty($data['Accompanying']))
			$data['Accompanying'] = array();
		if (empty($data['Item']))
			$data['Item'] = array();

		$ct = date('Y-m-d H:i:s');

		$this->loadModel('Registrations');

		// TODO: Spieler, Acc. loeschen, Mails an Benutzer und Doppelpartner
		// Das ist der Code vom RegistrationController, wie kann ich den hier ausfuehren?
		// Eventiell erstmal garnicht, so viele Stornos gibt es nicht. Orga muss die halt
		// manuell loeschen.

		$updateOrder = array(				
			'id' => $id,
			'cancellation_fee' => 0.,
			'total' => 0.,
			'cancellation_discount' => $order['cancellation_discount'] + intval($discount),
			'order_articles' => array()
		);

		if ($all) {
			$updateOrder['order_status_id'] = $stati['CANC'];
			$updateOrder['invoice_cancelled'] = date('Y-m-d H:i:s');

			foreach ($order['order_articles'] as $oa) {
				// Already cancelled, no need to do it again
				if (!empty($oa['cancelled']))
					continue;

				if ($isPaid) {
					$refund += $oa['total'];
					$cancellationFee = $oa['quantity'] * $oa['price'] * $this->_shopSettings['cancellation_fee'] / 100.;
					$fee += $cancellationFee;
				} else {
					$cancellationFee = 0.;
				}

				$updateOrder['order_articles'][] = array(
					'id' => $oa['id'],
					'order_id' => $oa['order_id'],
					'cancellation_fee' => $cancellationFee,
					'cancelled' => $ct,						
				);
			}
		} else {
			$allArticles = null;

			foreach ($order['order_articles'] as $oa) {
				$allArticles[$oa['id']] = $oa;
			}

			$updatedPlayers = array();
			$updatedAccs = array();

			$cancelledPlayers = array();
			$cancelledAccs = array();

			$cancelledArticles = array();

			// Cancel / Undo cancel players
			foreach ($data['Player'] as $player) {
				$playerArticle = $allArticles[$player['id']];
				$players = unserialize($playerArticle['detail']);

				if (empty($player['storno'])) {
					$updatedPlayers[$player['id']] = $players;
				} else {
					$cancelledPlayers[$player['id']] = $players;
				}
			}

			foreach ($cancelledPlayers as $playerId => $cancelled) {
				$playerArticle = $allArticles[$playerId];

				// Already cancelled
				if (!empty($playerArticle['cancelled']))
					continue;

				if ($isPaid) {
					$cancellationFee = $playerArticle['price'] * $this->_shopSettings['cancellation_fee'] / 100.;

					$refund += $playerArticle['price'];
					$fee += $cancellationFee;
				} else {
					$cancellationFee = 0.;
				}

				$playerArticle['cancelled'] = $ct;
				$playerArticle['cancellation_fee'] = $cancellationFee;
				$updateOrder['order_articles'][] = $playerArticle->toArray();

				if (!empty($playerArticle['person_id'])) {
					$linkedArticles = $this->OrderArticles->find('all', array(
						'contain' => array('Articles'),
						'conditions' => array(
							'person_id' => $playerArticle['person_id'],
							'Articles.visible' => 1,
							'cancelled IS NULL'
						),
						'fields' => array('OrderArticles.id')
					));

					foreach ($linkedArticles as $la) {
						$cancelledArticles[] = $allArticles[$la['order_articles']['id']];
					}
				}
			}

			foreach ($updatedPlayers as $playerId => $updated) {
				$playerArticle = $allArticles[$playerId];

				$playerArticle['detail'] = serialize($updated);
				$playerArticle['quantity'] = 1;
				$playerArticle['total'] = $playerArticle['price'];
				$playerArticle['cancelled'] = null;
				$playerArticle['cancellation_fee'] = 0.;

				$updateOrder['order_articles'][] = $playerArticle->toArray();
			}

			// Same for acc. persons
			foreach ($data['Accompanying'] as $acc) {
				$accArticle = $allArticles[$acc['id']];
				$accs = unserialize($accArticle['detail']);

				if (empty($acc['storno'])) {
					$updatedAccs[$acc['id']] = $accs;
				} else {
					$cancelledAccs[$acc['id']] = $accs;
				}
			}

			foreach ($cancelledAccs as $accId => $cancelled) {
				$accArticle = $allArticles[$accId];

				// Already cancelled
				if (!empty($accArticle['cancelled']))
					continue;

				if ($isPaid) {
					$cancellationFee = $accArticle['price'] * $this->_shopSettings['cancellation_fee'] / 100.;

					$refund += $accArticle['price'];
					$fee += $cancellationFee;
				} else {
					$cancellationFee = 0.;
				}

				$accArticle['cancelled'] = $ct;
				$accArticle['cancellation_fee'] = $cancellationFee;
				$updateOrder['order_articles'][] = $accArticle->toArray();

				if (!empty($accArticle['person_id'])) {
					$linkedArticles = $this->OrderArticles->find('all', array(
						'contain' => ['Articles'],
						'conditions' => array(
							'person_id' => $accArticle['person_id'],
							'Articles.visible' => 1,
							'cancelled IS NULL'
						),
						'fields' => array('OrderArticles.id')
					));

					foreach ($linkedArticles as $la) {
						$cancelledArticles[] = $allArticles[$la['id']];
					}
				}
			}

			foreach ($updatedAccs as $accId => $updated) {
				$accArticle = $allArticles[$accId];

				$accArticle['detail'] = serialize($updated);
				$accArticle['quantity'] = 1;
				$accArticle['total'] = $accArticle['price'];
				$accArticle['cancelled'] = null;
				$accArticle['cancellation_fee'] = 0.;

				$updateOrder['order_articles'][] = $accArticle->toArray();

				$updateOrder['total'] += $accArticle['total'];
			}

			// And now all linked articles (order by the person)
			foreach ($cancelledArticles as $ca) {
				if ($isPaid) {
					$cancellationFee = $ca['price'] * $this->_shopSettings['cancellation_fee'] / 100.;

					$refund += $ca['price'];
					$fee += $cancellationFee;
				} else {
					$cancellationFee = 0.;
				}

				$ca['cancelled'] = $ct;
				$ca['cancellation_fee'] = $cancellationFee;
				$updateOrder['order_articles'][] = $ca->toArray();					
			}

			// All other items
			foreach ($data['Item'] as $item) {
				$itemId = $item['id'];
				$itemArticle = $allArticles[$itemId];

				// If item is not cancelled, the combobox is disabled and
				// the value not included in POST data
				$count = isset($item['quantity']) ? $item['quantity'] : $itemArticle['quantity' ];					

				// Cancelled and already cancelled
				if (!empty($item['storno']) && !empty($itemArticle['cancelled']))
					continue;

				if (empty($item['storno'])) {
					$itemArticle['quantity'] = $count;
					$itemArticle['total'] = $count * $itemArticle['price'];
					$itemArticle['cancelled'] = null;
					$itemArticle['cancellation_fee'] = 0.;

					$updateOrder['order_articles'][] = $itemArticle->toArray();

					continue;
				}

				if ($count == $itemArticle['quantity']) {
					if ($isPaid) {
						$cancellationFee = $itemArticle['price'] * $this->_shopSettings['cancellation_fee'] / 100.;

						$refund += $count * $itemArticle['price'];
						$fee += $count * $cancellationFee;
					} else {
						$cancellationFee = 0.;
					}

					$itemArticle['cancelled'] = $ct;
					$itemArticle['cancellation_fee'] = $count * $cancellationFee;
					$updateOrder['order_articles'][] = $itemArticle->toArray();
				} else {
					if ($isPaid) {
						$cancellationFee = $itemArticle['price'] * $this->_shopSettings['cancellation_fee'] / 100.;

						$refund += $count * $itemArticle['price'];
						$fee += $count * $cancellationFee;
					} else {
						$cancellationFee = 0.;
					}

					$cancelArticle = array_merge(
						$itemArticle->toArray(),
						array(
							'id' => false,
							'quantity' => $count,
							'total' => $count * $itemArticle['price'],
							'cancelled' => $ct,
							'cancellation_fee' => $count * $cancellationFee,
						)
					);

					$updateOrder['order_articles'][] = $cancelArticle;

					$itemArticle['quantity'] -= $count;
					$itemArticle['total'] -= $count * $itemArticle['price'];

					$updateOrder['total'] += $itemArticle['total'];

					$updateOrder['order_articles'][] = $itemArticle->toArray();
				}
			}
		}

		$updateOrder['cancellation_fee'] += $fee;

		// TODO: $discount abspeichern, evtl. als eigener Artikel
		
		$order = $this->Orders->patchEntity($order, $updateOrder);
		if (!$this->Orders->save($order, array('modified' => $ct))) {
			return false;
		}

		// Now cancel people if they are in the system
		// A better way to do that would be to collect the person_id from the articles
		if ($cancelPeople) {
			if ($all) {
				$this->loadModel('Registrations');

				$regIds = $this->Registrations->find('list', array(
					'fields' => array('Registrations.id', 'Registrations.cancelled'),
					'conditions' => array('OrderArticles.order_id' => $id),
					'join' => array(
						array('table' => 'people', 'alias' => 'People', 'type' => 'INNER', 'conditions' => 'Registrations.person_id = People.id'),
						array('table' => 'shop_order_articles', 'alias' => 'OrderArticles', 'type' => 'INNER', 'conditions' => 'People.id = OrderArticles.person_id')
					)
				))->toArray();

				foreach ($regIds as $regId => $cancelled) {
					if (!empty($cancelled))
						continue;

					$this->RegistrationUpdate->_delete($regId, array('modified' => $ct));
				}
			} else {
				foreach ($data['Player'] as $player) {
					if (empty($player['id']))
						continue;

					if (empty($player['storno']))
						continue;

					$pid = $this->OrderArticles->fieldByConditions('person_id', array(
						'id' => $player['id']
					));
					$reg = $this->Registrations->find('all', array(
						'contain' => array('People'),
						'conditions' => array('People.id' => $pid)
					))->first();

					if (!empty($reg['cancelled']))
						continue;

					$this->RegistrationUpdate->_delete(
							$reg['id'], array('modified' => $ct)
					);
				}

				foreach ($data['Accompanying'] as $acc) {
					if (empty($acc['id']))
						continue;

					if (empty($acc['storno']))
						continue;

					$pid = $this->OrderArticles->fieldByConditions('person_id', array(
						'id' => $acc['id']
					));
					$reg = $this->Registrations->find('all', array(
						'contain' => array('People'),
						'conditions' => array('People.id' => $pid)
					))->first();

					if (!empty($reg['cancelled']))
						continue;

					$this->RegistrationUpdate->_delete(
							$reg['id'], array('modified' => $ct)
					);
				}
			}
		}

		if ($isPaid && ($refund - $fee + $discount) > 0) {
			// $data['Storno']['refund'] is not set if refund is not applicable
			// But we are only interested if the value is true
			if (!empty($data['Storno']['refund'])) {
				// Only orders for which we received payment can be refunded
				$payment = $this->_getPayment($order['payment_method']);
				// Payment provider should write error message if refund failed
				if ($payment->storno($id, $refund - $fee + $discount)) {
					$this->MultipleFlash->setFlash(
							__('Refunded {0} {1}', 
								$refund - $fee + $discount, 
								$this->_shopSettings['currency']
							), 
							'success'
					);
					
					// Update order
					$order->refund += $refund - $fee + $discount;
					$this->Orders->save($order, ['modified' => $ct]);
				}
			}
		}

		// Send Mail only if the status allowed it (it was a valid order) and if we cancelled all of it.
		// If we cancel only part of the order a new invoice should be sent instead, but we don't do it yet.
		if ($sendMail && $all) {
			$this->loadModel('Tournaments');
			$tname = $this->Tournaments->fieldByConditions('name', array('id' => $tid));

			$invoice = $this->Orders->fieldByConditions('invoice',  ['id' => $id]);
			$orderDate = $this->Orders->fieldByConditions('created', ['id' => $id]);

			$replyTo = $this->_shopSettings['email'];
			$to = $order['email'];
			$bcc = $this->Users->fieldByConditions('email', array('username' => 'theis'));

			// Detect language
			if (!empty($order['language']))
				$lang = $order['language'];
			if (empty($lang))
				$lang = 'eng';

			$oldLang = I18n::getLocale();
			I18n::setLocale($lang);

			// If an order was paid we would cancel it only on request.
			// If an order was unpaid we could cancel it on our own and on request, 
			// but it doesnt rreally matter
			$this->set('onRequest', $isPaid || $isWait);

			$this->OrderUpdate->setVarsForOrder($id);

			$email = new Email('default');
			$email
				->viewBuilder()->setTemplate('Shop.cancel', 'default');

			if (!empty($replyTo))
				$email->setReplyTo($replyTo);

			$email
				->setEmailFormat('both')
				->addHeaders(array(
					'X-Tournament' => $tname,
					'X-Type' => 'Storno',
					'X-' . $tname . '-Type' => 'Storno'
				))
				->setTo($to)
				->setBcc($bcc)
				->setSubject(__d('user', '[{0}] Cancellation of registration {1} from {2}', $tname, $invoice, date('Y-m-d', strtotime($orderDate))))
				->setViewVars($this->viewBuilder()->getVars())
			;

			if (!empty($replyTo))
				$email->addBcc($replyTo);

			$email->send();		

			if (!empty($oldLang))
				I18n::setLocale($oldLang);				

		}
		
		return true;
	}
	
	public function unstorno($id = null) {
		$this->loadModel('Shop.OrderArticles');
		$this->loadModel('Registrations');
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		$ct = date('Y-m-d H:i:s');
		
		$order = $this->Orders->find('all', array(
			'contain' => array('OrderArticles'),
			'conditions' => array('Orders.id' => $id)
		))->first();
		
		if (empty($order)) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		$referer = $this->referer();
		
		$allowedStatus = array(
			OrderStatusTable::getCancelledId(),
			OrderStatusTable::getPendingId(),
			OrderStatusTable::getDelayedId(),
			OrderStatusTable::getPaidId()
		);
		
		if (!in_array($order['order_status_id'], $allowedStatus)) {
			$this->MultipleFlash->setFlash(__('Invalid order status'), 'error');
			return $this->redirect($referer);			
		}
		
		// Count people already in the system
		// People are linked via OrderArticle::person_id
		$pids = $this->OrderArticles->find('all', array(
			'fields' => array('person_id' => 'DISTINCT OrderArticles.person_id'),
			'conditions' => array(
				'person_id IS NOT NULL',
				'order_id' => $id
			)
		))->toArray();
		
		$total = 0.;

		// If the order was paid or players are already in the system,
		// then set it to INVO, so discount etc. can be given 
		$order['order_status_id'] = 
				$order['invoice_paid'] || count($pids) > 0 ?
				OrderStatusTable::getInvoiceId() :
				OrderStatusTable::getPendingId()
		;
		$order['invoice_cancelled'] = null;
				
		foreach ($order['order_articles'] as $oa) {
			$oa['cancelled'] = null;
			$oa['cancellation_fee'] = 0.;
			$total += $oa['total'];
		}
		
		$order['total'] = $total;
		
		// Mark order_articles explicitely as dirty
		$order->setDirty('order_articles', true);
		
		// Start transaction. The model argument is a dummy, hopefully ...
		$db = $this->Orders->getConnection();
		$db->begin();
		
		if (!$this->Orders->save($order, array('modified' => $ct))) {
			$db->rollback();
			$this->MultipleFlash->setFlash(__('Failed to undo order storno'), 'error');			
			return $this->redirect($referer);
		}
		
		// Restore registrations
		foreach ($pids as $p) {
			$pid = $p['person_id'];
			// We need the registration as array in the component
			$registration = $this->Registrations->find('all', array(
				'contain' => array('People', 'Participants'),
				'conditions' => array('Registrations.person_id' => $pid)
			))->first()->toArray();
			
			// Don't update people who were not cancelled before
			if (empty($registration['cancelled']))
				continue;

			if (!empty($registration)) {
				unset($registration['created']);
				unset($registration['modified']);

				$registration['cancelled'] = null;
				if (!empty($registration['participant']['id'])) {
					$registration['participant']['cancelled'] = 0;
					$registration['participant']['single_cancelled'] = 0;
					$registration['participant']['double_cancelled'] = 0;
					$registration['participant']['mexed_cancelled'] = 0;
					$registration['participant']['team_cancelled'] = 0;

					$registration['participant']['double_partner_id'] = null;
					$registration['participant']['mixed_partner_id'] = null;

					unset($registration['participant']['created']);
					unset($registration['participant']['modified']);
				} else {
					unset($registration['participant']);
				}

				if (!$this->RegistrationUpdate->_save($registration, array())) {
					$db->rollback();
					$this->MultipleFlash->setFlash(__('Failed to restore registration'), 'error');
					return $this->redirect($this->referer());
				}
			}
		}
		
		if (!$db->commit()) {
			$this->MultipleFlash->setFlash(__('Could not save changes'), 'error');
		} else {		
			$this->MultipleFlash->setFlash(__('Order storno undone'), 'success');
		}
		
		return $this->redirect($referer);
	}
	
	public function edit_invoice($id = null) {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		// Allow for discount and change of status
		$order = $this->Orders->get($id, array(
			'contain' => array('OrderComments' => array('Users')),
		));
		
		if ($this->request->is(['post', 'put'])) {
			$split = $order->invoice_split;
			$order = $this->Orders->patchEntity($order, $this->request->getData());
			
			// If split changes then reset to Invoice
			if ($split != $order->invoice_split)
				$order->order_status_id = OrderStatusTable::getInvoiceId ();

			$count = count($order['order_comments']);

			if ($count > 0) {
				if (!empty($order['order_comments'][$count-1]['comment']) && empty($order['order_comments'][$count-1]['id']))
					$order['order_comments'][$count-1]['user_id'] = $this->_user->id;
				else
					unset($order['order_comments'][$count-1]);
			}
			
			if ($this->Orders->save($order)) {
				$this->MultipleFlash->setFlash(__('Order updated'), 'info');
				return $this->redirect(array('controller' => 'Orders', 'action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('Could not update order'), 'error');
			}			
		}		

		$this->set('order', $order); // To access the comments in the view

	}
	
	public function edit_address($id = null) {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		$order = $this->Orders->find('all', array(
			'contain' => array('InvoiceAddresses'),
			'conditions' => array('Orders.id' => $id)
		))->first();

		if ($this->request->is('get')) {

			$this->set('order', $order);
			
			$this->loadModel('Shop.Countries');
			$this->set('countries', $this->Countries->find('list', array(
				'fields' => array('id', 'name'),
				'order' => array('name')
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
		
		$this->set('countryCode', $cc);
			$this->set('countryCodes', $this->Countries->find('list', array(
				'fields' => array('id', 'iso_code_2'),
				'order' => array('iso_code_2' => 'ASC')
			))->toArray());
		
		} else if ($this->request->is(['ppost', 'put'])) {
			$data = $this->request->getData();
						
			$data['invoice_address'] = array_merge(
					$data['invoice_address'],
					array(
						'order_id' => $id,
						'type' => 'P'
					)
			);

			$order = $this->Orders->patchEntity($order, $data);
			
			if ($this->Orders->save($order)) {
				$this->MultipleFlash->setFlash(__('Invoice address has been saved'), 'success');				
				
				// Update username
				if (!empty($order['user_id'])) {
					$this->loadModel('Users');
					$user = $this->Users->find('all', array(
						'conditions' => array('id'=> $order['user_id'])
					))->first();
					
					if (!empty($user) && $user['email'] !== $order['email']) {
						if ($user['username'] === $user['email'])
							$user['username'] = $order['email'];
						$user['email'] = $order['email'];
						
						if (!$this->Users->save($user))
							$this->MultipleFlash->setFlash(__('Could not update user'), 'warning');
					}
				}
			} else {				
				$this->MultipleFlash->setFlash(__('Invoice address could not be saved'), 'error');
			}
			
			return $this->redirect(array('action' => 'view', $id));
		} else {
			return $this->redirect(array('action' => 'view', $id));
		}
	}
	
	
	public function edit_person($id = null, $idx = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$this->loadModel('Shop.Articles');
		$this->loadModel('Shop.OrderArticles');
		$article = $this->OrderArticles->find('all', array(
			'conditions' => array('id' => $id)
		))->first();
		
		if (empty($article)) {
			$this->MultipleFlash->setFlash(__('Invalid order'), 'error');
			return $this->redirect(array('action' => 'index'));			
		}
		
		if ($this->request->getData('cancel') !== null) {
			return $this->redirect(array('action' => 'view', $article['order_id']));
		} 		
		
		if (!empty($article['cancelled'])) {
			$this->MultipleFlash->setFlash(__('Article already cancelled'), 'error');
			return $this->redirect(array('action' => 'view', $article['order_id']));
		}
		
		if (empty($article['detail'])) {
			$this->MultipleFlash->setFlash(__('Invalid article'), 'error');
			return $this->redirect(array('action' => 'view', $article['order_id']));
		}
		
		$person = unserialize($article['detail']);
					
		$order = $this->Orders->find('all', array(
			'conditions' => array('id' => $article['order_id'])
		))->first();

		// Old format
		if (isset($person['Person']))
			$person = $person['Person'];
		
		// Sometimes 'type' is missing
		if (!isset($person['type']))
			$person['type'] = $this->Articles->fieldByConditions('name', ['id' => $article['article_id']]);
			
		if ($this->request->is(['post', 'put'])) {
			$person = array_merge($person, $this->request->getData());
			
			// dob should be stored as a string, not as object or array
			if ($person['dob'] instanceof \Cake\I18n\Date)
				$person['dob'] = $person['dob']->format('Y-m-d');
			else if (is_array($person['dob'])) {
				$person['dob'] = 
						$person['dob']['year'] . '-' . $person['dob']['month'] . '-' . $person['dob']['day'];
			}
							
			$article['detail'] = serialize($person);
			
			unset($article['modified']);
			
			if ($this->OrderArticles->save($article)) {
				$this->MultipleFlash->setFlash(__('Person has been updated'), 'success');
				
				$this->loadModel('Registration');
				$this->loadModel('Person');
				$this->loadModel('Type');
				
			} else {
				$this->MultipleFlash->setFlash(__('Could not update person'), 'error');
			}
			
			return $this->redirect(array('action' => 'view', $article['order_id']));
		}
		
		$this->request = $this->request->withParsedBody($person);
		
		$this->set('order', $order);	
		$this->set('person', $person);

		$this->loadModel('Competitions');
		$maxYear = $this->Competitions->fieldByConditions(
			'Competitions.born', 
			array('tournament_id' => $tid), 
			['order' => ['born' =>  'DESC']]
		);

		if ($maxYear > 0)
			$this->set('maxYear', $maxYear);

		$this->loadModel('Nations');

		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'description'), 
			'order' => 'description'
		))->toArray());			
	}
	
	
	public function settings() {
		if ($this->request->getData('cancel') !== null) {
			return $this->redirect(array('action' => 'index'));
		}
		
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		} 
		
		$this->loadModel('Shop.OrderSettings');
		
		$settings = $this->OrderSettings->find('all', array(
			'contain' => ['OrderCancellationFees'],
			'conditions' => array(
				'tournament_id' => $this->request->getSession()->read('Tournaments.id')			
			)
		))->first();
		
		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->getData();
			
			$data['invoice_header'] = trim($data['invoice_header']);
			$data['invoice_footer'] = trim($data['invoice_footer']);
			if (empty($data['invoice_header']))
				$data['invoice_header'] = null;
			if (empty($data['invoice_footer']))
				$data['invoice_footer'] = null;
			
			// Leere Eintraege ausfiltern
			$data['order_cancellation_fees'] = 
					array_filter($data['order_cancellation_fees'], function($fee) {
						$ret = !empty($fee['fee']) && !empty($fee['start']);
						return $ret;
					});
									
			$settings = $this->OrderSettings->patchEntity($settings, $data);

			if ($this->OrderSettings->save($settings)) {
				$this->MultipleFlash->setFlash(__('The settings have been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The settings could not be saved. Please, try again.'), 'error');
			}
		}	
		
		$this->set('settings', $settings);
	}
	
	public function export() {
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('plugin' => null, 'controller' => 'tournaments', 'action' => 'index'));
		}
		
		// Disable debug output
		// Configure::write('debug', false);

		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$this->loadModel('Shop.OrderStatus');	
		$this->loadModel('Shop.OrderArticles');

		$articleConditions = array('OrderArticles.cancelled IS NULL');
		$conditions = array();
		$conditions['Orders.tournament_id'] = $tid;

		if ($this->request->getSession()->check('Shop.OrderStatus.id'))
			$conditions['order_status_id IN'] = explode(',', $this->request->getSession()->read('Shop.OrderStatus.id'));
		
		if ($this->request->getSession()->check('Shop.InvoiceAddresses.country_id'))
			$conditions['InvoiceAddresses.country_id'] = $this->request->getSession()->read('Shop.InvoiceAddresses.country_id');
		
		if ($this->request->getSession()->check('Shop.InvoiceAddresses.last_name'))
			$conditions['UPPER(InvoiceAddresses.last_name) COLLATE utf8_bin LIKE '] = $this->request->getSession()->read('Shop.InvoiceAddresses.last_name') . '%';
		
		// Filter by payment method
		if ($this->request->getSession()->check('Shop.Orders.payment_method')) {
			if ($this->request->getSession()->read('Shop.Orders.payment_method') === 'none')
				$conditions[] = 'Orders.payment_method IS NULL';
			else
				$conditions['Orders.payment_method'] = $this->request->getSession()->read('Shop.Orders.payment_method');
		}
		
		// Filter by Article
		if ($this->request->getSession()->check('Shop.Articles.id')) {
			$aids = explode(',', $this->request->getSession()->read('Shop.Articles.id'));
			$conditions['Orders.id IN'] = 
					$this->OrderArticles->find()
						->select('order_id')
						->distinct()
						->where(['article_id IN' => $aids])
			;
			
			$articleConditions['OrderArticles.article_id IN'] = $aids;
		}
		
		$orders = $this->Orders->find('all', array(
			'contain' => array(
				'OrderArticles' => array('conditions' => $articleConditions),
				'InvoiceAddresses'
			),
			'conditions' => $conditions,
			'order' => array('Orders.created' => 'DESC')
		));
		
		$this->set('orders', $orders);
		
		$this->loadModel('Shop.Articles');
		$articles = $this->Articles->find('all', array(
			'conditions' => array('tournament_id' => $tid),
			'order' => ['sort_order' => 'ASC']
		));

		$this->set('articles', $articles);
		
		$sort_order = $this->Articles->find('list', array(
			'fields' => array('id', 'sort_order')
		))->toArray();
		$this->set('sort_order', $sort_order);
		
		$this->loadModel('Shop.OrderStatus');
		$stati = $this->OrderStatus->find('list', array(
			'fields' => array('id', 'name')
		))->toArray();
		$this->set('stati', $stati);
		
		$this->loadModel('Shop.Countries');
		$countries = $this->Countries->find('list', array(
			'fields' => array('id', 'iso_code_3')
		))->toArray();
		$this->set('countries', $countries);
		
		$countryNames= $this->Countries->find('list', array(
			'fields' => array('id', 'name')
		))->toArray();
		$this->set('countryNames', $countryNames);

		$this->response->withDownload('export.csv');
		// return $this->redirect(array('actions' => 'index'));
	}
	
	public function export_players() {
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('plugin' => null, 'controller' => 'tournaments', 'action' => 'index'));
		}
		
		// Disable debug output
		// Configure::write('debug', false);

		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$this->loadModel('Shop.OrderStatus');		

		$conditions = array();
		$conditions['Orders.tournament_id'] = $tid;
		$conditions[] = 'OrderArticles.cancelled IS NULL';

		if ($this->request->getSession()->check('Shop.OrderStatus.id'))
			$conditions['order_status_id IN'] = explode(',', $this->request->getSession()->read('Shop.OrderStatus.id'));
		
		if ($this->request->getSession()->check('Shop.InvoiceAddresses.country_id'))
			$conditions['InvoiceAddresses.country_id'] = $this->request->getSession()->read('Shop.InvoiceAddresses.country_id');
		
		if ($this->request->getSession()->check('Shop.InvoiceAddresses.last_name'))
			$conditions['UPPER(InvoiceAddresses.last_name) COLLATE utf8_bin LIKE '] = $this->request->getSession()->read('Shop.InvoiceAddresses.last_name') . '%';
		
		
		// Filter by payment method
		if ($this->request->getSession()->check('Shop.Orders.payment_method')) {
			if ($this->request->getSession()->read('Shop.Orders.payment_method') === 'none')
				$conditions[] = 'Orders.payment_method IS NULL';
			else
				$conditions['Orders.payment_method'] = $this->request->getSession()->read('Shop.Orders.payment_method');
		}
		
		$this->loadModel('Shop.Articles');
		$aid = $this->Articles->field('id', [
			'tournament_id' => $tid,
			'name' => 'PLA'
		]);
		
		$conditions['OrderArticles.article_id'] = $aid;
		
		$this->loadModel('Shop.OrderArticles');
		$articles = $this->OrderArticles->find()
				->contain([
					'Articles',
					'Orders' => [
						'InvoiceAddresses'
					]
				])
				->where($conditions)
		;
		
		if ($this->request->getSession()->read('Shop.Orders.duplicates') === 'duplicates') {
			$DupOrders = TableRegistry::get('DupOrders', ['className' => 'Shop.Orders']);
			
			$subquery = $DupOrders->find()
				->select(['DupOrders.id'])
				->where([
					'Orders.tournament_id = DupOrders.tournament_id',
					'Orders.email = DupOrders.email',
					'Orders.total = DupOrders.total',
					'Orders.id <> DupOrders.id',
					'DupOrders.order_status_id IN' => [
						OrderStatusTable::getPaidId(),
						OrderStatusTable::getPendingId(),
						OrderStatusTable::getDelayedId(),
						OrderStatusTable::getWaitingListId()						
					]
				])
			;
			
			$articles->andWhere(function(QueryExpression $exp, Query $q) use ($subquery) {
				return $exp->exists($subquery);
			});
		}
		
		$this->set('articles', $articles->all());
		
		$this->loadModel('Nations');
		$this->set('nations', $this->Nations->find('list', array(
			'fields' => ['id', 'name']
		))->toArray());
		
		$this->loadModel('Competitions');
		
		$men = $this->Competitions->find('list', array(
			'conditions' => [
				'tournament_id' => $tid,
				'sex' => 'M'
			],
			'keyField' => 'born',
			'valueField' => 'name',
			'groupField' => 'type_of',
			'order' => [
				'type_of' => 'ASC',
				'born' => 'DESC'
			]
		))->toArray();
		$women = $this->Competitions->find('list', array(
			'conditions' => [
				'tournament_id' => $tid,
				'sex' => 'F'
			],
			'keyField' => 'born',
			'valueField' => 'name',
			'groupField' => 'type_of',
			'order' => [
				'type_of' => 'ASC',
				'born' => 'DESC'
			]
		))->toArray();
		$this->set('competitions', ['M' => $men, 'F' => $women]);
		
		$this->response->withDownload('export.csv');		
	}
}

/*
class MySoapClient extends SoapClient {
	public function __doRequest($request, $location, $action, $version, $one_way = 0) {
		file_put_contents('/tmp/xxx', $request);
		return parent::__doRequest($request, $location, $action, $version, $one_way);
	}
}
*/

