<?php /* Copyright (c) 2022 Christoph Theis */ ?>
<?php
namespace Shop\Payment;

/*
 * Payment with Nest Pay http://www.asseco-see.com.tr
 */

use Shop\Payment\AbstractPayment;

use Cake\Core\Configure;
use Cake\Routing\Router;

Use Shop\Model\Table\OrderStatusTable;

/**
 * Description of NestPayPayment
 */
class NestPayPayment extends AbstractPayment {
	private $configBaseName = 'Shop.PaymentProviders.NestPay';
	
	// Deliver page for payment
	public function prepare($amount) {
		$this->_controller->set('amount', $amount);
		$this->_controller->set('submitUrl', Configure::read($this->configBaseName . '.endpoint'));		
			
		$shopCurrency = $this->_controller->_shopSettings['currency'];
		$bankCurrency = Configure::read('Shop.CurrencyConverter.currency', $shopCurrency);
		
		if ($shopCurrency !== $bankCurrency) {
			$driver = \Otherguy\Currency\DriverFactory::make(Configure::read('Shop.CurrencyConverter.engine'));
			$driver->accessKey(Configure::read('Shop.CurrencyConverter.key'));
			$driver->config('format', '1');

			$result = $driver->from($shopCurrency)->to($bankCurrency)->get();
		
			$bankAmount = $result->convert($amount, $shopCurrency, $bankCurrency);

			$this->_controller->set('bankCurrency', $bankCurrency);
			$this->_controller->set('bankAmount', $bankAmount);
			$this->_controller->set('bankExchange', $result->convert(1, $shopCurrency, $bankCurrency));
		}		

				
		$this->_controller->render('Shops/Payment/nestpay');						
	}

	// Called when customer confirms order
	public function confirm($orderId) {	
		$this->_controller->loadModel('Tournaments');
		
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Shop.OrderArticles');
		
		$order = $this->_controller->Orders->get($orderId, [
			'contain' => ['InvoiceAddresses' => 'Countries']
		]);
		
		$ct = time();
		
		$amount = number_format($order->outstanding, 2, '.', '');
		
		$clientId = Configure::read($this->configBaseName . '.accountData.clientId');
		$storeKey = Configure::read($this->configBaseName . '.accountData.storeKey');
		$rnd = sprintf('%020d', $order->id);
				
		$isTest = Configure::read($this->configBaseName . '.test') == true;

		$parameters = [
			'clientid' => $clientId,
			'oid' => sprintf('%05d', $orderId) . ($isTest ? '-' . substr('' . $ct, -8) : ''),
			'amount' => number_format($amount, 2),
			'currency' => Configure::read($this->configBaseName . '.currency'), 
			'okurl' => $this->_getUrlSuccess($orderId),
			'failurl' => $this->_getUrlError($orderId),
			'trantype' => 'Auth',
			'storetype' => '3d_pay_hosting', // 'pay_hosting', '3d_pay', '3d', '3d_pay_hosting'
			'lang' => 'en',	
			'rnd' => $rnd,
			'encoding' => 'utf-8',
			'hashAlgorithm' => 'ver2',
			'shopurl' => 'https://galadriel.ttm.co.at/evc2025/users/login'
		];
		
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
			
			$parameters['amount'] = number_format($amount, 2, '.', '');
		}		

		$hash = $this->_encrypt($parameters, $storeKey);	
		
		$parameters['hash'] = $hash;

