<?php
namespace Shop\Payment;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Shop\Payment\AbstractPayment;

// Implement Adapter to Redsys (Spain)
class RedsysPayment extends AbstractPayment {
	var $_returnMsg = array(
		  0	=> 'Success',
		900	=> 'Refund approved',
		101	=> 'Card expired',
		102	=> 'Card temporarily suspended',
		104	=> 'Transaction not allowed for the card',
		116	=> 'Insufficient funds',
		118	=> 'Card not registered',
		129	=> 'Security code (CVV) incorrect',
		180	=> 'Card not recognized',
		184	=> 'Cardholder authentication failed',
		190 => 'Transaction declined',
		191	=> 'Wrong expiration date',
		202	=> 'Card temporarily suspended'
	);
	
	public function __construct($controller) {
		parent::__construct($controller);
	}
	
	public function prepare($amount) {		
		$redsysUrl = Configure::read('Shop.PaymentProviders.Redsys.accountData.url');
		
		$this->_controller->set('amount', $amount * 100);
		$this->_controller->set('redsysUrl', $redsysUrl);
		
		$this->_controller->render('Shops/Payment/redsys');
	}

	public function confirm($orderId) {
		$this->_controller->loadModel('Tournaments');
		$tournament = $this->_controller->Tournaments->find('first', array(
			'recursive' => -1,
			'conditions' => array('Tournaments.id'=> $this->_controller->request->session()->read('Tournaments.id'))
		));
		
		$this->_controller->loadModel('Shop.Orders');
		$order = $this->_controller->Orders->get($orderId);
		
		$amount = $order['outstanding'] * 100;
		$code = Configure::read('Shop.PaymentProviders.Redsys.accountData.code');
		$currency = 978; // EUR
		$type = '0'; // Authorisation
		
		$order = str_pad($orderId, 9, '0', STR_PAD_LEFT);
		
		$paramArray = array(
			'Ds_Merchant_Amount' => $amount,
			'Ds_Merchant_Order' => $order,
			'Ds_Merchant_MerchantCode' => $code,
			'Ds_Merchant_Currency' => $currency,
			'Ds_Merchant_TransactionType' => $type,
			'Ds_Merchant_Terminal' => Configure::read('Shop.PaymentProviders.Redsys.accountData.terminal'),
			'Ds_Merchant_MerchantURL' => $this->_getMerchantUrl($order),
			'Ds_Merchant_UrlOK' => $this->_getMerchantUrlOK($order),
			'Ds_Merchant_UrlKO' => $this->_getMerchantUrlKO($order),
			'Ds_Merchant_ProductDescription' => __('Registration for {0}', $tournament['name'])
		);
		
		switch ($this->_controller->_getLanguage()) {
			case 'deu' :
				$paramArray['Ds_Merchant_ConsumerLanguage'] = '005';
				break;
			
			case 'spa' :
				$paramArray['Ds_Merchant_ConsumerLanguage'] = '001';
				break;
			
			default :
				$paramArray['Ds_Merchant_ConsumerLanguage'] = '002';
				break;
		}
		
		$parameters = $this->_createParameters($paramArray);
		$signature = $this->_createSignature($parameters, $order);
		
		$this->_controller->set('json_object', array(
			'order' => $order,
			'parameters' => $parameters,
			'signature' => $signature,
		));
		
		$this->_controller->render('json');
	}

	public function process() {
		
	}

