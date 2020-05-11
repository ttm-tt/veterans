<?php
namespace Shop\Payment;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Shop\Payment\AbstractPayment;

// An implementation for b-payment.com
class BPayment extends AbstractPayment {
	public function prepare($amount) {
		$bpaymentUrl = Configure::read('Shop.PaymentProviders.BPayment.endpoint');
		
		$this->_controller->set('amount', $amount);
		$this->_controller->set('bpaymentUrl', $bpaymentUrl);
		
		$this->_controller->render('Shops/Payment/bpayment');
		
	}

	
	public function confirm($orderId) {
		$this->_controller->loadModel('Tournaments');
		$tournament = $this->_controller->Tournaments->find('first', array(
			'recursive' => -1,
			'conditions' => array('Tournaments.id'=> $this->_controller->request->session()->read('Tournaments.id'))
		));
		
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Shop.OrderArticles');
		
		$order = $this->_controller->Orders->get($orderId);
		
		// B-Payment needs the shopping cart
		$orderArticles = $this->_controller->OrderArticles->find()
				->where(['order_id' => $order->id])
				->hydrate(false)
				->all()
		;
		
		$amount = $order->outstanding;
		$currency = $this->_controller->_shopSettings['currency']; 
		
		$language = 'EN';
		
		switch ($this->_controller->_getLanguage()) {
			case 'deu' :
				$language = 'DE';
				break;
			
			case 'spa' :
				$language = 'ES';
				break;
		}
		
		$configBaseName = 'Shop.PaymentProviders.BPayment';
		
		$merchantid = Configure::read($configBaseName . '.accountData.merchantid');
		$paymentgatewayid = Configure::read($configBaseName . '.accountData.paymentgatewayid');
		$secretkey = Configure::read($configBaseName . '.accountData.secretkey');
		
		$message = // utf8_encode(
				$merchantid . '|' . 
				$this->_getUrlSuccess($order->id) . '|' .
				$this->_getUrlSuccessServer($order->id) . '|' .
				$order->id . '|' . 
				$amount . '|' . 
				$currency
		;
		
		$checkhash = hash_hmac('sha256', $message, $secretkey);
		
		$parameters = [
			'merchantid' => $merchantid,
			'paymentgatewayid' => $paymentgatewayid,
			'orderid' => $order->id,
			'checkhash' => $checkhash,
			'amount' => $amount,
			'currency' => $currency,
			'language' => $language,
			'returnurlsuccess' => $this->_getUrlSuccess($order->id),
			'returnurlsuccessserver' => $this->_getUrlSuccessServer($order->id),
			'returnurlcancel' => $this->_getUrlCancel($order->id),
			'returnurlerror' => $this->_getUrlError($order->id),
			'skipreceiptpage' => 1
		];
		
		$this->_controller->set('json_object', array(
			'cart' => $orderArticles,
			'parameters' => $parameters,
		));
		
		$this->_controller->render('json');
		
	}

	
	public function process() {
		
	}

	
	public function success($request) {
		file_put_contents(TMP . '/bpayment/xxxsuccess-' . date('Ymd-His'), print_r($request, true));	
		$orderId = $request->getData('orderid');
		$this->_controller->_success($orderId);		
	}

	
	public function error($request) {
		file_put_contents(TMP . '/bpayment/xxxerror-' . date('Ymd-His'), print_r($request, true));	
		$orderId = $request->getData('orderid');
		if ($request->getData('status') === 'CANCEL') {
			$this->_controller->_onError($orderId, 'CANC');
			$this->_controller->_failure($orderId, 'Payment cancelled by user');		
		} else {
			$this->_controller->loadModel('Shop.OrderPayments');
			$this->_controller->OrderPayments->setTable('shop_order_bpayment');
			
			$data = $request->data;
			$data['order_id'] = $orderId;
			$this->_controller->OrderPayments->save(
					$this->_controller->OrderPayments->newEntity($data)
			);

			$errMsg = $request->getData('errordescription') ?: 'Unknown error';
			$errMsg . ' (' . ($request->getData('errorcode') ?: '<unknown>') . ')';
			
			// Set status to ERR, if not yet done
			// If the users cancels the order UrlKO was not called.
			$this->_controller->_onError($orderId, 'ERR');
			$this->_controller->_failure($orderId, $errMsg);

		}
	}

	
	public function completed($request) {
		file_put_contents(TMP . '/bpayment/xxxcompleted-' . date('Ymd-His'), 
				print_r([
					'POST/GET' => $request->is(['post', 'put', 'get']), 
					'Request' => $request], true)
		);
		
		// Whatever we do we have to send back XML Comfirmation
		$this->_controller->response = 
			$this->_controller->response
				->withType('xml')
				->withStringBody('<PaymentNotification>Accepted</PaymentNotification>')
		;


		if ($request->is(['post', 'put']))
			$data = $request->data;
		else if ($request->is(['get']))
			$data = $request->query;
		else
			return;
				
		$orderId = $data['orderid'];
		$data['order_id'] = $orderId;
		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_bpayment');
		
		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity($data)
		);

		$this->_controller->loadModel('Shop.Orders');
		$order = $this->_controller->Orders->get($orderId);
		
		if ($order === null) {
			$this->_controller->_onError($orderId, 'ERR');
			return;
		}

		$configBaseName = 'Shop.PaymentProviders.BPayment';
		$secretkey = Configure::read($configBaseName . '.accountData.secretkey');

		$amount = $order->outstanding;
		$currency = $this->_controller->_shopSettings['currency']; 
		
		$message = $orderId . '|' . $amount . '|' . $currency;
		$checkhash = hash_hmac('sha256', $message, $secretkey);
		
		if ($checkhash !== $data['orderhash']) {
			$this->_controller->_onError($orderId, 'FRD');
			return;
		}

		if ($data['status'] === 'OK')
			$this->_controller->_onSuccess($orderId);
		else
			$this->_controller->_onError($orderId, 'ERR');
	}

	
	public function storno($orderId, $amount) {
		return false;
	}

	
	public function getOrderPayment($orderId) {
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_bpayment');
		
		return $this->_controller->OrderPayments->find('all', array(
			'conditions' => array('order_id' => $orderId),
			'order' => ['created' => 'DESC']
		));		
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
	
	
	private function _getUrlCancel($order) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v3/shop/shops/payment_error';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error'), true);		
	}
	
	
	private function _getUrlError($order) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v3/shop/shops/payment_error';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error'), true);
	}

	public function getPaymentName() {
		return 'b-payment.hu';
	}

	public function getSubmitUrl() {
		return Configure::read('Shop.PaymentProviders.BPayment.endpoint');		
	}

}

