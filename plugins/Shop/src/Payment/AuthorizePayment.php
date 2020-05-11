<?php
namespace Shop\Payment;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Shop\Payment\AbstractPayment;

// An implamentation for authoize.net
class AuthorizePayment extends AbstractPayment {
	public function __construct($controller) {
		parent::__construct($controller);
	}
	
	// Deliver page to collect credit card information
	public function prepare($amount) {
		$this->_controller->loadModel('Shop.Countries');
		$this->_controller->set('countries', $this->_controller->Countries->find('list', array(
			'fields' => array('iso_code_2', 'name'),
			'order' => 'name'
		))->toArray());

		$this->_controller->set('address', $this->_controller->Cart->getAddress());

		$currency = $this->_controller->_shopSettings['currency'];
		$sequence = time();  // Invoice number or something unique
		$time = time();
		$login_id = Configure::read('Shop.PaymentProviders.AuthorizeNet.accountData.loginId');
		$transaction_key = Configure::read('Shop.PaymentProviders.AuthorizeNet.accountData.transactionKey');
		
		$hash = hash_hmac("md5", $login_id . '^' . $sequence . '^' . $time . '^' . $amount . '^' . $currency, $transaction_key);

		$this->_controller->set('amount', $amount);
		$this->_controller->set('currency', $currency);
		$this->_controller->set('fp_sequence', $sequence);
		$this->_controller->set('fp_hash', $hash);
		$this->_controller->set('fp_timestamp', $time);
		$this->_controller->set('relay_response_url', $this->_getRelayUrl());
		$this->_controller->set('login_id', $login_id);		

		$this->_controller->render('Shops/Payment/authorizenet');
	}

	// Optional callback when customer confirms payment
	public function confirm($orderId) {
		$this->_controller->set('json_object', $orderId);
		$this->_controller->render('json');
	}

	// Called by wizard after closing the page
	public function process() {
		
	}

	// Payment was successful (hidden trigger url)
	public function success($request) {
		file_put_contents(TMP . '/authorizenet/xxxsuccess-' . date('Ymd-His'), print_r($request, true));
		
		$data = $request->data;
		$order_id = $data['x_invoice_num'];
		$data['order_id'] = $order_id;

		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_authorizenet');
		
		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity($data)
		);

		$url = $this->_getRedirectUrl();
		
		$url .= '?x_invoice_num=' . $data['x_invoice_num'];
		$url .= '&x_response_code=' . $data['x_response_code'];
		
		$html = 
			"<html><head><script language=\"javascript\">
                <!--
                window.location=\"" . $url . "\";
                //-->
                </script>
                </head><body><noscript><meta http-equiv=\"refresh\" content=\"1;url=" . $url . "\"></noscript></body></html>";
		
		$this->_controller->response->body($html);
		$this->_controller->response->send();
	}

	// Payment completed
	public function completed($request) {
		file_put_contents(TMP . '/authorizenet/xxxcompleted-' . date('Ymd-His'), print_r($request, true));

		$data = $request->query;
		$orderId = $data['x_invoice_num'];

		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_authorizenet');
		$details = $this->_controller->OrderPayments->find('first', array(
			'OrderPayment.order_id' => $orderId,
			'order' => ['created' => 'DESC']
		));
		
		if ($data['x_response_code'] != 1) {	
			$this->_controller->__onError($orderId, 'ERR');
			
			$msg = $details['x_response_reason_text'] . ' (' . $details['x_response_reason_code'] . ')';
			$this->_controller->_failure($orderId, $msg);
		} else {
			// TODO: detect fraud
			// Go to success page
			$this->_controller->_onSuccess($orderId);
			$this->_controller->_success($orderId);
		}
	}

	// Payment was not successful (not called)
	public function error($request) {
		
	}

	public function getOrderPayment($orderId) {
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_authorizenet');
		$details = $this->_controller->OrderPayments->find('all', array(
			'conditions' => array('order_id' => $orderId),
			'order' => 'OrderPayments.created ASC'
		));
		
		return $details;
	}

	public function storno($orderId, $amount) {
		// TODO
		return true;
	}
	
	private function _getRelayUrl() {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans/shop/shops/payment_success';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success'), true);		
	}
	
	
	private function _getRedirectUrl() {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans/shop/shops/payment_complete';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_complete'), true);
	}

	public function getPaymentName() {
		return 'authorize.net';
	}

	public function getSubmitUrl() {
		return Configure::read('Shop.PaymentProviders.AuthorizeNet.endpoint');		
	}

}

?>