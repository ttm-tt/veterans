<?php

/*
 * TODO
 * - completed: Storno mit Order abgleichen
 * - completed: Wenn Gesamtstorno auch Order stornieren
 * 
 * - success: Ergebnis der Aufrufe auswerten, Fehlermeldung
 * 
 * - error: Auf Fehlerseite weiterleiten
 * - error: Fehler auswerten
 * 
 * - storno nach 60 Tagen: nur Rueckerstattung
 */
namespace Shop\Payment;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Shop\Payment\AbstractPayment;


// Implement Adapter to Paypal Express Checkout
class PaypalExpressPayment extends AbstractPayment {
	
	public function __construct($controller) {
		parent::__construct($controller);
	}
	
	// Deliver page for input of credit card data
	public function prepare($amount) {
		$this->_controller->set('amount', $amount);
		$this->_controller->set('paypalUrl', $this->_getFormURL());
		
		$this->_controller->render('Shops/Payment/paypal_express');		
	}

	// Optional callback when customer confirms payment
	public function confirm($orderId) {
		$this->_setExpressCheckout($orderId);
	}

	// Called by wizard after closing the page
	public function process() {
		
	}

	// Payment was successful
	public function success($request) {
		file_put_contents(TMP . '/paypal/xxxsuccess-' . date('Ymd-His'), print_r($request, true));
		
		$token = $request->query['token'];
		$payerId = $request->query['PayerID'];
		$orderId = $request->query['order'];
		
		$details = $this->_executeCall('GetExpressCheckoutDetails', array(
			'TOKEN' => $token
		));
		
		// $orderId = $details['PAYMENT_REQUEST_0_CUSTOM'];
		
		$result = $this->_executeCall('DoExpressCheckoutPayment', array(
			'TOKEN' => $token,
			'PAYERID' => $payerId,
			'PAYMENTREQUEST_0_AMT' => $details['PAYMENTREQUEST_0_AMT'],
			'PAYMENTREQUEST_0_CURRENCYCODE' => $details['PAYMENTREQUEST_0_CURRENCYCODE'],
			'PAYMENTREQUEST_0_NOTIFYURL' => $this->_getCallbackURL($orderId),
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale'
		));
		
		file_put_contents(TMP . '/paypal/xxxdo-' .  date('Ymd-His'), print_r(array($details, $result), true));
	
		$this->_controller->_success($orderId);
	}

	// Payment was cancelled, redirect to select payment
	public function error($request) {
		$orderId = $request->query['order'];
		
		$this->_controller->_onError($orderId, 'ERR');
		$this->_controller->_failure($orderId, __('Payment cancelled'));
	}

	// Payment completed
	public function completed($request) {
		file_put_contents(TMP . '/paypal/xxxipn-' . date('Ymd-His') . '-' . $request->data['payment_status'], 
				print_r([
					'POST/GET' => $request->is(['post', 'put', 'get']), 
					'Request' => $request], true)
		);

		if (!isset($request->query['order'])) {
			// Invalid request
			// TODO: Write error
			return;
		}
		
		$curl = curl_init();
		$curlOptions = array(
			CURLOPT_URL => $this->_getFormURL(),
			CURLOPT_VERBOSE => 1,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => array('cmd' => '_notify-validate') + $request->data
		);
		
		$curl = curl_init();
		curl_setopt_array($curl, $curlOptions);
		
		$response = curl_exec($curl);
		
		$errno = curl_errno($curl);
		
		curl_close($curl);
		
		$orderId = $request->query['order'];
		
		// $orderId = $request->data['PAYMENT_REQUEST_0_CUSTOM'];
		
		$this->_controller->loadModel('Shop.Orders');
		$order = $this->_controller->Order->find('first', array(
			'recursive' => -1,
			'contain' => array('OrderStatus'),
			'conditions' => array('Orders.id' => $orderId)
		));
		
		if (empty($order)) {
			// Order not found
			return;
		}

		// Save or update data		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_paypal');
		
		$params = $request->data;
		$params['order_id'] = $orderId;
		
		// Convert paypal timestamp to MySQL readable
		$params['payment_date'] = date('Y-m-d H:i:s', strtotime($params['payment_date']));
		
		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity($params)
		);
		
