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
		$ct = time();
		file_put_contents(TMP . '/smartpay/xxxcompleted-' . date('Ymd-His', $ct), 
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
		
		// Nothing we can do without the valid response
		if (empty($data['encResp']))
			return;
				
		$configBaseName = 'Shop.PaymentProviders.SmartPay';
		$encryption_key = Configure::read($configBaseName . '.accountData.encryption_key');
				
		// decrypt data
		$decryptedText = '';
		$response = $this->_decrypt($data['encResp'], $encryption_key, $decryptedText);
		file_put_contents(TMP . '/smartpay/xxxdecrypt-response' . date('Ymd-His', $ct), 
				print_r([$decryptedText, $response], true));
		if (empty($response['order_id']))
			return;
		
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');

		// Get order id. In test mode there is a random postfix to make the order id unique
		$orderId = substr($response['order_id'], 0, 5);;
		
		// We need a unique order id

		$order = $this->_controller->Orders->get($orderId);
		if ($order === null) {
			file_put_contents(TMP . '/smartpay/xxxerror-' . date('Ymd-His', $ct), print_r($response, true));
			return;
		}
		
		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity([
						'order_id' => $orderId,
						'payment' => 'smartpay',
						'value' => json_encode($response)
					])
		);

		// $amount = number_format($order->outstanding, 2, '.', '');
		// $currency = $response['currency'];
		$status = 'PAID';
		
		if (($response['order_status'] ?? '') !== 'Success') {
			file_put_contents(TMP . '/smartpay/xxxerror-' . date('Ymd-His'), print_r($response, true));
			$status = 'ERR';
		} else {
			$status = 'PAID';
		}
		
		if ($status === 'PAID') {
			$this->_controller->_onSuccess($orderId, $status);
		} else {
			$this->_controller->_onError($orderId, $status);
		}
		
		return $this->_controller->redirect($status === 'PAID' ? 
				$this->_getUrlSuccess($orderId) : $this->_getUrlError($orderId));
	}

	/**
	 *  Callback when customer confirms payment
	 */
	public function confirm($orderId) {
		$this->_controller->loadModel('Tournaments');
		
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Shop.OrderArticles');
		
		$order = $this->_controller->Orders->get($orderId, [
			'contain' => ['InvoiceAddresses' => 'Countries']
		]);
		
		$ct = time();
		
		$amount = number_format($order->outstanding, 2, '.', '');
		
		$configBaseName = 'Shop.PaymentProviders.SmartPay';
		$access_code = Configure::read($configBaseName . '.accountData.access_code');
		$encryption_key = Configure::read($configBaseName . '.accountData.encryption_key');
				
		$isTest = Configure::read($configBaseName . '.test') == true;

		// Convert currency to accepted currency by provider, if necessary
		
		$shopCurrency = $this->_controller->_shopSettings['currency'];
		$bankCurrency = Configure::read('Shop.CurrencyConverter.currency', $shopCurrency);
		
		if ($shopCurrency !== $bankCurrency) {
			$driver = \Otherguy\Currency\DriverFactory::make(Configure::read('Shop.CurrencyConverter.engine'));
			$driver->accessKey(Configure::read('Shop.CurrencyConverter.key'));
			$driver->config('format', '1');

			$result = $driver->from($shopCurrency)->to($bankCurrency)->get();
		
			$amount = $result->convert($amount, $shopCurrency, $bankCurrency);
			
			$order->payment_total = $amount;
			$order->payment_currency = $bankCurrency;
			
			$this->_controller->Orders->save($order);
		}
		
		$parameters = [
			'merchant_id' => Configure::read($configBaseName . '.accountData.merchant_id'),
			'order_id' => sprintf('%05d', $orderId) . ($isTest ? substr('' . $ct, -8) : ''),
			'amount' => number_format($amount, 3),
			'currency' => $bankCurrency, 
			'si_type' => 'ONDEMAND',
			'cancel_url' => $this->_getUrlCompleted($orderId),
			'redirect_url' => $this->_getUrlCompleted($orderId),
			'billing_name' => // $order->invoice_address->title . ' ' . 
							  $order->invoice_address->first_name . ' ' . 
							  $order->invoice_address->last_name,
			'billing_address' => $order->invoice_address->street,
			'billing_city' => $order->invoice_address->city,
			'billing_country' => $order->invoice_address->country->name ?? '',
			'billing_email' => $order->email
		];
		
		$data = $this->_encrypt($parameters, $encryption_key);
		
		$this->_controller->set('json_object', [
			// 'parameters' => $parameters,
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
		// I use 'oid' as the URL parameter, it is shorter
		if (empty($data['oid']))
			return;
		
		$orderId = substr($data['oid'], 0, 5);
		
		$errMsg = 'The transaction has failed';
		
		// Set status to ERR, if not yet done
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
		// I use 'oid' as the URL parameter, it is shorter
		if (empty($data['oid']))
			return;
		
		$orderId = substr($data['oid'], 0, 5);
		
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

	private function _decrypt(string $encryptedText, $encryption_key, &$decryptedText) : array {
		$iv_len = $tag_len = 16;
		
		$encryptedText = hex2bin($encryptedText);
		$iv = substr($encryptedText, 0, $iv_len);
		$tag = substr($encryptedText, -$tag_len, $iv_len);
		$cyphertext = substr($encryptedText, $iv_len, -$tag_len);
		$decryptedText = openssl_decrypt($cyphertext, 'AES-256-GCM', $encryption_key, OPENSSL_RAW_DATA, $iv, $tag);
		
		$res = [];
		parse_str($decryptedText, $res);
		
		return $res;
	}
	
	
	private function _getUrlSuccess($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v4/' . $this->_controller->getRequest()->getParam('ds') . '/shop/shops/payment_success?oid=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success', '?' => ['oid' => $orderId]), true);
	}
	
	
	private function _getUrlError($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v4/' . $this->_controller->getRequest()->getParam('ds') . '/shop/shops/payment_error?oid=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error', '?' => ['oid' => $orderId]), true);		
	}

	private function _getUrlCompleted($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v4/' . $this->_controller->getRequest()->getParam('ds') . '/shop/shops/payment_complete';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_complete'), true);
	}
}
