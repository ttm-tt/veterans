<?php /* Copyright (c) 2022 Christoph Theis */ ?>
<?php
namespace Shop\Payment;

/*
 * Payment with Nets Easy https://developers.nets.eu/en-EU/
 */

use Shop\Payment\AbstractPayment;

use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * Description of NetsEasyPayment
 */
class NetsEasyPayment extends AbstractPayment {
	private $configBaseName = 'Shop.PaymentProviders.NetsEasy';
	
	// Deliver page for payment
	public function prepare($amount) {
		$this->_controller->set('amount', $amount);
		$this->_controller->set('submitUrl', '');
		
		$this->_controller->render('Shops/Payment/netseasy');				
	}

	// Called when customer confirms order
	public function confirm($orderId) {
		$response = $this->_executeCall($orderId);
	}

	// Callback when payment is completed
	public function completed($request) {
		
	}

	// Payment was successful
	public function success($request) {
		
	}

	// Payment was not successful
	public function error($request) {
		
	}

	// Get the payment details
	public function getOrderPayment($orderId) {
		
	}

	// Get the paymentname
	public function getPaymentName() : string {
		
	}

	public function getSubmitUrl() : string {
		
	}

	public function process() {
		
	}

	// Cancel a payment
	public function storno($orderId, $amount) {
		
	}

	// Get options for the call
	private function _getOptions(int $orderId) : array {
		$order = $this->_controller->Orders->record($orderId);
		
		return [
			'checkout' => [
				'integrationType' => 'HostedPaymentPage',
				'returnUrl' => Router::url(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success', '?' => ['order' => $orderId]]),
				'errorUrl' => ['plugin' => 'Shop', 'controller' => 'shops', 'action' => 'payment_error', '?' => ['order' => $orderId]],
				'termsUrl' => Router::url(['plugin' => 'shop', 'controller' => 'pages', 'action' => 'shop_agb']),
			],
			'order' => [
				'amount' => $order->outstanding * 100,
				'items' => [[
					'reference' => __('Registration') . ' ' . $order->invoice,
					'name' => $order->invoice,
					'quantity' => 1,
					'unit' => 'pcs',
					'unitPrice' => $order->outstanding * 100,
					'grossTotalAmount' => $order->outstanding * 100,
					'netTotalAmount' => $order->outstanding * 100
				]],
				'amount' => $order->outstanding * 100,
				'currency' => $this->_controller->_shopSettings['currency'],
				'reference' => $orderId,
				'notifications' => [
					'webHooks' => [
						'eventName' => 'payment.checkout.completed',
						'url' => $this->_getCallbackUrl($orderId)
					]
				]
			]
		];
	}
	
	// Do a call
	private function _executeCall(int $orderId) : array {
		$url = Configure::read($this->configBaseName . '.endpoint');
		$options = $this->_getOptions($orderId);
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($options));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: ' . Configure::read($this->configBaseName . '.accountData.secretKey')
		]);
		$json = curl_exec($curl);
		debug($json);
		$result = json_decode($json, true);
		
		return $result;
	}

	// The PSP will call this URL to notify about the state of a transaction
	private function _getCallbackUrl($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans/shop/shops/payment_complete?order=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_complete', '?' => ['order' => $orderId]), true);
	}
	
	private function _getUrlSuccess($order) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v3/shop/shops/payment_success?order=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success', '?' => ['order' => $orderId]), true);
	}
	
	
	private function _getUrlError($order) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v3/shop/shops/payment_error?order=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error', '?' => ['order' => $orderId]), true);		
	}

}
