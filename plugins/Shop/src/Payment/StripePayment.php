<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Shop\Payment;

use Shop\Payment\AbstractPayment;

use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * Description of StripePayment
 *
 * @author ettu
 */
class StripePayment extends AbstractPayment {
	/**
	 * Get the submit URL
	 */
	public function getSubmitUrl() {
		
	}
	
	/**
	 *  Deliver page for input of credit card data
	 */
	public function prepare($amount) {
		$this->_controller->set('amount', $amount);
		
		$this->_controller->render('Shops/Payment/stripe');
	}
	
	/**
	 *  Callback when customer confirms payment
	 *  Sets parameters for POST call to payment provider
	 */
	public function confirm($orderId) {
		$this->_controller->loadModel('Shop.Orders');
		$order = $this->_controller->Orders->get($orderId);
		
		$this->_controller->loadModel('Tournaments');
		$tournament = $this->_controller->Tournaments->get($order->tournament_id);
		
		$options = [
			'payment_method_types' => ['card'],
			'mode' => 'payment',
			'success_url' => $this->_getSuccessURL($orderId),
			'cancel_url' => $this->_getCancelURL($orderId),
			'client_reference_id' => $orderId,
			'customer_email' => $order->email,
			// Registration is the line item
			'line_items' => [[
				'currency' => $this->_controller->_shopSettings['currency'],
				'name' => 'Registration fee for ' . ($tournament['name'] ?: ''),
				'quantity' => 1,
				'amount' => $order->total * 100
			]]
		];
		
		// Create session
		\Stripe\Stripe::setApiKey(Configure::read('Shop.PaymentProviders.Stripe.accountData.secret_key'));
		
		$session = \Stripe\Checkout\Session::create($options);
		
		$this->_controller->set('json_object', $session->id);
		
		$this->_controller->render('json');
	}
	
	/**
	 *  Called by wizard after closing the page
	 */
	public function process() {
		
	}
	
	/**
	 *  Payment was successful, redirect initiated from PSP in case of success
	 */
	public function success($request) {
		file_put_contents(TMP . '/stripe/xxxsuccess-' . date('Ymd-His'), print_r($request, true));
		
		$orderId = $request->getQuery('orderId');
		
		$this->_controller->_success($orderId);
	}
	
	/**
	 *  Payment was not successful, redirect initiated from PSP in case of error
	 */
	public function error($request) {
		file_put_contents(TMP . '/stripe/xxxerror-' . date('Ymd-His'), print_r($request, true));
		
		$orderId = $request->getQuery('orderId');
		
		$errMsg = 'The transaction has failed';
		
		// Set status to ERR, if not yet done
		// If the users cancels the order UrlKO was not called.
		$this->_controller->_onError($orderId, 'ERR');
		$this->_controller->_failure($orderId, $errMsg);		
	}
	
	/**
	 *  Payment completed Hidden callback from PSP
	 */
	public function completed($request) {
		file_put_contents(TMP . '/stripe/xxxcompleted-' . date('Ymd-His'), print_r($request, true));
		
		if ($request->is(['post', 'put']))
			$data = $request->data;
		else if ($request->is(['get']))
			$data = $request->query;
		else
			return;
				
		$orderId = $data['data']['object']['client_reference_id'];
		
		// Save or update data		
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');
		
		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity([
					'order_id' => $orderId,
					'payment' => 'stripe',
					'value' => json_encode($data)
				])
		);

		// Set secret key for API
		\Stripe\Stripe::setApiKey(Configure::read('Shop.PaymentProviders.Stripe.accountData.secret_key'));
		
		// Webhook key
		$whKey = Configure::read('Shop.PaymentProviders.Stripe.accountData.webhook_key');
		$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
		$event = null;
		$status = 'PAID';
		$body = $request->input();
		
		try {
			$event = \Stripe\Webhook::constructEvent($body, $sigHeader, $whKey);
		} catch (UnexpectedValueException $ex) {
			file_put_contents(TMP . '/stripe/xxxexception-' . date('Ymd-His'), print_r($ex, true));
			$status = 'ERR';
			$this->_controller->response->statusCode(400);
		} catch (\Stripe\Exception\SignatureVerificationException $ex) {
			file_put_contents(TMP . '/stripe/xxxfraud-' . date('Ymd-His'), print_r($ex, true));
			$status = 'FRD';
			$this->_controller->response->statusCode(400);
		}
		
		if ($status === 'PAID')
			$this->_controller->_onSuccess($orderId, $status);
		else 
			$this->_controller->_onError($orderId, $status);		
	}
	
	/**
	 *  Cancel a payment
	 */
	public function storno($orderId, $amount) {
		// Retrieve order
		$this->_controller->loadModel('Shop.Orders');
		$order = $this->_controller->Orders->get($orderId);
		
		// Set secret key for API
		\Stripe\Stripe::setApiKey(Configure::read('Shop.PaymentProviders.Stripe.accountData.secret_key'));
		
		// First get the payment intent
		$payments = $this->getOrderPayment($orderId);
		
		if ($payments === null || count($payments) === 0)
			return false;
		
		$piId = $payments[0]['data']['object']['payment_intent'] ?: null;
		
		if ($piId === null)
			return false;
		
		try {
			$pi = \Stripe\PaymentIntent::retrieve($piId);

			$data = \Stripe\Refund::create([
				'payment_intent' => $pi->id,
				'amount' => $amount * 100,
				'reason' => 'requested_by_customer'
			]);
			
			// Save or update data		
			$this->_controller->loadModel('Shop.OrderPayments');
			$this->_controller->OrderPayments->setTable('shop_order_payment_details');

			$this->_controller->OrderPayments->save(
					$this->_controller->OrderPayments->newEntity([
						'order_id' => $orderId,
						'payment' => 'stripe',
						'value' => json_encode($data)
					])
			);

		} catch (Exception $ex) {
			$this->_controller->MultipleFlash->setFlash(__('Could not refund payment: {0}', $ex->getMessage()), 'error');
			return false;
		}
		
		return true;
	}
	
	/**
	 *  Get the payment details
	 */
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
	
	/**
	 * Get the payment provider name
	 */
	public function getPaymentName() {
		return 'Stripe';
	}
	
	/**
	 *  Get the payment logo
	 */
	public function getPaymentLogo() {
		return null;
	}	
	
	
	// return URL for sucess
	private function _getSuccessURL($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v3/shop/shops/payment_success?sessionId={CHECKOUT_SESSION_ID}&orderId=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success', '?' => ['sessionId' => '{CHECKOUT_SESSION_ID}', 'orderId' => $orderId]), true);
	}
	
	
	// return the URL for cancel
	private function _getCancelURL($orderId) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v3/shop/shops/payment_error?sessionId={CHECKOUT_SESSION_ID}&orderId=' . $orderId;
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error', '?' => ['sessionId' => '{CHECKOUT_SESSION_ID}', 'orderId' => $orderId]), true);		
	}

}