	// Hidden callback
	public function completed($request) {
		file_put_contents(TMP . '/redsys/xxxcompleted-' . date('Ymd-His'), 
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
				
		$order = $request->query['order'];
		
		$params = $this->_decodeParameters(strtr($data['Ds_MerchantParameters'], '-_', '+/'));
		
		$signature = strtr($data['Ds_Signature'], '-_', '+/');
		
		$verifySignature = $this->_createSignature($data['Ds_MerchantParameters'], $order); 
		
		// Correct vars
		$tmp = explode('/', str_replace('%2F', '/', $params['Ds_Date']));
		$params['Ds_Date'] = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
		
		$params['Ds_Hour'] = str_replace('%3A', ':', $params['Ds_Hour']);
		
		$orderId = ltrim($order, '0');
		$params['order_id'] = $orderId;
		
		// Save or update data		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_redsys');
		
		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity($params)
		);

		// Calculate new status
		if ($signature != $verifySignature)
			$status = 'FRD';
		else if ($order != $params['Ds_Order'])
			$status = 'FRD';
		else if (!empty($params['Ds_TransactionType']) && $params['Ds_TransactionType'] = 3) {
			// This was a refund, don't update order
			if ($params['Ds_Response'] != 900)
				file_put_contents(TMP . '/redsys/xxxrefunderror-' . date('Ymd-His'), print_r(array(
					'params' => $params,
					'signature' => $signature,
					'verify' => $verifySignature,
					'status' => $status
				), true));
			
			return;
		}
		else if ($params['Ds_Response'] > 99)
			$status = 'ERR';
		else
			$status = 'PAID';
		
		file_put_contents(TMP . '/redsys/xxxcompleteparams-' . date('Ymd-His'), print_r(array(
			'params' => $params,
			'signature' => $signature,
			'verify' => $verifySignature,
			'status' => $status
		), true));

		if ($status === 'PAID')
			$this->_controller->_onSuccess($orderId);
		else 
			$this->_controller->_onError($orderId, $status);
	}

	// Redirect on success
	public function success($request) {
		file_put_contents(TMP . '/redsys/xxxsuccess-' . date('Ymd-His'), print_r($request, true));	
		if (!empty($request->query['order']))
			$orderId = $request->query['order'];
		else
			$orderId = false;
		$this->_controller->_success($orderId);
	}

	// Redirect on error
	public function error($request) {
		file_put_contents(TMP . '/redsys/xxxerror-' . date('Ymd-His'), print_r($request, true));
		$orderId = $request->query['order'];
		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_redsys');
		$errCode = $this->_controller->OrderPayments->fieldByConditions('DS_Response', 
				array('order_id' => $orderId)
		);
		
		if (!empty($this->_returnMsg[$errCode]))
			$errMsg = $this->_returnMsg[$errCode];
		else
			$errMsg = 'The transaction has failed';
		
		if (!empty($errCode))
			$errMsg .= ' (' . $errCode . ')';
		
		// Set status to ERR, if not yet done
		// If the users cancels the order UrlKO was not called.
		$this->_controller->_onError($orderId, 'ERR');
		$this->_controller->_failure($orderId, $errMsg);
	}

