<?php /* Copyright (c) 2022 Christoph Theis */ ?>
<?php

namespace Shop\Payment;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utililty\Hash;

use Shop\Payment\AbstractPayment;
use Shop\Model\Table\OrderStatusTable;


// Implement Adapter to Paypal Checkout
class PaypalStandardPayment extends AbstractPayment {
	public function __construct($controller) {
		parent::__construct($controller);
	}
	
	// Deliver page for input of credit card data
	public function prepare($amount) {
		$this->_controller->set('amount', $amount);
		$this->_controller->set('currency', $this->_controller->_shopSettings['currency']);
		$this->_controller->set('paypalUrl', $this->getScriptUrl());
		
		$this->_controller->render('Shops/Payment/paypal_standard');		
	}

	// Optional callback when customer confirms payment
	public function confirm($orderId) {
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Tournaments');
		
		$order = $this->_controller->Orders->find()
					->where(['Orders.id' => $orderId])
					->first()
		;
		
		$tournament = $this->_controller->Tournaments->find()
					->where(['Tournaments.id'=> $order->tournament_id])
					->first()
		;
		
		$this->_controller->set('json_object', [
			'order_id' => $orderId,
			'url' => $this->getScriptUrl(),
			'currency' => $this->_controller->_shopSettings['currency'],
			'amount' => $order['outstanding'],
			'description' => __('{0}: Invoice no {1}', $tournament['name'], $order['invoice'])
		]);
		
		$this->_controller->render('json');		
	}

	// Called by wizard after closing the page
	public function process() {
		
	}
	
	
	// Payment completed
	public function completed($request ) {
		if ($request == null) {
			$this->_controller->set('json_object', [
				'codee' => 'ERR', 
				'msg' => 'Transaction failed',
				'reeason' => 'request is null'
			]);
			$this->_controller->render('json');				
			return;
		}
		
		if (empty($request->getData('paypal_order_check'))) {
			file_put_contents(TMP . '/paypal_standard/xxxcompleted-' . date('Ymd-His'), print_r(['No data', $request], true));
			$this->_controller->set('json_object', [
				'code' => 'ERR', 
				'msg' => 'Transaction failed',
				'reeason' => 'paypal_order_check is missing'				
			]);
			$this->_controller->render('json');				
			return;
		}
			
		// order_id is a key from paypal to access payment data
		$paypalId = $request->getData('paypal_id') ?? null;
		$orderId = $request->getData('order_id') ?? null;
		
		if (empty($paypalId) || empty($orderId)) {
			file_put_contents(TMP . '/paypal_standard/xxxcompleted-' . date('Ymd-His'), print_r(['No order_id', $request], true));
			$this->_controller->set('json_object', [
				'code' => 'ERR', 
				'msg' => 'Transaction failed',
				'reason' => 'paypal_id or order_id are missing'
			]);
			$this->_controller->render('json');				
			return;
		}
				
		$result = $this->_validate($paypalId);
		
		// Save or update data		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');

		file_put_contents(TMP . '/paypal_standard/xxxcompleted-' . date('Ymd-His'), print_r([
			'paypalId' => $paypalId,
			'orderId' => $orderId,
			'request' => $request,
			'result' => $result
		], true));
		
		$this->_controller->OrderPayments->save($this->_controller->OrderPayments->newEntity([
			'order_id' => $orderId,
			'payment' => 'paypal',
			'value' => json_encode($result)
		]));
		
		if (empty($result['status'] ?? null)) {
			$this->_controller->_onError($orderId, 'ERR');
			
			$this->_controller->set('json_object', [
				'order_id' => $orderId,
				'code' => 'ERR', 
				'msg' => 'Transaction failed',
				'reason' => 'validate returned empty or missing status'
			]);
			$this->_controller->render('json');				
			return;			
		}

		$this->_controller->loadModel('Shop.Orders');
		$order = $this->_controller->Orders->record($orderId);

		if ($result['code'] != 'PAID') {
			// On error change status only if not yet paid		
			if ($order->order_status_id === OrderStatusTable::getInitiateId()) {
				$this->_controller->_onError($orderId, 'ERR');
			}
			
			$this->_controller->set('json_object', [
				'order_id' => $orderId,
				'code' => $result['code'], 
				'msg' => $result['msg'] ?? 'Transaction failed',
				'reason' => 'validate returned status ' . $result['status']
			]);
			$this->_controller->render('json');				
			return;			
		}
		
		// Payment complete
		if ($result['code'] === 'PAID') {
			// Usually order is in state INIT, but when paid by link it can
			// be in any of the other ones. See Shops/pay($ticcket) 
			if ( $order->order_status_id === OrderStatusTable::getInitiateId() ||
				 $order->order_status_id === OrderStatusTable::getPendingId() ||
				 $order->order_status_id === OrderStatusTable::getIncompleteId() ||
				 $order->order_status_id === OrderStatusTable::getDelayedId() ||
				 $order->order_status_id === OrderStatusTable::getInvoiceId() ) {
				// Success
				$this->_controller->_onSuccess($orderId);				
			}
		}		

		$this->_controller->set('json_object', $result + ['order_id' => $orderId]);
		$this->_controller->render('json');				
	}

