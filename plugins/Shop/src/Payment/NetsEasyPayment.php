<?php /* Copyright (c) 2022 Christoph Theis */ ?>
<?php
namespace Shop\Payment;

/*
 * Payment with Nets Easy https://developers.nets.eu/en-EU/
 */

use Shop\Payment\AbstractPayment;

use Cake\Core\Configure;
use Cake\Routing\Router;

Use Shop\Model\Table\OrderStatusTable;

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
		$this->_controller->set('json_object', ['hostedPaymentPageUrl' => $response['hostedPaymentPageUrl']]);
		$this->_controller->render('json');		
		
	}

	// Callback when payment is completed
	public function completed($request) {
		file_put_contents(TMP . '/netseasy/xxxcompleted-' . date('Ymd-His'), print_r(['request' => $request], true));		
		// Callback seems not to be called ...
	}

	// Payment was successful
	public function success($request) {
		file_put_contents(TMP . '/netseasy/xxxsuccess-' . date('Ymd-His'), print_r(['request' => $request], true));		
		
		if ($request->isPost())
			$data = $request->getData();
		else if ($request->isGet())
			$data = $request->getQuery();
		else
			return;
				
		// Nothing we can do without order id
		if (empty($data['order']))
			return;
		
		$orderId = $data['order'];
		
		// Save or update data		
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Shop.OrderStatus');
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');

		$order = $this->_controller->Orders->record($orderId);
		
		$ct = time();
		
		$status = 'PAID';

		if ($order->order_status_id == OrderStatusTable::getInitiateId()) {
			$url = Configure::read($this->configBaseName . '.endpoint') . '/' . $data['paymentid'];
			
			$curl = curl_init($url);
			
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST , 'GET');
			curl_setopt($curl, CURLOPT_HTTPHEADER, [
				'Authorization: ' . Configure::read($this->configBaseName . '.accountData.secretKey')
			]);
			$json = curl_exec($curl);
			// debug($json);
			$result = json_decode($json, true);
			
			file_put_contents(TMP . '/netseasy/xxxpayments-' . date('Ymd-His'), print_r([
				'url' => $url,
				'error' => curl_error($curl),
				'result' => $result
			], true));		

			curl_close($curl);

			$this->_controller->OrderPayments->save(
					$this->_controller->OrderPayments->newEntity([
						'order_id' => $order->id,
						'payment' => $this->getPaymentName(),
						'value' => $json,
						'created' => date('Y-m-d H:i:s', $ct)							
					])
			);
			
			if (($result['payment']['summary']['chargedAmount'] ?? 0) != $order->outstanding * 100)
				$status = 'FRD';
		}
		
		
		if ($status === 'PAID') {
			$this->_controller->_onSuccess($orderId, $status);
			$this->_controller->_success($orderId);
		} else  {
			$this->_controller->_onError($orderId, $status);
			$this->_controller->_failure($orderId, __('The transaction has failed'));
		}
	}

	// Payment was not successful
	public function error($request) {
		file_put_contents(TMP . '/netseasy/xxxerror-' . date('Ymd-His'), print_r(['request' => $request], true));		
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

	// Get the paymentname
	public function getPaymentName() : string {
		return 'NetsEasy';
	}

	public function getSubmitUrl() : string {
		return '';
	}

	public function process() {
		
	}

	// Cancel a payment
	public function storno($orderId, $amount) {
		
	}

	// Get options for the call
	private function _getOptions(int $orderId) : array {
		$order = $this->_controller->Orders->record($orderId, [
			'contain' => [
				'InvoiceAddresses' => 'Countries'
			]
		]);
		// debug($order);
		
		return [
			'checkout' => [
				'integrationType' => 'HostedPaymentPage',
				'returnUrl' => Router::url(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success', '?' => ['order' => $orderId]], true),
				'errorUrl' => Router::url(['plugin' => 'Shop', 'controller' => 'shops', 'action' => 'payment_error', '?' => ['order' => $orderId]], true),
				'termsUrl' => Router::url(['plugin' => 'shop', 'controller' => 'pages', 'action' => 'shop_agb'], true),
				'merchantHandlesConsumerData' => true,
				'charge' => true,
				'appearance' => [
					'displayOptions' => [
						'showOrderSummary' => false
					]
				]
			],
			'order' => [
				'amount' => $order->outstanding * 100,
				'items' => [[
					'name' => __('Registration') . ' ' . $order->invoice,
					'reference' => $order->invoice,
					'quantity' => 1,
					'unit' => 'pcs',
					'unitPrice' => $order->outstanding * 100,
					'grossTotalAmount' => $order->outstanding * 100,
					'netTotalAmount' => $order->outstanding * 100
				]],
				'amount' => $order->outstanding * 100,
				'currency' => $this->_controller->_shopSettings['currency'],
				'reference' => $orderId,
			],
			'notifications' => [
				'webHooks' => [[
					'eventName' => 'payment.checkout.completed',
					'url' => $this->_getCallbackUrl($orderId),
					'authorization' => '12345678'
				]]
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
		// debug($json);
		$result = json_decode($json, true);
		
		file_put_contents(TMP . '/netseasy/xxxconfirm-' . date('Ymd-His'), print_r([
			'options' => json_encode($options),
			'json' => $json,
			'result' => $result
		], true));
		
		curl_close($curl);
		
		return $result;
	}

	// The PSP will call this URL to notify about the state of a transaction
	private function _getCallbackUrl($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v4/' . $this->_controller->getRequest()->getParam('ds') . '/shop/shops/payment_complete?order=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_complete', '?' => ['order' => $orderId]), true);
	}
	
	private function _getUrlSuccess($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v4/' . $this->_controller->getRequest()->getParam('ds') . '/shop/shops/payment_success?order=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success', '?' => ['order' => $orderId]), true);
	}
	
	
	private function _getUrlError($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v4/' . $this->_controller->getRequest()->getParam('ds') . '/shop/shops/payment_error?order=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error', '?' => ['order' => $orderId]), true);		
	}

}