	public function storno($orderId, $amount) {
		if (!Configure::read('Shop.PaymentProviders.Redsys.allowRefund')) {
			$this->_controller->MultipleFlash->setFlash(__('Automatic refunds disabled, do the refund manually'), 'info');
			return true;
		}
		
		// Redsys returns with an html page. Why?
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_redsys');
		
		$payment = $this->_controller->OrderPayments->find('first', array(
			'conditions' => array('OrderPayments.order_id' => $orderId),
			'order' => ['created' => 'DESC']
		));
		
		if (empty($payment))
			return true;
		
		$this->_controller->loadModel('Tournaments');
		$tournament = $this->_controller->Tournaments->find('first', array(
			'recursive' => -1,
			'conditions' => array('Tournaments.id'=> $this->_controller->request->session()->read('Tournament.id'))
		));
		
		$code = Configure::read('Shop.PaymentProviders.Redsys.accountData.code');
		$terminal = Configure::read('Shop.PaymentProviders.Redsys.accountData.terminal');
		$currency = 978; // EUR
		$type = '3'; // Automatic Refund
		$order = str_pad($orderId, 4, '0', STR_PAD_LEFT);
		$merchantUrl = $this->_getMerchantUrl($order);	
		$redsysUrl = Configure::read('Shop.PaymentProviders.Redsys.endpoint');
		
		$amount = $amount * 100;
		
		$data = array(
			'Ds_Merchant_Amount' => $amount,
			'Ds_Merchant_Order' => $order,
			'Ds_Merchant_MerchantCode' => $code,
			'Ds_Merchant_Terminal' => $terminal,
			'Ds_Merchant_Currency' => $currency,
			'Ds_Merchant_TransactionType' => $type,
			'Ds_Merchant_MerchantURL' => $merchantUrl,
			'Ds_Merchant_AuthorisationCode' => $payment['Ds_AuthorisationCode'],
			'Ds_Merchant_ProductDescription' => __('Registration for {0}', $tournament['description'])
		);
		
		$parameters = $this->_createParameters($data);
		$signature = $this->_createSignature($parameters, $order);
		
		$httpSocket = new Client();
		try {
			$result = $httpSocket->post($redsysUrl, array(
				'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
				'Ds_MerchantParameters' => $parameters,
				'Ds_Signature' => $signature
			));
		} catch (SocketException $e) {
			$this->_controller->MultipleFlash->setFlash(__('Exception while trying to refund money: {0}', $e->getMessage()), 'error');
			return false;
		}
		
		file_put_contents(TMP . '/redsys/xxxrefund-' . date('Ymd-His'), print_r($result, true));	
		
		// Does Redsys really not return a proper error code but an entire html page?!
		if ( strpos($result, '<!--900:-->') !== false ) {
			$this->_controller->MultipleFlash->setFlash(__('Refund money for order successful'), 'success');
			return true;
		}
		
		$start = strpos($result, '<!--SIS');
		$errMsg = 
			sprintf( "Refund money for order returned %s", $start === false ? "unknown error" : substr($result, $start + 4, 7) );
		
		$this->_controller->MultipleFlash->setFlash($errMsg, 'error');
		$this->_controller->log($errMsg, 'error');
		
		return false;
	}

	public function getOrderPayment($orderId) {
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_redsys');
		
		return $this->_controller->OrderPayments->find('all', array(
			'conditions' => array('order_id' => $orderId),
			'order' => ['created' => 'DESC']
		));
	}
	
	private function _createParameters($params) {
		$json = json_encode($params);
		return base64_encode($json);
	} 
		
	private function _createSignature($parameters, $order) {
		$secret = Configure::read('Shop.PaymentProviders.Redsys.accountData.key');
		
		$keyBin = base64_decode($secret);
		$iv = implode(array_map("chr", array(0, 0, 0, 0, 0, 0, 0, 0)));
		if (!function_exists('mcrypt_encrypt')) {			
			$len = ceil(strlen($order) / 16) * 16;
			$key = substr(openssl_encrypt($order . str_repeat("\0", $len - strlen($order)), 
					'des-ede3-cbc', $keyBin, OPENSSL_RAW_DATA, $iv), 0, $len);
		} else {
			$key = mcrypt_encrypt(MCRYPT_3DES, $keyBin, $order, MCRYPT_MODE_CBC, $iv);
		}
		$res = hash_hmac('sha256', $parameters, $key, true);
		$signature = base64_encode($res);
		
		return $signature;		
	}
	
	private function _decodeParameters($data) {
		$json = base64_decode($data);		
		$params = json_decode($json, true);
		
		return $params;
	}
	
	private function _getMerchantUrl($order) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v3/shop/shops/payment_complete?order=' . $order;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_complete'), true) . '?order=' . $order;
	}
	
	private function _getMerchantUrlOK($order) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v3/shop/shops/payment_success?order=' . $order;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success'), true) . '?order=' . $order;
	}
	
	private function _getMerchantUrlKO($order) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v3/shop/shops/payment_error?order=' . $order;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error'), true) . '?order=' . $order;
	}

	public function getPaymentName() {
		return 'redsys.es';
	}

	public function getSubmitUrl() {
		return Configure::read('Shop.PaymentProviders.Redsys.endpoint');		
	}

}
?>