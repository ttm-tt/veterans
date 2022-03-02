<?php
namespace Shop\Payment;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Shop\Payment\AbstractPayment;

class SmartPayPayment extends AbstractPayment {
	public function __construct($controller) {
		parent::__construct($controller);
	}
	
	/**
	 *  Payment completed Hidden callback from PSP
	 */
	public function completed($request) {
		file_put_contents(TMP . '/smartpay/xxxcompleted-' . date('Ymd-His'), 
				print_r([
					'POST' => $request->is(['post', 'put']), 
					'Request' => $request], true)
		);
				
		if ($request->is(['post', 'put']))
			$data = $request->getData();
		else if ($request->is(['get']))
			$data = $request->getQuery();
		else
			return;
		
		// Nothing we can do without the order id
		if (empty($data['oid']))
			return;
				
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');

		// Get order id. In test mode there is a random postfix to make the order id unique
		$orderId = explode('-', $data['oid'])[0] ?? 0;

		try {
			$order = $this->_controller->Orders->get($orderId);
		} catch (InvalidPrimaryKeyException | RecordNotFoundException $_ex) {
			$this->_UNUSED($_ex);
			file_put_contents(TMP . '/smartpay/xxxerror-' . date('Ymd-His'), print_r($data, true));
			return;			
		}
		
		// Read (last) payment details for order to get txndate
		$details = $this->_controller->OrderPayments->find()
				->where(['order_id' => $orderId])
				->order(['created' => 'DESC'])
				->first()
		;
		
		if ($details === null)
			return;
		
		$txnDateTime = $details->created->format('Y:m:d-H:i:s');
		
		$details->value = json_encode($data);
		$this->_controller->OrderPayments->save($details);
		
		$amount = number_format($order->outstanding, 2, '.', '');
		$currency = 'EUR';
		$status = 'PAID';
		$sha = $this->_verifyHashCode($txnDateTime, $data);
		
		if ($sha !== $data['response_hash'] || 
				$currency !== $data['currency'] ||
				$amount !== $data['chargetotal']) {
			file_put_contents(TMP . '/positivity/xxxfraud-' . date('Ymd-His'), print_r(
				[
					'sha' => $sha, 
					'txnDateTime' => $txnDateTime,
					'amount' => $amount,
					'data' => $data
				], true)
			);
			$status = 'FRD';
		} else if (($data['status'] ?? '') !== 'APPROVED') {
			file_put_contents(TMP . '/smartpay/xxxerror-' . date('Ymd-His'), print_r($data, true));
			$status = 'ERR';
		} else {
			$status = 'PAID';
		}
		
		if ($status === 'PAID')
			$this->_controller->_onSuccess($orderId, $status);
		else 
			$this->_controller->_onError($orderId, $status);		
	}

	/**
	 *  Callback when customer confirms payment
	 */
	public function confirm($orderId) {
		$this->_controller->loadModel('Tournaments');
		
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Shop.OrderArticles');
		
		$order = $this->_controller->Orders->get($orderId, [
			'contain' => ['InvoiceAddresses']
		]);
		
		$ct = time();
		
		// Transaction time (see below) is used later to verify the hash, 
		// but it is not included in the parameters returned from the server.
		// To access it any time later create a new record in payment_details
		// (we need it anyway later) with the 'created' timestamp set to $ct
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');
		
		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity([
					'order_id' => $order->id,
					'payment' => 'smartpay',
					'value' => '',
					'created' => date('Y-m-d H:i:s', $ct)
				])
		);
		
		$amount = number_format($order->outstanding, 2, '.', '');
		
		$configBaseName = 'Shop.PaymentProviders.SmartPay';
		$access_code = Configure::read($configBaseName . '.accountData.access_code');
		$encryption_key = Configure::read($configBaseName . '.accountData.encryption_key');
				
		$parameters = [
			'tid' => $ct,
			'merchant_id' => Configure::read($configBaseName . '.accountData.merchant_id'),
			'order_id' => $orderId,
			'amount' => number_format($amount, 3),
			'currency' => $this->_controller->_shopSettings['currency'],
			'cancel_url' => $this->_getUrlCompleted($orderId),
			'redirect_url' => $this->_getUrlCompleted($orderId,),
			'billing_name' => $order->invoice_address->title . ' ' . 
							  $order->invoice_address->first_name . ' ' . 
							  $order->invoice_address->last_name,
			'billing_address' => $order->invoice_address->street,
			'billing_city' => $order->invoice_address->city,
			'billing_country' => $order->invoice_address->country->name ?? '',
			'billing_email' => $order->email
		];
		
