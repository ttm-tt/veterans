<?php
namespace Shop\Payment;

use Shop\Payment\AbstractPayment;

// Implementation for CardPointe Hosted Payment Pages
class CardPointePayment extends AbstractPayment {
	public function __construct($controller) {
		parent::__construct($controller);
	}
	
	// Payment completed
	// Hidden callback from PSP
	public function completed($request) {
		file_put_contents(TMP . '/cardpointe/xxxcompleted-' . date('Ymd-His'), 
				print_r([
					'POST' => $request->is(['post', 'put']), 
					'Request' => $request], true)
		);
		
		if (!$request->is(['post', 'put']))
			return;

		$data = json_decode($request->getData('json'), true);
		
		$this->_controller->loadModel('Shop.Orders');

		if (empty($data['cf_orderid'])) {
			$orderId = $this->_controller->Orders->fieldByConditions('id', array(
				'invoice' => $data['invoice']
			));
		} else {
			$orderId = $data['cf_orderid'];
		}
		
		$data['order_id'] = $orderId;
		
		$order = $this->_controller->Orders->get($orderId);
				
		// Save or update data		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_card_pointe');
		
		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity($data)
		);

		if ($data['total'] != $order['outstanding']) {
			$status = 'FRD';
			file_put_contents(TMP . '/cardpointe/xxxfraud-' . date('Ymd-His'), 'amount mismatch: got ' . $data['total'] . ', expected ' . $order['outstanding']);
		} else {   
			// Only called if successful
			$status = 'PAID';
		}
		
		if ($status === 'PAID')
			$this->_controller->_onSuccess($orderId, $status);
		else 
			$this->_controller->_onError($orderId, $status);
	}

	// Optional callback when customer confirms payment
	public function confirm($orderId) {
		$this->_controller->loadModel('Shop.Orders');
		
		$order = $this->_controller->Orders->find('first', array(
			'conditions' => array('Order.id' => $orderId),
			'contain' => array(
				'InvoiceAddress' => array('Countries'),
			)
		));
		
		if ($order !== null) {			
			$this->request->session()->write('Order.ticket', $order['ticket']);
		
			$this->_controller->set('json_object', array(
				'orderid' => $orderId,
				'ticket' => $order['ticket'],
				'total' => $order['outstanding'],
				'invoice' => $order['invoice'],
				'customerId' => 'N/A',
				'billCompany' => '',
				'billFName' => $order['invoice_address']['first_name'],
				'billLName' => $order['invoice_address']['last_name'],
				'billAddress1' => $order['invoice_address']['street'],
				'billAddress2' => '',
				'billCity' => $order['invoice_address']['city'],
				'billZip' => $order['invoice_address']['zip_code'],
				'billCountry' => $order['invoice_address']['country']['iso_code_2'],
				'email' => $order['email'],
				'phone' => $order['invoice_address']['phone']
			));
		}
		$this->_controller->render('json');
	}

	// Get the payment details
	public function getOrderPayment($orderId) {
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_card_pointe');
		
		return $this->_controller->OrderPayments->find('all', array(
			'conditions' => array('order_id' => $orderId),
			'order' => ['created' => 'DESC']
		));
	}

	public function prepare($amount) {
		$this->_controller->set('amount', $amount);
		
		$this->_controller->render('Shops/Payment/card_pointe');		
	}

	// Called by wizard after closing the page
	public function process() {
		
	}

	// Cancel a payment
	public function storno($orderId, $amount) {
		return false;
	}

	// Payment was successful
	// IPayment: hidden_trigger_url
	public function success($request) {
		$this->_controller->loadModel('Shop.Orders');

		if (!empty($request->query['orderid'])) {
			$orderId = $request->query['orderid'];
		} else if (!empty($request->query['invoice'])) {
			$orderId = $this->_controller->Orders->fieldByConditions('id', array(
				'invoice' => $request->query['invoice']
			));			
		} else {
			$orderId = $this->_controller->Orders->fieldByConditions('id', array(
				'invoice' => $this->request->session()->read('Order.ticket')
			));			
		}
		
		if (empty($orderId))
			return;
		
		$this->_controller->_success($orderId);		
	}

	// Payment was not successful
	// IPayment: silent_error_url
	public function error($request) {
		$this->_controller->loadModel('Shop.Orders');

		if (!empty($request->query['orderid'])) {
			$orderId = $request->query['orderid'];
		} else if (!empty($request->query['invoice'])) {
			$orderId = $this->_controller->Orders->fieldByConditions('id', array(
				'invoice' => $request->query['invoice']
			));			
		} else {
			$orderId = $this->_controller->Orders->fieldByConditions('id', array(
				'invoice' => $this->request->session()->read('Order.ticket')
			));			
		}
		
		if (empty($orderId))
			return;
				
		$errMsg = 'The transaction has failed';
		
		// Set status to ERR, if not yet done
		// If the users cancels the order UrlKO was not called.
		$this->_controller->_onError($orderId, 'ERR');
		$this->_controller->_failure($orderId, $errMsg);		
	}

	public function getPaymentName() {
		return 'CardPointe';
	}

	public function getSubmitUrl() {
		return Configure::read('Shop.PaymentProviders.CardPointe.endpoint');		
	}

}
?>