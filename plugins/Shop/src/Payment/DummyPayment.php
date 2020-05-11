<?php
namespace Shop\Payment;

use Shop\Payment\AbstractPayment;
use Cake\Routing\Router;


// A dummy implementation of AbstractPayment to be used as a replacement
class DummyPayment extends AbstractPayment {
	public function __construct($controller) {
		parent::__construct($controller);
	}
	
	public function completed($request) {
		if ($request->isPost())
			$data = $request->data;
		else if ($request->isGet())
			$data = $request->query;
		else
			return;
		
		$orderId = $data['orderid'];
		$error = $data['error'] ?? null;
		
		if ($error === null)
			$this->_controller->_onSuccess($orderId);
		else
			$this->_controller->_onError($orderId, 'ERR');		
		
		return $this->_controller->redirect(['action' => 'payment_success', '?' => ['orderid' => $orderId]]);
	}

	public function confirm($orderId) {
		$this->_controller->set('json_object', array('orderid' => $orderId));
		$this->_controller->render('json');
	}

	public function getOrderPayment($orderId) {
		return false;
	}

	public function prepare($amount) {
		$this->_controller->set('amount', $amount);
		
		$this->_controller->render('Shops/Payment/dummy');		
	}

	public function process() {
		
	}

	public function storno($orderId, $amount) {
		return true;
	}

	public function success($request) {
		if ($request->isPost())
			$orderId = $request->getData('orderid');
		else
			$orderId = $request->getQuery('orderid');
		
		$this->_controller->_success($orderId);		
	}

	public function error($request) {
		if ($request->isPost())
			$orderId = $request->getData('orderid');
		else
			$orderId = $request->getQuery('orderid');
		
		$errMsg = 'The transaction has failed';
		
		// Set status to ERR, if not yet done
		// If the users cancels the order UrlKO was not called.
		$this->_controller->_onError($orderId, 'ERR');
		$this->_controller->_failure($orderId, $errMsg);		
	}

	public function getPaymentName() {
		return 'Dummy';
	}

	public function getSubmitUrl() {
		return Router::url(array('action' => 'payment_complete'));
	}

}
?>