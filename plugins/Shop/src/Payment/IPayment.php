<?php
namespace Shop\Payment;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Shop\Payment\AbstractPayment;

// An implementation for ipayment.de
class IPayment extends AbstractPayment {
	public function __construct($controller) {
		parent::__construct($controller);
	}
	
	public function prepare($amount) {		
		$this->_controller->loadModel('Shop.Countries');
		$this->_controller->set('countries', $this->_controller->Countries->find('list', array(
			'fields' => array('iso_code_2', 'name'),
			'order' => 'name'
		))->toArray());

		$this->_controller->set('address', $this->_controller->Cart->getAddress());

		$this->_controller->set('amount', $amount);

		$this->_controller->render('Shops/Payment/ipayment');
	}
	
	public function confirm($orderId) {
		$this->_controller->loadModel('Tournaments');
		
		$tid = $this->_controller->request->getSession()->read('Tournaments.id');
		$tournament = $this->_controller->Tournaments->find('first', array(
			'recursive' => -1,
			'conditions' => array('Tournament.id' => $tid)
		));

		$client = new SoapClient('https://ipayment.de/service/3.0/?wsdl', array(
			'trace' => 0,
			'exceptions' => 1
		));
		
		try {
			// createSession must be in double apostrophes, not in single
			// We must call via __soapCall to be able to pass arguments as an array and not an object
			$result = $client->__soapCall("createSession", array(
				'accountData' => Configure::read('Shop.PaymentProviders.Ipayment.accountData'),
				'transactionData' => array(
					'trxAmount' => $this->_controller->Cart->getTotal() * 100,
					'trxCurrency' => $this->_controller->_shopSettings['currency'],
					'shopperId' => $tournament['Tournament']['name'] . '-' . $orderId . '-' . gethostname(),
					'invoice_text' => $tournament['Tournament']['name'] . ' Registration'
				),
				'transactionType' => 'auth',
				'paymentType' => 'cc',
				'options' => array(
					'fromIp' => $_SERVER['REMOTE_ADDR'],
					'checkDoubleTrx' => true,
					'advancedStrictIdCheck' => true
				),
				'processorUrls' => array(
					'redirectUrl' => $this->_getRedirectUrl(),
					'silentErrporUrl' => $this->_getSilentErrorUrl(),
					'hiddenTriggerUrl' => $this->_getHiddenTriggerUrl()
				)
			));

			// TODO: Fehlermeldung
			$this->_controller->set('json_object', $result);
			$this->_controller->render('json');
		} catch (Exception $ex) {
			$this->_controller->log(print_r($ex, true), 'error');
			$this->_controller->set('json_object', '');
			$this->_controller->render('json');
		}
	}
	
	public function process() {
		
	}
	
	public function error($request) {
		file_put_contents(TMP . '/ipayment/xxxerror-' . date('Ymd-His'), print_r($request, true));

		if ($request->isPost())
			$data = $request->data;
		else if ($request->isGet())
			$data = $request->params;
		else
			return;
		
		$this->_handleCallback($data);
	}
	
	public function success($request) {
		file_put_contents(TMP . '/ipayment/xxxsuccess-' . date('Ymd-His'), print_r($request, true));

		if ($request->isPost())
			$data = $request->data;
		else if ($request->isGet())
			$data = $request->query;
		else
			return;
		
		$this->_handleCallback($data);
	}
	
	public function completed($request) {
		file_put_contents(TMP . '/ipayment/xxxcompleted-' . date('Ymd-His'), 
				print_r([
					'POST/GET' => $request->is(['post', 'put', 'get']), 
					'Request' => $request], true)
		);

		if ($request->is(['post', 'pust']))
			$data = $request->data;
		else if ($request->is(['get']))
			$data = $request->query;
		else
			return;
		
		$this->_handleCallback($data);
	}
	
	public function storno($orderId, $amount) {
		$this->_controller->loadModel('OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_ipayment');
		
		$payment = $this->_controller->OrderPayments->find('first', array(
			'conditions' => array('OrderPayments.order_id' => $orderId),
			'order' => ['created' => 'DESC']
		));
		
		if (empty($payment))
			return true;
		
		$client = new SoapClient('https://ipayment.de/service/3.0/?wsdl', array(
			'trace' => 1,
			'exceptions' => 0
		));

		// createSession must be in double apostrophes, not in single
		// We must call via __soapCall to be able to pass arguments as an array and not an object
		$refundResult = $client->__soapCall("refund", array(
			'accountData' => Configure::read('Shop.PaymentProviders.Ipayment.accountData'),
			'origTrxNumber' => $payment['ret_trx_number'],
			'transactionData' => array(
				'trxAmount' => $amount * 100,
				'trxCurrency' => $this->_controller->_shopSettings['currency']
			),
			'options' => array(
				'fromIp' => $payment['ret_ip'],
			),
		));

		$this->_controller->log(print_r($refundResult, true), true);

		if ($refundResult->status == 'SUCCESS') {
			$this->_controller->MultipleFlash(__('Refund money for order successful'), 'success');
			$result = true;
		} else {
			$this->_controller->MultipleFlash->setFlash(__('Could not refund money for order:') . ' ' . $result->errorDetails->retErrorMsg, 'error');
			$result = false;
		}

		return $result;
	}
	
	
	public function getOrderPayment($id) {
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_ipayment');
		
		return $this->_controller->OrderPayments->find('all', array(
			'conditions' => array('order_id' => $orderId),
			'order' => ['created' => 'DESC']
		));
	}
	
	private function _handleCallback($data) {
		// Update order
		$shopper_id = explode('-', $data['shopper_id']);
		$order_id = $shopper_id[1];
		$data['order_id'] = $order_id;

		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_ipayment');
		
		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity($data)
		);
		
		// Sanity tests
		$this->_controller->loadModel('Shop.Orders');
		$order = $this->_controllre->Orders->get($order_id);
		$amount = $order->outstanding;
		
		$wasSuccess = false;
		
		if ($data['ret_errorcode'] != 0)
			$this->_controller->_onError($order_id, 'ERR');
		else if ($data['trx_amount'] != $amount * 100)
			$this->_controller->_onError($order_id, 'FRD');
		else if ($data['trx_currency'] != $this->_controller->_shopSettings['currency'])
			$this->_controller->_onError($order_id, 'FRD');
		else {
			$wasSuccess = true;
			$this->_controller->_onSuccess($order_id);
		}
		
		if ($wasSuccess) {
			// Go to success page
			$this->_controller->_success($order_id);
		} else {
			// Go to error page
			$this->_controller->_failure($order_id, $data['ret_errormsg']);
		}
	}
	
	private function _getRedirectUrl() {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans/shop/shops/payment_success';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_complete'), true);
	}


	private function _getHiddenTriggerUrl() {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans/shop/shops/payment_complete';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success'), true);
	}


	private function _getSilentErrorUrl() {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans/shop/shops/payment_error';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error'), true);
	}

	public function getPaymentName() {
		return 'ipayment.de';
	}

	public function getSubmitUrl() {
		return Configure::read('Shop.PaymentProviders.IPayment.endpoint');		
	}

}

/*
class MySoapClient extends SoapClient {
	public function __doRequest($request, $location, $ction, $version, $one_way = 0) {
		file_put_contents(TMP . '/ipayment/xxx', $request);
	}
}
*/

?>