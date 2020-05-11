<?php
namespace Shop\Payment;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Shop\Payment\AbstractPayment;

class DibsPayment extends AbstractPayment {
	private  $_currencyCodes = array(
		'EUR' => 978,
		'SEK' => 752
	);
	
	private $_supportedLangs = array(
		'da', // Danish
		'en', // English
		'de', // German
		'es', // Spanish
		'fi', // Finnish
		'fo', // Faroese
		'fr', // French
		'it', // Italian
		'nl', // Dutch
		'no', // Norwegian
		'pl', // Polish
		'sv', // Swedish
		'kl'  // Greenlandic
	);
	
	// Deliver page for input of credit card data
	public function prepare($amount) {
		App::import('I18n', 'I18n');
		
		// Force a translation
		I18n::getInstance()->translate("");
		$lang = I18n::getInstance()->l10n->locale;
		
		if (strlen($lang) > 2) 
			$lang = substr($lang, 0, 2);

		if (!in_array($lang, $this->_supportedLangs))
			$lang = 'en';
		
		// Set the variables
		$dibsUrl = Configure::read('Shop.PaymentProviders.Dibs.endpoint');
		$this->_controller->set('dibsUrl', $dibsUrl);
		$this->_controller->set('amount', $amount * 100);
		$this->_controller->set('currency', $this->_controller->_shopSettings['currency']);
		$this->_controller->set('merchantId', Configure::read('Shop.PaymentProviders.Dibs.accountData.merchant'));
		$this->_controller->set('lang', $lang); // FIXME
		$this->_controller->set('test', Configure::read('App.test'));
		
		$this->_controller->render('Shops/Payment/dibs');
	}

	// Optional callback when customer confirms payment
	public function confirm($orderId) {
		$this->_controller->loadModel('Shop.Orders');
		$order = $this->_controller->Orders->get($orderId);
		
		$invoice = str_replace(' ', '.', $order['invoice']);
		
		$md5Params = 
				'' . 
				'merchant=' . Configure::read('Shop.PaymentProviders.Dibs.accountData.merchant') .
				'&orderid=' . $invoice .
				'&currency=' . $this->_controller->_shopSettings['currency'] .
				'&amount=' . $order['outstanding'] * 100
		;
		
		$md5Key = 
				MD5(Configure::read('Shop.PaymentProviders.Dibs.accountData.k2') . 
				MD5(Configure::read('Shop.PaymentProviders.Dibs.accountData.k1') . 
				$md5Params
		));
		
		$this->_controller->set('json_object', array(
			'accepturl' => $this->_getAcceptUrl($orderId),
			'callbackurl' => $this->_getCallbackUrl($orderId),
			'cancelurl' => $this->_getCancelUrl($orderId),
			'declineurl' => $this->_getDeclineUrl($orderId),
			'invoice' => $invoice,
			'md5key' => $md5Key,
			'order_id' => $orderId
		));
		
		$this->_controller->render('json');		
	}

	// Called by wizard after closing the page
	public function process() {
		
	}

	// Payment was successful (client redirected here
	public function success($request) {
		file_put_contents(TMP . '/dibs/xxxsuccess-' . date('Ymd-His'), print_r($request, true));		
		
		if (!empty($request->data['order_id']))
			$orderId = $request->data['order_id'];
		else
			$orderId = false;
		
		$this->_controller->_success($orderId);
	}

	// Payment was cancelled, redirect to select payment
	public function error($request) {
		file_put_contents(TMP . '/dibs/xxxerror-' . date('Ymd-His'), print_r($request, true));		

		if ($request->isPost())
			$data = $request->data;
		else if ($request->isGet())
			$data = $request->query;
		else
			return;

		// May be an erraneous call
		if (empty($data['order_id']))
			return;
		
		$orderId = $data['order_id'];
		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_dibs');
		$statuscode = $this->_controller->OrderPayments->fieldByConditions('statuscode', 
				array('order_id' => $orderId)
		);
		
		if (empty($statuscode)) {
			$this->_controller->OrderPayments->save($data);
		}
		
		$errMsg = 'The transaction has failed';
		
		if (!empty($statuscode))
			$errMsg .= ' (' . $statuscode . ')';

		// Set status to ERR, if not yet done
		// If the users cancels the order UrlKO was not called.
		$this->_controller->_onError($orderId, 'ERR');
		$this->_controller->_failure($orderId, $errMsg);
	}

	// Payment completed (hidden callback)
	public function completed($request) {
		file_put_contents(TMP . '/dibs/xxxcompleted-' . date('Ymd-His'), 
				print_r([
					'POST/GET' => $request->is(['post', 'put', 'get']),
					'Request' => $request], true)
		);
		
		if ($request->is(['post', 'put']))
			$data = $request->data;
		else if ($request->is(['get']))
			$data = $request->query;
		else
			return;
				
		$orderId = $data['order_id'];
		
		$this->_controller->loadModel('Shop.Orders');
		$order = $this->_controller->Orders->get($orderId);
		
		$checkParams = 
			'' .
			'transact=' . $data['transact'] .
			'&amount=' . $data['amount'] .
			'&currency=' . $this->_currencyCodes[$data['currency']]
		;
		
		$checkMd5Key = 
				MD5(Configure::read('Shop.PaymentProviders.Dibs.accountData.k2') . 
				MD5(Configure::read('Shop.PaymentProviders.Dibs.accountData.k1') . 
				$checkParams
		));
		
		// Save or update data		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_dibs');
		
		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity($data)
		);

