<?php
namespace Shop\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

class ShopAppController extends AppController {
	// Models loaded on the fly
	public $Articles = null;
	public $ArticleVariants = null;
	public $Allotments = null;
	public $Countries = null;
	public $Orders = null;
	public $OrderAddresses = null;
	public $OrderArticles = null;
	public $OrderPayments = null;
	public $OrderSettings = null;
	public $OrderStatus = null;
	public $OrderCancellationFees = null;

	var $_shopSettings;

	public function beforeFilter(EventInterface $event) {
		parent::beforeFilter($event);
		
		if ($this->request->getSession()->check('Tournaments.id')) {
			$this->loadModel('Shop.OrderSettings');
			$tmp = $this->OrderSettings->find('all', array(
				'contain' => ['OrderCancellationFees'],
				'conditions' => array(
					'tournament_id' => $this->request->getSession()->read('Tournaments.id')
				)
			))->first();
			
			if (empty($tmp)) {
				$tmp = array(
					'invoice_no_prefix' => '', 
					'invoice_no_postfix' => '', 
					'currency' => 'EUR'
				);
			}
			
			$this->loadModel('Shop.OrderCancellationFees');
			
			$tmp['cancellation_fee'] = 
				$this->OrderCancellationFees->fieldByConditions('fee', [
					'shop_settings_id' => $tmp['id'],
					'start <=' => date('Y-m-d'),
				], [
					'order' => ['start' => 'DESC']
				]) ?: 0;
			
			$this->_shopSettings = $tmp;
			
			$this->set('shopSettings', $this->_shopSettings);
		}
	}
	
	protected function _getPayment($what = null) {
		if ($what === null)
			$what = Configure::read('Shop.payment');
		if (empty($what))
			$engineClass = 'DummyPayment';
		else
			$engineClass = Configure::read('Shop.PaymentProviders.' . $what . '.engine');

		if (empty($engineClass))
			$engineClass = 'DummyPayment';

		$engineClass = 'Shop\\Payment\\' . $engineClass;
		
		return new $engineClass($this);
	}
}
