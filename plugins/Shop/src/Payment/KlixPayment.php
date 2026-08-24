<?php /* Copyright (c) 2026 Christoph Theis */ ?>
<?php

namespace Shop\Payment;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utililty\Hash;

use Shop\Payment\AbstractPayment;
use Shop\Model\Table\OrderStatusTable;


// Implement Adapter to Klix
class KlixPayment extends AbstractPayment {
	private $configBaseName = 'Shop.PaymentProviders.Klix';

	public function prepare($amount) {
		$this->_controller->set('amount', $amount);
		$this->_controller->set('currency', $this->_controller->_shopSettings['currency']);
		
		$this->_controller->render('Shops/Payment/klix');				
	}

	public function confirm($orderId) {
		$this->_controller->loadModel('Tournaments');
		
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Shop.OrderArticles');
		
		$order = $this->_controller->Orders->get($orderId, [
			'contain' => ['InvoiceAddresses' => 'Countries']
		]);
		
		$amount = $order->outstanding;

		$tournament = $this->_controller->Tournaments->find()
					->where(['Tournaments.id'=> $order->tournament_id])
					->first()
		;
				
		// Get specific url for klix to redirect customer to
		$brandId = Configure::read($this->configBaseName . '.accountData.brandId');
		$apiKey = Configure::read($this->configBaseName . '.accountData.apiKey');
		$endpoint = Configure::read($this->configBaseName . '.endpoint');
		
		$klix = new \Klix\KlixApi($brandId, $apiKey, $endpoint);

		$client = new \Klix\Model\ClientDetails();
		$client->email = $order->email;
		$purchase = new \Klix\Model\Purchase();
		$purchase->client = $client;
		$details = new \Klix\Model\PurchaseDetails();
		$product = new \Klix\Model\Product();
		$product->name = __('{0}: Invoice no {1}', $tournament['name'], $order['invoice']);
		$product->price = $amount * 100;
		$details->products = [$product];
		$purchase->purchase = $details;
		$purchase->brand_id = $brandId;
		$purchase->success_callback = $this->_getCallbackUrl($orderId);
		$purchase->success_redirect = $this->_getSuccessUrl($orderId);
		$purchase->failure_redirect = $this->_getFailureUrl($orderId);

		$result = $klix->createPurchase($purchase);

		file_put_contents(TMP . '/klix/xxxconfirm-' . date('Ymd-His'), 
				print_r([
					'purchase' => $purchase,
					'result' => $result
				], true));
		
		// Callback does not contain a reference to the order, except the order_id
		// Threfor stor the id in result  so we can check the status later
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');
		
		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity([
					'order_id' => $order->id,
					'payment' => 'klix',
					'value' => json_encode(['purchase_id' => $result->id])
				])
		);
		