		$this->_controller->set('json_object', $parameters);
		$this->_controller->render('json');		
	}

	// Callback when payment is completed
	public function completed($request) {
		file_put_contents(TMP . '/nestpay/xxxcompleted-' . date('Ymd-His'), 
				print_r([
					'POST/GET' => $request->is(['post', 'put', 'get']), 
					'Request' => $request], true)
		);				
		
		// Is never called
	}

	// Payment was successful
	public function success($request) {
		file_put_contents(TMP . '/nestpay/xxxsuccess-' . date('Ymd-His'), print_r(['request' => $request], true));		
		
		if ($request->isPost())
			$data = $request->getData();
		else if ($request->isGet())
			$data = $request->getQuery();
		else
			return;
		
		// Nothing we can do without order id
		if (empty($data['ReturnOid']))
			return;
		
		$orderId = explode('-', $data['ReturnOid'])[0];
		if (empty($orderId))
			return;
		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');

		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity([
					'order_id' => $orderId,
					'payment' => 'nestpayment',
					'value' => json_encode($data)
				])
		);

		$storeKey = Configure::read($this->configBaseName . '.accountData.storeKey');

		if (!$this->_verify($data, $storeKey)) {
			$this->_controller->_onError($orderId, 'ERR');
			$this->_controller->_failure($orderId, $data['ErrMsg'] ?: __d('user', 'An error occured'));
		}
		
		$this->_controller->set('nestpay', $data);

		$this->_controller->_onSuccess($orderId, 'PAID');
		$this->_controller->_success($orderId);				
	}

	// Payment was not successful
	public function error($request) {
		file_put_contents(TMP . '/nestpay/xxxerror-' . date('Ymd-His'), print_r(['request' => $request], true));				

		if ($request->isPost())
			$data = $request->getData();
		else if ($request->isGet())
			$data = $request->getQuery();
		else
			return;
		
		// Nothing we can do without order id
		if (empty($data['ReturnOid']))
			return;
		
		$orderId = explode('-', $data['ReturnOid'])[0];
		if (empty($orderId))
			return;
		
		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');

		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity([
					'order_id' => $orderId,
					'payment' => 'nestpayment',
					'value' => json_encode($data)
				])
		);

		$errMsg = 'The transaction has failed';
		
		$errMsg .= ' (' . $data['ErrMsg'] . ')';

		// Set status to ERR, if not yet done
		// If the users cancels the order UrlKO was not called.
		$this->_controller->_onError($orderId, 'ERR');
		$this->_controller->_failure($orderId, $errMsg);		
		$this->_controller->set('nestpay', $data);
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
	public function getPaymentName() {
		return 'NestPay';
	}
	
	// Get the logo requested by Nestpay
	public function getPaymentLogo(): string {
		return 'Payment/nestpay/kartica.png';
	}

	public function getSubmitUrl() {
		return '';
	}

	public function process() {
		
	}

	// Cancel a payment
	public function storno($orderId, $amount) {
		
	}
	
	// -------------------------------------------------------------------
	private function _getUrlSuccess($orderId) {
		if (Configure::read('Shop.testUrl', false) !== false)
			return 'https://galadriel.ttm.co.at/evc2025/shop/shops/payment_success?oid=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success', '?' => ['oid' => $orderId]), true);
	}
	
	
	private function _getUrlError($orderId) {
		if (Configure::read('Shop.testUrl', false) !== false)
			return 'https://galadriel.ttm.co.at/evc2025/shop/shops/payment_error?oid=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error', '?' => ['oid' => $orderId]), true);		
	}

	
	private function _encrypt(array $params, string $storeKey) : string {
		$plainText = 
				str_replace("|", "\\|", str_replace("\\", "\\\\", $params['clientid'])) . '|' . 
				str_replace("|", "\\|", str_replace("\\", "\\\\", $params['oid'])) . '|' .
				str_replace("|", "\\|", str_replace("\\", "\\\\", $params['amount'])) . '|' .
				str_replace("|", "\\|", str_replace("\\", "\\\\", $params['okurl'])) . '|' .
				str_replace("|", "\\|", str_replace("\\", "\\\\", $params['failurl'])) . '|' .
				str_replace("|", "\\|", str_replace("\\", "\\\\", $params['trantype'])) . '||' .
				str_replace("|", "\\|", str_replace("\\", "\\\\", $params['rnd'])) . '||||' .
				str_replace("|", "\\|", str_replace("\\", "\\\\", $params['currency'])) . '|' .
				str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey))
		;
		
		$hashValue = hash('sha512', $plainText);
		$hash = base64_encode(pack('H*', $hashValue));
		
		return $hash;
	}
	
	
	private function _verify(array $params, string $storeKey) : bool {
		if (($params['hashAlgorithm'] ?: null) === null) {
			// Required parameter missing
			return false;
		}
		if (($params['clientid'] ?? null) === null) {
			// Required parameter missing
			return false;
		}
		if (($params['ReturnOid'] ?? null) === null) {
			// Required parameter missing
			return false;
		}
		if (($params['Response'] ?? null) === null) {
			// Required parameter missing
			return false;
		}
		if (($params['HASHPARAMS'] ?? null) === null) {
			// Required parameter missing
			return false;
		}
		if (($params['HASHPARAMSVAL'] ?? null) === null) {
			// Required parameter missing
			return false;
		}
		if (($params['HASH'] ?? null) === null) {
			// Required parameter missing
			return false;
		}
				
		// Response must be Approved
		if ($params['Response'] !== 'Approved')
			return false;
		
		// Algorithm must be ver2
		if ($params['hashAlgorithm'] !== 'ver2')
			return false;
		
		$hashParams = $params['HASHPARAMS'];
		$hashParamsVal = $params['HASHPARAMSVAL'];
		$hashP = $params['HASH'];
		$paramsval = '';
		
		$parsedHashParams = explode("|", $hashParams);
		foreach ($parsedHashParams as $parsedHashParam) {
			$vl = ($params[$parsedHashParam] ?: null);
			if ($vl == null)
				$vl = "";
			$escapedValue = str_replace("\\", "\\\\", $vl);
			$escapedValue = str_replace("|", "\\|", $escapedValue);
			$paramsval = $paramsval . $escapedValue . "|";
		}
		
		$escapedStoreKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));
		$hashval = $paramsval . $escapedStoreKey;
		$hash = base64_encode(pack('H*',hash('sha512', $hashval)));	
		
		if ($hash !== $hashP) {
			file_put_contents(TMP . '/nestpay/xxxhasherror-' . date('Ymd-His'), print_r([
				'sent' => $hashP, 
				'expected' => $hash
			], true));	
			
			return false;
		}
		
		if ($hash !== $hashP)
			return false;
		
		// Check mdStatus, must between 1 (succes) and 4 (card not participating)
		// mdStatus == 0 means Authntication error
		// mdStatus 5..8 is system error or not available
		if (($data['mdStatus'] ?? 1) === 0)
			return false;
		
		return true;
	}
}
