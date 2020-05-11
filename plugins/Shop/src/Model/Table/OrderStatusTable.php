<?php
namespace Shop\Model\Table;

use Cake\ORM\TableRegistry;
use Shop\Model\Table\ShopAppModelTable;

class OrderStatusTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->setTable('shop_order_status');
		
		$this->hasMany('Shop.Orders', ['foreignKey' => 'order_id']);
	}

	private static $_map = null;

	public static function getInitiateId() {
		return OrderStatusTable::_getId('INIT');
	}
	
	public static function getWaitingListId() {
		return OrderStatusTable::_getId('WAIT');
	}
	
	public static function getPendingId() {
		return OrderStatusTable::_getId('PEND');
	}
	
	public static function getDelayedId() {
		return OrderStatusTable::_getId('DEL');
	}
	
	public static function getInvoiceId() {
		return OrderStatusTable::_getId('INVO');
	}
	
	public static function getPaidId() {
		return OrderStatusTable::_getId('PAID');
	}
	
	public static function getCancelledId() {
		return OrderStatusTable::_getId('CANC');
	}
	
	// Don't repeat yourself
	private static function _getId($name) {
		if (OrderStatusTable::$_map == null) {
			$stati = TableRegistry::get('Shop.OrderStatus');
			OrderStatusTable::$_map = $stati->find('list', array(
				'fields' => array('name', 'id')
			))->toArray();
		}

		return OrderStatusTable::$_map[$name];
	}
}
?>