		$this->_controller->set('json_object', $result);
		$this->_controller->render('json');
	}

	public function process() {
		
	}
	
	public function completed($request) {
		file_put_contents(TMP . '/klix/xxxcompleted-' . date('Ymd-His'), 
				print_r([
					'POST' => $request->is(['post', 'put']), 
					'Request' => $request,
					'order_id' => $request->getQuery('order_id')
				], true)
		);
				
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');

		$orderId = $request->getQuery('order_id') ?? 0;

		try {
			$order = $this->_controller->Orders->get($orderId);
		} catch (InvalidPrimaryKeyException | RecordNotFoundException $_ex) {
			$this->_UNUSED($_ex);
			file_put_contents(TMP . '/klix/xxxerror-' . date('Ymd-His'), print_r(_ex, true));
			return;			
		}
		
		// TODO: Get status of order
		
		// Read (last) payment details for order to get purchase_id
		try {
			$details = $this->_controller->OrderPayments->find()
					->select(['value'])
					->where(['order_id' => $orderId])
					->order(['created' => 'DESC'])
					->first()
					?->value
			;
		} catch (InvalidPrimaryKeyException | RecordNotFoundException $_ex) {
			$this->_UNUSED($_ex);
			file_put_contents(TMP . '/klix/xxxerror-' . date('Ymd-His'), print_r($_ex, true));
			return;			
		}
		
		if ($details === null) {
			file_put_contents(TMP . '/klix/xxxerror-' . date('Ymd-His'), print_r(
				'Payment details is NULL', true));
			return;
		}
		
		$purchaseId = json_decode($details)->purchase_id ?? null;
		if (!$purchaseId) {
			file_put_contents(TMP . '/klix/xxxerror-' . date('Ymd-His'), print_r([
				'Purchase id not found in details',
				$details
			], true));
			return;
		}
		
		$apiKey = Configure::read($this->configBaseName . '.accountData.apiKey');
		$url = 'https://portal.klix.app/api/v1/purchases/' . $purchaseId . '/';

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Accept: application/json',
			'Authorization: Bearer ' . $apiKey
		]);

		$response = curl_exec($curl);
		$status = curl_getinfo($curl);
		
		if ($status['http_code'] !== 200) {
			file_put_contents(TMP . '/klix/xxxerror-' . date('Ymd-His'), print_r([
				'Cannot verify payment',
				'Url' => $url,
				'Response' => $response,
				'Status' => $status
			], true));

			$this->_controller->_onError($orderId, 'ERR');
			return;
		}				

		$data = json_decode($response, true);

		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity([
					'order_id' => $order->id,
					'payment' => 'klix',
					'value' => $response
				])
		);
		
		$status = 'ERR;';
		
		if ($data['status'] != 'paid')
			$status= 'ERR';
		else if ($data['payment']['amount'] < $order->outstanding * 100)
			$status = 'INCO';
		else
			$status = 'PAID';
		
		file_put_contents(TMP . '/klix/xxxstatus-' . date('Ymd-His'), 
				print_r([
					'Response' => $response,
					'Eval' => $data['status'],
					'Amount' => $data['payment']['amount'],
					'Due' => $order->outstanding * 100,
					'Result' => $status
				], true)
		);

		if ($status === 'PAID')
			$this->_controller->_onSuccess($orderId, 'PAID');
		else
			$this->_controller->_onError($orderId, $status);
	}

	
	public function success($request) {
		file_put_contents(TMP . '/klix/xxxsuccess-' . date('Ymd-His'), print_r(['request' => $request], true));	
		
		// After successul payment Klix redirects to here.
		// Details are already stored so all we have to do is get the order 
		// and check status
				
		$this->_controller->loadModel('Shop.Orders');

		$orderId = $request->getQuery('order_id') ?? 0;

		try {
			$order = $this->_controller->Orders->get($orderId);
		} catch (InvalidPrimaryKeyException | RecordNotFoundException $_ex) {
			$this->_UNUSED($_ex);
			file_put_contents(TMP . '/klix/xxxerror-' . date('Ymd-His'), print_r(_ex, true));
			
			return $this->_controller->_failure($orderId, __('Order {0} not found'));			
		}
		
		if ($order->order_status_id == OrderStatusTable::getPaidId())
			return $this->_controller->_success($orderId);
		// Callback may come before completed
		else if ($order->order_status_id == OrderStatusTable::getInitiateId())
			return $this->_controller->_success($orderId);			
		else if ($order->order_status_id == OrderStatusTable::getIncompleteId())
			return $this->_controller->_success($orderId);
		else {
			file_put_contents(TMP . '/klix/xxxerror-' . date('Ymd-His'), print_r([
				'Unexpected order status', 
				'Request' => $request,
				'Order' => $order], true));
			return $this->_controller->_failure($orderId, __('Unexpectred order status'));
		}
		
	}

	public function error($request) {
		file_put_contents(TMP . '/klix/xxxcompleted-' . date('Ymd-His'), 
				print_r([
					'POST' => $request->is(['post', 'put']), 
					'Request' => $request,
					'order_id' => $request->getQuery('order_id')
				], true)
		);
				
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');

		$orderId = $request->getQuery('order_id') ?? 0;

		try {
			$order = $this->_controller->Orders->get($orderId);
		} catch (InvalidPrimaryKeyException | RecordNotFoundException $_ex) {
			$this->_UNUSED($_ex);
			file_put_contents(TMP . '/klix/xxxerror-' . date('Ymd-His'), print_r(_ex, true));
			return;			
		}
		
		// TODO: Get status of order
		
		// Read (last) payment details for order to get purchase_id
		try {
			$details = $this->_controller->OrderPayments->find()
					->select(['value'])
					->where(['order_id' => $orderId])
					->order(['created' => 'DESC'])
					->first()
					?->value
			;
		} catch (InvalidPrimaryKeyException | RecordNotFoundException $_ex) {
			$this->_UNUSED($_ex);
			file_put_contents(TMP . '/klix/xxxerror-' . date('Ymd-His'), print_r($_ex, true));
			return;			
		}
		
		if ($details === null) {
			file_put_contents(TMP . '/klix/xxxerror-' . date('Ymd-His'), print_r(
				'Payment details is NULL', true));
			return;
		}
		
		$purchaseId = json_decode($details)->purchase_id ?? null;
		if (!$purchaseId) {
			file_put_contents(TMP . '/klix/xxxerror-' . date('Ymd-His'), print_r([
				'Purchase id not found in details',
				$details
			], true));
			return;
		}
		
		$apiKey = Configure::read($this->configBaseName . '.accountData.apiKey');
		$url = 'https://portal.klix.app/api/v1/purchases/' . $purchaseId . '/';

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Accept: application/json',
			'Authorization: Bearer ' . $apiKey
		]);

		$response = curl_exec($curl);
		$status = curl_getinfo($curl);
		
		if ($status['http_code'] !== 200) {
			file_put_contents(TMP . '/klix/xxxerror-' . date('Ymd-His'), print_r([
				'Cannot verify payment',
				'Url' => $url,
				'Response' => $response,
				'Status' => $status
			], true));

			$this->_controller->_onError($orderId, 'ERR');
			return;
		}				

		$data = json_decode($response, true);

		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity([
					'order_id' => $order->id,
					'payment' => 'klix',
					'value' => $response
				])
		);
		
		$status = 'ERR;';
		
		if ($data['status'] != 'paid')
			$status= 'ERR';
		else if ($data['payment']['amount'] < $order->outstanding * 100)
			$status = 'INCO';
		else
			$status = 'PAID';
		
		file_put_contents(TMP . '/klix/xxxstatus-' . date('Ymd-His'), 
				print_r([
					'Response' => $response,
					'Eval' => $data['status'],
					'Due' => $order->outstanding * 100,
					'Result' => $status
				], true)
		);

		$this->_controller->_onError($orderId, $status);
		return $this->_controller->_failure($orderId, __('Payment failed'));
	}

	public function storno($orderId, $amount) {
		
	}

	public function getOrderPayment($orderId) {
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');
		
		$payments = $this->_controller->OrderPayments->find('all', array(
			'conditions' => array('order_id' => $orderId),
			'order' => ['created' => 'DESC']
		));
		
		$ret = [];
		foreach ($payments as $p) {
			$details = json_decode($p->value, true);
			$details['id'] = $p->id;
			$details['order_id'] = $p->order_id;
			$details['created'] = $p->created;
			$ret[] = $details;
		}
		
		return $ret;						
	}

	public function getPaymentName() {
		return 'Klix';
	}

	public function getSubmitUrl() {
		
	}
	
	private function _getCallbackUrl($orderId) {
		if (Configure::read($this->configBaseName . '.callbackUrl') ?? null)
			return Configure::read($this->configBaseName . '.callbackUrl') . '?order_id=' . $orderId;
		else if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/evc2027' . '/shop/shops/payment_complete?order_id=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_complete', '?' => ['order_id' => $orderId]), true);				
	}
	
	private function _getSuccessUrl($orderId) {
		if (Configure::read($this->configBaseName . '.successUrl') ?? null)
			return Configure::read($this->configBaseName . '.successUrl') . '?order_id=' . $orderId;
		else if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/evc2027' . '/shop/shops/payment_success?order_id=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success', '?' => ['order_id' => $orderId]), true);		
}
	
	private function _getFailureUrl($orderId) {
		if (Configure::read($this->configBaseName . '.failureUrl') ?? null)
			return Configure::read($this->configBaseName . '.failureUrl') . '?order_id=' . $orderId;
		else if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/evc2027' . '/shop/shops/payment_error?order_id=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error', '?' => ['order_id' => $orderId]), true);				
	}
	
	private function _getCancelUrl($orderId) {
		if (Configure::read($this->configBaseName . '.cancelUrl') ?? null)
			return Configure::read($this->configBaseName . '.cancelUrl') . '?order_id=' . $orderId;
		else
			return $this->_getFailureUrl($orderId);
	}
}