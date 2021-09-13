<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Shop\Payment;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Shop\Payment\AbstractPayment;

/**
 * Description of AxeptaPayment
 *
 * @author ettu
 */
class PositivityPayment extends AbstractPayment {
	public function __construct($controller) {
		parent::__construct($controller);
	}
	
	/**
	 *  Payment completed Hidden callback from PSP
	 */
	public function completed($request) {
		file_put_contents(TMP . '/positivity/xxxcompleted-' . date('Ymd-His'), 
				print_r([
					'POST' => $request->is(['post', 'put']), 
					'Request' => $request], true)
		);
				
		if ($request->is(['post', 'put']))
			$data = $request->getData();
		else if ($request->is(['get']))
			$data = $request->getQuery();
		else
			return;
				
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');

		// Get order id. In test mode there is a random postfix to make the order id unique
		$orderId = explode('-', $data['oid'])[0] ?? 0;

		try {
			$order = $this->_controller->Orders->get($orderId);
		} catch (InvalidPrimaryKeyException | RecordNotFoundException $_ex) {
			$this->_UNUSED($_ex);
			file_put_contents(TMP . '/positivity/xxxerror-' . date('Ymd-His'), print_r($data, true));
			return;			
		}
		
		// Read (last) payment details for order to get txndate
		$details = $this->_controller->OrderPayments->find()
				->where(['order_id' => $orderId])
				->order(['created' => 'DESC'])
				->first()
		;
		
		if ($details === null)
			return;
		
		$txnDateTime = $details->created->format('Y:m:d-H:i:s');
		
		$details->value = json_encode($data);
		$this->_controller->OrderPayments->save($details);
		
		$amount = number_format($order->outstanding, 2, '.', '');
		$currency = 'EUR';
		$status = 'PAID';
		$sha = $this->_verifyHashCode($txnDateTime, $data);
		
		if ($sha !== $data['response_hash'] || 
				$currency !== $data['currency'] ||
				$amount !== $data['chargetotal']) {
			file_put_contents(TMP . '/positivity/xxxfraud-' . date('Ymd-His'), print_r(
				['got' => $sha, 'data' => $data], true)
			);
			$status = 'FRD';
		} else if ($data['status'] !== 'APPROVED') {
			file_put_contents(TMP . '/positivity/xxxerror-' . date('Ymd-His'), print_r($data, true));
			$status = 'ERR';
		} else {
			$status = 'PAID';
		}
		
		if ($status === 'PAID')
			$this->_controller->_onSuccess($orderId, $status);
		else 
			$this->_controller->_onError($orderId, $status);		
	}

	/**
	 *  Callback when customer confirms payment
	 */
	public function confirm($orderId) {
		$this->_controller->loadModel('Tournaments');
		
		$this->_controller->loadModel('Shop.Orders');
		$this->_controller->loadModel('Shop.OrderArticles');
		
		$order = $this->_controller->Orders->get($orderId, [
			'contain' => ['InvoiceAddresses']
		]);
		
		$ct = time();
		
		// Transaction time (see below) is used later to verify the hash, 
		// but it is not included in the parameters returned from the server.
		// To access it any time later create a new record in payment_details
		// (we need it anyway later) with the 'created' timestamp set to $ct
		$this->_controller->loadModel('Shop.OrderPayments');
		$this->_controller->OrderPayments->setTable('shop_order_payment_details');
		
		$this->_controller->OrderPayments->save(
				$this->_controller->OrderPayments->newEntity([
					'order_id' => $order->id,
					'payment' => 'positivity',
					'value' => '',
					'created' => date('Y-m-d H:i:s', $ct)
				])
		);
		
		$amount = number_format($order->outstanding, 2, '.', '');
		
		$configBaseName = 'Shop.PaymentProviders.Positivity';
				
		// In test mode we need to make our order id unique, but in production
		// we want to detect duplicates
		$isTest = Configure::read($configBaseName . '.test') == true;
		$storeId = Configure::read($configBaseName . '.accountData.storeId');
		$kSig = Configure::read($configBaseName . '.accountData.kSig');
		$txnDateTime = date('Y:m:d-H:i:s', $ct);
		$currency = 'EUR';

		$parameters = [
			'txntype' => 'PURCHASE',
			'timezone' => 'CET',
			'txndatetime' => $txnDateTime,
			'hash' => $this->_getHashCode($storeId, $txnDateTime, $amount, $currency, $kSig),
			'storename' => $storeId,
			'mode' => 'payonly',
			'currency' => $currency,
			'language' => 'EN',
			'responseSuccessURL' => $this->_getUrlSuccess($order),
			'responseFailURL' => $this->_getUrlError($order),
			'transactionNotificationURL' => $this->_getUrlCompleted($order),
			'chargetotal' => $amount,
			// In test environment the order id may be a duplicate so we append 
			// something unique
			'oid' => $isTest ? $order->id . '-' . uniqid() : $order->id,
			'invoicenumber' => $order->invoice,
		];
		
		$this->_controller->set('json_object', 
			$parameters
		);
		
		$this->_controller->render('json');		
	}

