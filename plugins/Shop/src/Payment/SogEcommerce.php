<?php
namespace Shop\Payment;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Shop\Payment\AbstractPayment;


class SogEcommerce extends AbstractPayment {

	protected $_controller = null;
	
	private $acceptedStatus = [
		'AUTHORISED',
		'AUTHORISED_TO_VALIDATE',
		'CAPTURED',
		'CAPTURE_FAILED', // capture will be redone
		'ACCEPTED'
	];
	
	private $pendingStatus = [
		'INITIAL',
		'WAITING_AUTHORISATION',
		'WAITING_AUTHORISATION_TO_VALIDATE',
		'UNDER_VERIFICATION',
		'WAITING_FOR_PAYMENT'
	];
	
	public function __construct($controller) {
		$this->_controller = $controller;
	}
	
	/**
	 *  Deliver page for input of credit card data
	 */
	public function prepare($amount) {
		$submitUrl = Configure::read('Shop.PaymentProviders.SogEcommerce.endpoint');
		
		$this->_controller->set('amount', $amount);
		$this->_controller->set('submitUrl', $submitUrl);
		
		$this->_controller->render('Shops/Payment/sogecommerce');		
	}
	
	/**
	 *  Optional callback when customer confirms payment
	 */
	public function confirm($orderId) {
		$this->_controller->loadModel('Tournaments');
		
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Shop.OrderArticles');
		
		$order = $this->_controller->Orders->get($orderId, [
			'contain' => ['InvoiceAddresses']
		]);
		
		$amount = $order->outstanding;
		
		$configBaseName = 'Shop.PaymentProviders.SogEcommerce';
		
		$site_id = Configure::read($configBaseName . '.accountData.site_id');
		$key = Configure::read($configBaseName . '.accountData.key');
		$ctx_mode = Configure::read($configBaseName . '.ctx_mode');
		$currency = Configure::read($configBaseName . '.currency');
		
		$parameters = array_filter([
			'vads_action_mode' => 'INTERACTIVE',
			'vads_amount' => intval($amount * 100),
			'vads_capture_delay' => 0,
			'vads_ctx_mode' => $ctx_mode,
			'vads_currency' => $currency,
			'vads_cust_address' => $order->invoice_address->street,
			'vads_cust_city' => $order->invoice_address->city,
			'vads_cust_first_name' => $order->invoice_address->first_name,
			'vads_cust_last_name' => $order->invoice_address->last_name,
			'vads_cust_zip' => $order->invoice_address->zip,
			'vads_order_id' => $orderId,
			'vads_order_info' => str_replace('/', '-', $order->invoice),
			'vads_page_action' => 'PAYMENT',
			'vads_payment_config' => 'SINGLE',
			'vads_return_mode' => 'POST',
			'vads_site_id' => $site_id,
			// vads_trans_date must be in UTC
			// date_create with unix timestamp (preceded with '@') is always UTC
			'vads_trans_date' => date_create('@' . strtotime('now'))->format('YmdHis'),
			'vads_trans_id' => '' . ($order->invoice_split % 10) . str_pad($order->id, 5, '0', STR_PAD_LEFT),
			'vads_url_check' => $this->_getUrlSuccessServer($order->id),
			'vads_url_return' => $this->_getUrlError($order->id),
			'vads_url_success' => $this->_getUrlSuccess($order->id),
			'vads_version' => 'V2'
		], function($val) {return $val !== null;});
		
		$signature = $this->_makeSignature($parameters, $key);
		
		$parameters['signature'] = $signature;
		
		$this->_controller->set('json_object', 
			$parameters
		);
		
		$this->_controller->render('json');		
	}
	
	/**
	 *  Called by wizard after closing the page
	 */
	public function process() {
		
	}
	
	/**
	 *  Payment was successful, redirect initiated from PSP in case of success
	 */
	public function success($request) {
		file_put_contents(TMP . '/sogecommerce/xxxsuccess-' . date('Ymd-His'), print_r($request, true));		
		
		if ($request->isPost())
			$data = $request->getData();
		else if ($request->isGet())
			$data = $request->getQuery();
		else
			return;

		// May be an erraneous call
		if (empty($data['vads_order_id']))
			return;
		
		$configBaseName = 'Shop.PaymentProviders.SogEcommerce';		
		$key = Configure::read($configBaseName . '.accountData.key');
		
		if (empty($data['signature']))
			$orderId = false;
		else if ($data['signature'] !== $this->_makeSignature($data, $key))
			$orderId = false;
		else {
			$orderId = $data['vads_order_id'];
		}
		
		if (!empty($orderId)) {		
			$this->_controller->loadModel('Shop.OrderPayments');
			$this->_controller->OrderPayments->setTable('shop_order_payment_details');
			
			$this->_controller->OrderPayments->save(
					$this->_controller->OrderPayments->newEntity([
						'order_id' => $orderId,
						'payment' => 'sogecommerce',
						'value' => json_encode($data)
					])
			);
		}		
		
		$this->_controller->_success($orderId);
		
	}
	