		if ($order['order_status']['name'] === 'INIT') {
			// On error change status only if not yet paid		
			if ($errno) {
				$this->_controller->onError($orderId, 'ERR');
				return;
			}
		
			if ($response !== 'VERIFIED') {
				$this->_controller->onError($orderId, 'FRD');
				return;
			}
		}
				
		// Payment complete
		if ($params['payment_status'] === 'Completed') {
			if ($order['OrderStatus']['name'] === 'INIT') {
				// Success
				$this->_controller->_onSuccess($orderId);				
			}
		} else if ($params['payment_status'] === 'Refunded') {
			if ($order['OrderStatus']['name'] === 'PAID') {
				// Cancel order an all items therein, but only if all was refunded
			}
		}
	}

	// Cancel a payment
	public function storno($orderId, $amount) {
		if (!Configure::read('Shop.PaymentProviders.PaypalExpress.allowRefund')) {
			$this->_controller->MultipleFlash->setFlash(__('Automatic refunds disabled, do the refund manually'), 'info');
			return true;
		}

		// Within 60 days: RefundTransaction
		// After 60 days: MassPay
		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_paypal');
		
		$payment = $this->_controller->OrderPayments->find('first', array(
			'recursive' => -1,
			'conditions' => array('OrderPayments.order_id' => $orderId),
			'order' => ['created' => 'DESC']
		));
		
		if (empty($payment))
			return true;
		
		// Check date created
		if ($payment['created'] < date('Y-m-d', time('-60 days'))) {
			// Not within 60 days
			$this->_controller->MultipleFlash(__('Automatic refund not possible after 60 days'), 'warning');
			return true;
		}
		
		$this->_controller->loadModel('Shop.Orders');
		$order = $this->_controller->Orders->find('first', array(
			'recursive' => -1,
			'contain' => array('OrderStatus'),
			'conditions' => array('Order.id' => $orderId)
		));
		
		if (empty($order)) {
			$this->_controller->MultipleFlash(__('Invalid order id given'), 'error');
			return false;
		}
		
		$params = array(
			'TRANSACTIONID' => $payment['txn_id'],
			'INVOICEID' => $payment['invoice']			
		);
		
		if ($order['order_status']['name'] === 'CANC') {
			// Full refund, but what about cancellation fee?
			// If fee eq. discount (either they are both 0.00 or sthg. else), we have a full refund
			if($order['cancellation_fee'] == $order['cancellation_discount']) {
				$params['REFUNDTYPE'] = 'Full';
			} else {
				$params['REFUNDTYPE'] = 'Partial';
				$params['AMT'] = $amount;
				$params['CURRENCYCODE'] = $payment['currency_code'];				
			}
		} else if ($order['order_status']['name'] !== 'PAID') {
			$this->_controller->MultipleFlash(__('Invalid order state {0}', $order['order_status']['name']), 'error');
			return false;			
		} else {
			$params['REFUNDTYPE'] = 'Partial';
			$params['AMT'] = $amount;
			$params['CURRENCYCODE'] = $payment['currency_code'];
		}
		
		$response = $this->_executeCall('RefundTransaction', $params);
		
		file_put_contents(TMP . '/paypal/xxxstorno-' . date('Ymd-His'), print_r($response, true));
		
		return $response['ACK'] === 'Success';
	}

	// Get the payment details
	public function getOrderPayment($orderId) {
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_paypal');
		
		return $this->_controller->OrderPayments->find('all', array(
			'conditions' => array('order_id' => $orderId),
			'order' => ['created' => 'DESC']
		));
		
	}
	
	
	// Internal functions
	// Initiate express checkout and retrieve token
	private function _setExpressCheckout($orderId) {
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Tournaments');
		
		$tournament = $this->_controller->Tournaments->find('first', array(
			'recursive' => -1,
			'conditions' => array('Tournament.id'=> $this->reqeust->session()->read('Tournaments.id'))
		));
		
		
		$order = $this->_controller->Orders->find('first', array(
			'conditions' => array('Order.id' => $orderId)
		));
		
		if (empty($order)) {
			$order = array(
				'id' => $orderId,
				'total' => 10.,
				'invoice' => '2016/1'
			);
		}
		
		$options = array(
			'RETURNURL' => $this->_getReturnURL($orderId),
			'CANCELURL' => $this->_getCancelURL($orderId),
			// 'CALLBACK' => $this->_getCallbackURL($orderId),
			'LANDINGPAGE' => 'Login',
			'NOSHIPPING' => 1,
			'REQCONFIRMSHIPPING' => 0,
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'PAYMENTREQUEST_0_AMT' => $order['outstanding'],
			'PAYMENTREQQUEST_0_ITEMAMT' => $order['outstanding'],
			'PAYMENTREQUEST_0_CURRENCYCODE' => $this->_controller->_shopSettings['currency'],
			'PAYMENTREQUEST_0_DESC' => __('{0}: Invoice no {1}', $tournament['name'], $order['invoice']),
			'PAYMENTREQUEST_0_INVNUM' => $order['invoice']			
		);
		
		$items = $this->_controller->Cart->getItems();
		$idx = 0;
		foreach ($items as $item) {
			if ($item['OrderArticle']['quantity'] == 0)
				continue;
			
			// $options['L_PAYMENTREQUEST_0_ITEMCATEGORY' . $idx] = 'Digital';
			$options['L_PAYMENTREQUEST_0_NAME' . $idx] = $item['Article']['description'];
			$options['L_PAYMENTREQUEST_0_DESC' . $idx] = $item['OrderArticle']['description'];
			$options['L_PAYMENTREQUEST_0_QTY' . $idx] = $item['OrderArticle']['quantity'];
			$options['L_PAYMENTREQUEST_0_AMT' . $idx] = $item['OrderArticle']['price'];  // Per Item
			
			$idx++;
		}
		
		$response = $this->_executeCall('SetExpressCheckout', $options);		
		
		file_put_contents(TMP . '/paypal/xxxset-' . date('Ymd-His'), print_r($response, true));
		
		$response['orderId'] = $orderId;
		
		if (is_array($response) && $response['ACK'] === 'Success') {
			$this->_controller->set('json_object', $response);		
			$this->_controller->render('json');
		}
	}
	
	// Build URLs
	// Paypal form url
	private function _getFormURL() {
		if (Configure::read('App.test'))
			return 'https://www.sandbox.paypal.com/cgi-bin/webscr?useraction=commit';
		else
			return 'https://www.sandbox.paypal.com/cgi-bin/webscr?useraction=commit';
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
	
	// Do a call
	private function _executeCall($method, $params) {
		$requestParams = array(
			'METHOD' => $method,
			'VERSION' => Configure::read('Shop.PaymentProviders.PaypalExpress.api_version'),
			'USER' => Configure::read('Shop.PaymentProviders.Shop.PaymentProviders.PaypalExpress.accountData.username'),
			'PWD' => Configure::read('Shop.PaymentProviders.PaypalExpress.accountData.password'),
			'SIGNATURE'=> Configure::read('Shop.PaymentProviders.PaypalExpress.accountData.signature')
		);
		
		$request = http_build_query($requestParams + $params);
		
		$curlOptions = array(
			CURLOPT_URL => Configure::read('Shop.PaymentProviders.PaypalExpress.endpoint'),
			CURLOPT_VERBOSE => 1,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $request
		);
		
		$curl = curl_init();
		curl_setopt_array($curl, $curlOptions);
		
		$response = curl_exec($curl);
		
		if (curl_errno($curl)) {
			// Handle error
			return array();
		} else {
			curl_close($curl);
			$responseArray = array();
			parse_str($response, $responseArray);
			return $responseArray;
		}
	}

	public function getPaymentName() {
		return 'Paypal';
	}

	public function getSubmitUrl() {
		return Configure::read('Shop.PaymentProviders.PaypalExpress.endpoint');		
	}

}

?>