	/**
	 *  Payment was not successful, redirect initiated from PSP in case of error
	 */
	public function error($request) {
		file_put_contents(TMP . '/positivity/xxxerror-' . date('Ymd-His'), print_r($request, true));		
				
		if ($request->isPost())
			$data = $request->getData();
		else if ($request->isGet())
			$data = $request->getQuery();
		else
			return;
		
		$orderId = explode('-', $data['oid'])[0];
		
		$errMsg = 'The transaction has failed';
		
		// Set status to ERR, if not yet done
		// If the users cancels the order UrlKO was not called.
		$this->_controller->_onError($orderId, 'ERR');
		$this->_controller->_failure($orderId, $errMsg);		
	}

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

	public function getPaymentName() {
		return 'BNL POSitivity';
	}

	public function getSubmitUrl() {
		return Configure::read('Shop.PaymentProviders.Positivity.endpoint');		
	}

	public function prepare($amount) {
		$submitUrl = Configure::read('Shop.PaymentProviders.Positivity.endpoint');
		
		$this->_controller->set('amount', $amount);
		$this->_controller->set('submitUrl', $submitUrl);
		
		$this->_controller->render('Shops/Payment/positivity');				
	}

	/**
	 *  Called by wizard after closing the page
	 */
	public function process() {
		
	}

	/**
	 *  Cancel a payment
	 */
	public function storno($orderId, $amount) {
		return false;
	}

	/**
	 *  Payment was successful, redirect initiated from PSP in case of success
	 */
	public function success($request) {
		file_put_contents(TMP . '/positivity/xxxsuccess-' . date('Ymd-His'), print_r($request, true));		
		
		if ($request->isPost())
			$data = $request->getData();
		else if ($request->isGet())
			$data = $request->getQuery();
		else
			return;
				
		$orderId = explode('-', $data['oid'])[0];
		
		$this->_controller->_success($orderId);		
	}

	private function _getHashCode($storeId, $txnDateTime, $amount, $currency, $kSig) : string {
		$strToHash = $storeId . $txnDateTime . $amount . $currency . $kSig;
		$hex = bin2hex($strToHash);		
		$sha = sha1($hex);
		
		return $sha;
	}
	
	private function _verifyHashCode($txnDateTime, $data) : string {
		$configBaseName = 'Shop.PaymentProviders.Positivity';
				
		$storeId = Configure::read($configBaseName . '.accountData.storeId');
		$kSig = Configure::read($configBaseName . '.accountData.kSig');
		
		$strToHash = $kSig . $data['approval_code'] . $data['chargetotal'] . $data['currency'] . $txnDateTime . $storeId;
		$hex = bin2hex($strToHash);
		$sha = sha1($hex);

		return $sha;
	}
	
	private function _getUrlSuccess($order) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v4/' . $this->_controller->getRequest()->getParam('ds') . '/shop/shops/payment_success';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success'), true);
	}
	
	
	private function _getUrlError($order) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v4/' . $this->_controller->getRequest()->getParam('ds') . '/shop/shops/payment_error';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error'), true);		
	}

	private function _getUrlCompleted($order) {
		if (Configure::read('Shop.testUrl'))
			return 'https://galadriel.ttm.co.at/veterans-v4/' . $this->_controller->getRequest()->getParam('ds') . '/shop/shops/payment_complete';
		else
			return Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_complete'), true);
	}
}
