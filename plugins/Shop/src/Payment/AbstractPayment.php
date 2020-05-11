<?php
namespace Shop\Payment;

abstract class AbstractPayment {
	protected $_controller = null;
	
	public function __construct($controller) {
		$this->_controller = $controller;
	}
	
	/**
	 * Get the submit URL
	 */
	abstract public function getSubmitUrl();
	
	/**
	 *  Deliver page for input of credit card data
	 */
	abstract public function prepare($amount);
	
	/**
	 *  Callback when customer confirms payment
	 *  Sets parameters for POST call to payment provider
	 */
	abstract public function confirm($orderId);
	
	/**
	 *  Called by wizard after closing the page
	 */
	abstract public function process();
	
	/**
	 *  Payment was successful, redirect initiated from PSP in case of success
	 */
	abstract public function success($request);
	
	/**
	 *  Payment was not successful, redirect initiated from PSP in case of error
	 */
	abstract public function error($request);
	
	/**
	 *  Payment completed Hidden callback from PSP
	 */
	abstract public function completed($request);
	
	/**
	 *  Cancel a payment
	 */
	abstract public function storno($orderId, $amount);
	
	/**
	 *  Get the payment details
	 */
	abstract public function getOrderPayment($orderId);
	
	/**
	 * Get the payment provider name
	 */
	abstract public function getPaymentName();
	
	/**
	 *  Get the payment logo
	 */
	public function getPaymentLogo() {
		return null;
	}	
}
?>