		$data = $this->_encrypt($parameters, $encryption_key);
		
		$this->_controller->set('json_object', [
			'encRequest' => $data,
			'access_code' => $access_code
		]);
		
		$this->_controller->render('json');		
	}

	/**
	 *  Payment was not successful, redirect initiated from PSP in case of error
	 */
	public function error($request) {
		file_put_contents(TMP . '/smartpay/xxxerror-' . date('Ymd-His'), print_r($request, true));		
				
		if ($request->isPost())
			$data = $request->getData();
		else if ($request->isGet())
			$data = $request->getQuery();
		else
			return;
		
		// Nothing we can do without order id
		if (empty($data['oid']))
			return;
		
		$orderId = explode('-', $data['oid'])[0];
		
		$errMsg = 'The transaction has failed';
		
		// Set status to ERR, if not yet done
		// If the users cancels the order UrlKO was not called.
		$this->_controller->_onError($orderId, 'ERR');
		$this->_controller->_failure($orderId, $errMsg);		
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
		return 'SmartPay';
	}

	public function getSubmitUrl() {
		return Configure::read('Shop.PaymentProviders.SmartPay.endpoint');		
	}

	public function prepare($amount) {
		$submitUrl = $this->getSubmitUrl();
		
		$this->_controller->set('amount', $amount);
		$this->_controller->set('submitUrl', $submitUrl);
		
		$this->_controller->render('Shops/Payment/smartpay');				
	}

	/**
	 *  Called by wizard after closing the page
	 */
	public function process() {
		
	}

	/**
	 *  Cancel a payment
	 */
	public function storno($orderId, $amount) {
		return false;
	}

	/**
	 *  Payment was successful, redirect initiated from PSP in case of success
	 */
	public function success($request) {
		file_put_contents(TMP . '/smartpay/xxxsuccess-' . date('Ymd-His'), print_r($request, true));		
		
		if ($request->isPost())
			$data = $request->getData();
		else if ($request->isGet())
			$data = $request->getQuery();
		else
			return;
				
		// Nothing we can do without order id
		if (empty($data['oid']))
			return;
		
		$orderId = explode('-', $data['oid'])[0];
		
		$this->_controller->_success($orderId);		
	}

	private function _encrypt(array $params, $encryption_key) : string {
		// $data = http_build_query($params);
		$data = '';
		foreach ($params as $key => $val)
			$data .= $key . '=' . urlencode($val) . '&';
				
		$iv = openssl_random_pseudo_bytes(16);
		$tag = '';
		
		$openMode = openssl_encrypt($data, 'AES-256-GCM', $encryption_key, OPENSSL_RAW_DATA, $iv, $tag);
		$b2h = bin2hex($iv) . bin2hex($openMode . $tag);
		
		return $b2h;
	}

	private function _decrypt(string $encryptedText, $encryption_key) : array {
		$iv_len = $tag_len = 16;
		
		$encryptedText = hex2bin($encryptedText);
		$iv = substr($encryptedText, 0, $iv_len);
		$tag = substr($encryptedText, -$tag_len, $iv_len);
		$cyphertext = substr($encryptedText, $iv_len, $tag_len);
		$data = openssl_decrypt($cyphertext, 'AES-256-GCM', $encryption_key, OPENSSL_RAW_DATA, $iv, $tag);
		
		parse_str($data, $res);
		
		return $res;
	}
	
	
	private function _getUrlSuccess($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v4/' . $this->_controller->getRequest()->getParam('ds') . '/shop/shops/payment_success';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success'), true);
	}
	
	
	private function _getUrlError($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v4/' . $this->_controller->getRequest()->getParam('ds') . '/shop/shops/payment_error';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error'), true);		
	}

	private function _getUrlCompleted($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v4/' . $this->_controller->getRequest()->getParam('ds') . '/shop/shops/payment_complete';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_complete'), true);
	}
}