		if ($data['currency'] != $this->_controller->_shopSettings['currency']) {
			$status = 'FRD';
		} else if ($data['amount'] != $order['outstanding'] * 100) {
			$status = 'FRD';
		} else if ($data['authkey'] != $checkMd5Key) {
			$status = 'FRD';
			file_put_contents(TMP . '/dibs/xxxfraud-' . date('Ymd-His'), 'MD5 mismatch, expected ' . $checkMd5Key);
		} else if (
				$data['statuscode'] == 5  ||    // Capture completed
				$data['statuscode'] == 11 ||    // Refund completed
				$data['statuscode'] == 15 ) {   // Refund pending
			$status = 'PAID';
		} else if (
				$data['statuscode'] == 2  ||    // Authorization approved
				$data['statuscode'] == 12 ) {   // Capture pending
			$status = 'PEND';
		} else {
			$status = 'ERR';
		}
		
		if ($status === 'PAID')
			$this->_controller->_onSuccess($orderId, $status);
		else if ($status === 'PEND')
			$this->_controller->_onSuccess($orderId, $status);
		else 
			$this->_controller->_onError($orderId, $status);
	}

	// Cancel a payment
	public function storno($orderId, $amount) {
		if (!Configure::read('Shop.PaymentProviders.Dibs.allowRefund')) {
			$this->_controller->MultipleFlash->setFlash(__('Automatic refunds disabled, do the refund manually'), 'info');
			return true;
		}

		// Within 60 days: RefundTransaction
		// After 60 days: MassPay
		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_dibs');
		
		$payment = $this->_controller->OrderPayments->find('first', array(
			'conditions' => array('OrderPayments.order_id' => $orderId),
			'order' => ['created' => 'DESC']
		));
		
		if (empty($payment))
			return true;
		
		$this->_controller->loadModel('Shop.Orders');
		$order = $this->_controller->Order->find('first', array(
			'recursive' => -1,
			'contain' => array('OrderStatus'),
			'conditions' => array('Orders.id' => $orderId)
		));
		
		if (empty($order)) {
			$this->_controller->MultipleFlash(__('Invalid order id given'), 'error');
			return false;
		}

		$md5Params = 
			'merchant=' . Configure::read('Shop.PaymentProviders.Dibs.accountData.merchant') .
			'&orderid=' . $payment['orderid'] .  // which is invoice. Not order_id
			'&transact=' . $payment['transact'] .
			'&amount=' . $amount * 100
		;
		$md5key = 
				MD5(Configure::read('Shop.PaymentProviders.Dibs.accountData.k2') . 
				MD5(Configure::read('Shop.PaymentProviders.Dibs.accountData.k1') . 
				$md5Params
		));
		$params = array(
			'amount' => $amount * 100,
			'currency' => $this->_controller->_shopSettings['currency'],
			'md5key' => $md5key,
			'merchant' => Configure::read('Shop.PaymentProviders.Dibs.accountData.merchant'),
			'orderid' => $payment['orderid'], // orderid (invoice) and not order_id
			'transact' => $payment['transact']
		);
				
		$request = http_build_query($params);
		
		$curlOptions = array(
			CURLOPT_URL => Configure::read('Shop.PaymentProviders.Dibs.refundEndpoint'),
			CURLOPT_USERPWD => 
				Configure::read('Shop.PaymentProviders.Dibs.accountData.merchant') . ':' . 
				Configure::read('Shop.PaymentProviders.Dibs.accountData.password'),
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
			$this->_controller->MultipleFlash->setFlash(__("Failed to make refund: {0}", curl_errno($curl)), 'error');
			return false;
		} else {
			curl_close($curl);
			$responseArray = array();
			parse_str($response, $responseArray);
		}
		
		file_put_contents(TMP . '/dibs/xxxrefund-' . date('Ymd-His'), print_r($responseArray, true));
		
		if ($responseArray['result'] == 0) {
			$this->_controller->MultipleFlash->setFlash(__('Refund money for order successful'), 'success');
			return true;
		}
		
		$msg = '';
		if (!empty($responseArray['message']))
			$msg = ' ' . $responseArray['message'];
		
		$this->_controller->MultipleFlash->setFlash(__('Failed to make refund with return code {0}{1}', $responseArray['result'], $msg), 'error');
	}

	// Get the payment details
	public function getOrderPayment($orderId) {
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_dibs');
		
		return $this->_controller->OrderPayments->find('all', array(
			'conditions' => array('order_id' => $orderId),
			'order' => ['created' => 'DESC']
		));
		
	}
	
	public function getPaymentLogo() {
		return 'https://cdn.dibspayment.com/logo/checkout/combo/horiz/DIBS_checkout_kombo_horizontal_04.png';
	}
	
	// The client will be redirected to this URL if the payment was successful
	private function _getAcceptUrl($orderId) {
		return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success'), true) . '?order_id=' . $orderId;
	}
	
	// The PSP will call this URL to notify about the state of a transaction
	private function _getCallbackUrl($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans/shop/shops/payment_complete';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_complete'), true);
	}
	
	// The client will be redirected if he cancels the payment
	private function _getCancelUrl($orderId) {
		return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error'), true);
	}
	
	// The client will be redirected if the transaction is declined
	private function _getDeclineUrl($orderId) {
		return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error'), true);
	}

	public function getPaymentName() {
		return 'DIBS Payment Services';
	}

	public function getSubmitUrl() {
		return Configure::read('Shop.PaymentProviders.Dibs.endpoint');		
	}

}

?>