	/**
	 *  Payment was not successful, redirect initiated from PSP in case of error
	 */
	public function error($request) {
		file_put_contents(TMP . '/sogecommerce/xxxerror-' . date('Ymd-His'), print_r($request, true));		

		if ($request->isPost())
			$data = $request->getData();
		else if ($request->isGet())
			$data = $request->getQuery();
		else
			return;

		// May be an erraneous call
		if (empty($data['vads_order_id']))
			return;
		
		$orderId = $data['vads_order_id'];
		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');

		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity([
					'order_id' => $orderId,
					'payment' => 'sogecommerce',
					'value' => json_encode($data)
				])
		);

		$errMsg = 'The transaction has failed';
		
		$errMsg .= ' (' . $data['vads_trans_status'] . ')';

		// Set status to ERR, if not yet done
		// If the users cancels the order UrlKO was not called.
		$this->_controller->_onError($orderId, 'ERR');
		$this->_controller->_failure($orderId, $errMsg);		
	}
	
	/**
	 *  Payment completed Hidden callback from PSP
	 */
	public function completed($request) {
		file_put_contents(TMP . '/sogecommerce/xxxcompleted-' . date('Ymd-His'), 
				print_r([
					'POST/GET' => $request->is(['post', 'put', 'get']), 
					'Request' => $request], true)
		);				
		
		if ($request->is(['post', 'put']))
			$data = $request->getData();
		else if ($request->is(['get']))
			$data = $request->getQuery();
		else
			return;
				
		$this->_controller->loadModel('Shop.Orders');

		$orderId = $data['vads_order_id'];
		
		$order = $this->_controller->Orders->get($orderId);
				
		// Save or update data		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');
		
		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity([
					'order_id' => $orderId,
					'payment' => 'sogecommerce',
					'value' => json_encode($data)
				])
		);

		$configBaseName = 'Shop.PaymentProviders.SogEcommerce';
		
		$key = Configure::read($configBaseName . '.accountData.key');
		$signature = $this->_makeSignature($data, $key);

		if ($data['signature'] !== $signature) {
			$status = 'FRD';
			file_put_contents(TMP . '/sogecommerce/xxxfraud-' . date('Ymd-His'), 'wrong signature: got ' . $data['signature'] . ', expected ' . $signature);
		} else if ($data['vads_amount'] != intval($order->outstanding * 100)) {
			$status = 'FRD';
			file_put_contents(TMP . '/sogecommerce/xxxfraud-' . date('Ymd-His'), 'amount mismatch: got ' . $data['vads_amount'] . ', expected ' . intval($order->outstanding * 100));
		} else if (in_array($data['vads_trans_status'], $this->acceptedStatus)) {
			$status = 'PAID';
		} else {   
			// TODO: What about pending status?
			$status = 'ERR';
		}
		
		if ($status === 'PAID')
			$this->_controller->_onSuccess($orderId, $status);
		else 
			$this->_controller->_onError($orderId, $status);		
	}
	
	/**
	 *  Cancel a payment
	 */
	public function storno($orderId, $amount) {
		
	}
	
	/**
	 *  Get the payment details
	 */
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
	
	/**
	 *  Get the payment logo
	 */
	public function getPaymentLogo() {
		return null;
	}
	
	// -------------------------------------------------------------------
	private function _makeSignature($parameters, $secret) {
		$message = '';
		ksort($parameters);
		foreach ($parameters as $key => $val) {
			if (strpos($key, 'vads_') === 0)
				$message .= $val . '+';
		}
		
		$message .= $secret;
		$signature = base64_encode(hash_hmac('sha256', $message, $secret, true));
		
		return $signature;
	}
	
	private function _getUrlSuccessServer($order) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v3/shop/shops/payment_complete';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_complete'), true);
	}
	
	
	private function _getUrlSuccess($order) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v3/shop/shops/payment_success';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success'), true);
	}
	
	
	private function _getUrlError($order) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v3/shop/shops/payment_error';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error'), true);		
	}

	public function getPaymentName() {
		return 'Societe General eCommerce';
	}

	public function getSubmitUrl() {
		return Configure::read('Shop.PaymentProviders.SogEcommerce.endpoint');
	}

}
?>