	// Payment was successful
	public function success($request) {
		file_put_contents(TMP . '/paypal_standard/xxxsuccess-' . date('Ymd-His'), print_r($request, true));

		$orderId = $request->getQuery('order');
		$this->_controller->_success($orderId);
	}

	// Payment was cancelled, redirect to select payment
	public function error($request) {
		$orderId = $request->getQuery('order');
		
		$this->_controller->_onError($orderId, 'ERR');
		$this->_controller->_failure($orderId, __('Payment cancelled'));
	}

	// Cancel a payment
	public function storno($orderId, $amount) {
		if (!Configure::read('Shop.PaymentProviders.Paypal.allowRefund')) {
			$this->_controller->MultipleFlash->setFlash(__('Automatic refunds disabled, do the refund manually'), 'info');
			return true;
		}

		$this->_controller->MultipleFlash->setFlash(__('Automatic refunds not allowed, do the refund manually'), 'info');
		return true;
	}

	// Get the payment details
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
	
	private function _validate($paypalId) {
		$endpointAuth = Configure::read('Shop.PaymentProviders.PaypalStandard.endpointAuth');
		$endpoint = Configure::read('Shop.PaymentProviders.PaypalStandard.endpoint');
		$clientId = Configure::read('Shop.PaymentProviders.PaypalStandard.accountData.PAYPAL_CLIENT_ID');
		$secret = Configure::read('Shop.PaymentProviders.PaypalStandard.accountData.PAYPAL_CLIENT_SECRET');
		
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $endpointAuth);
        curl_setopt($ch, CURLOPT_HEADER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $secret); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials"); 
        $auth_response = json_decode(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch); 

        if ($http_code != 200 && !empty($auth_response->error)) {
			file_put_contents(TMP . '/paypal_standard/xxxvalidate-' . date('Ymd-His'), print_r(['curl_init error', $http_code, $auth_response], true));
			return ['status' => 'ERR', 'msg' => $auth_response->error_description];
            // throw new Exception('Error '.$auth_response->error.': '.$auth_response->error_description); 
        }
         
		if (empty($auth_response)) {
			file_put_contents(TMP . '/paypal_standard/xxxvalidate-' . date('Ymd-His'), print_r(['curl_init empty response', $http_code, $auth_response], true));
			return ['status' => 'ERR', 'msg' => 'Transaction failed'];
		}
		
		if (empty($auth_response->access_token)) {
			file_put_contents(TMP . '/paypal_standard/xxxvalidate-' . date('Ymd-His'), print_r(['curl_init no aaccess token', $http_code, $auth_response], true));
			return ['status' => 'ERR', 'msg' => 'Transaction failed'];
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint . '/orders/' . $paypalId);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '. $auth_response->access_token)); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		$api_data = json_decode(curl_exec($ch), true);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
		curl_close($ch);
		
		if (empty($api_data)) {
			file_put_contents(TMP . '/paypal_standard/xxxvalidate-' . date('Ymd-His'), print_r(['curl error', $http_code, $api_data], true));
			// throw new Exception('Error '.$api_data['error'].': '.$api_data['error_description']); 
			return ['status' => 'ERR', 'msg' => 'Transaction failed'];
		}
		
		if ($http_code != 200 && !empty($api_data['error'])) { 
			file_put_contents(TMP . '/paypal_standard/xxxvalidate-' . date('Ymd-His'), print_r(['curl error', $http_code, $api_data], true));
			// throw new Exception('Error '.$api_data['error'].': '.$api_data['error_description']); 
			return ['status' => 'ERR', 'msg' => $api_data['error'] .': '.$api_data['error_description']];
		}

		file_put_contents(TMP . '/paypal_standard/xxxvalidate-' . date('Ymd-His'), print_r(['validate', $http_code, $api_data], true));
		
		return $api_data + ['code' => 'PAID'];
	}
	
	// Internal functions
	
	// Build URLs
	// Paypal form url
	public function getScriptUrl() {
		$clientId = Configure::read('Shop.PaymentProviders.PaypalStandard.accountData.PAYPAL_CLIENT_ID');
		$currency = 'USD';
		
		if (Configure::read('App.test'))
			$url = 'https://www.paypal.com/sdk/js?currency=' . $currency . '&client-id=' . $clientId;
		else
			$url = 'https://www.paypal.com/sdk/js?currency=' . $currency . '&client-id=' . $clientId;
		
		$url .= '&enable-funding=card';
		$url .= '&disable-funding=paylater';
		// $url .= ',eps,venmo';
		
		return $url;
	}
	
	// URL called if payment is OK
	private function _getReturnURL($orderId) {
		return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success'), true) . '?order=' . $orderId;
	}
	
	// URL called if user cancel payment
	private function _getCancelURL($orderId) {
		return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error'), true) . '?order=' . $orderId;
	}
	
	// URL called by Paypal to notify about changes (hidden callback)
	private function _getCallbackURL($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans/shop/shops/payment_complete?order=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_complete'), true) . '?order=' . $orderId;
	}
	
	public function getPaymentName() {
		return 'Paypal';
	}

	public function getSubmitUrl() {
		return Configure::read('Shop.PaymentProviders.Paypal.endpoint');		
	}

}
