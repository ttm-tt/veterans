<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;

class OrderPaymentsTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);

		// Changes per payment provider
		// $this->setTable('shop_order_ipayment');
		
		$this->belongsTo('Shop.Orders', ['foreignKey' => 'order_id']);
	}
}

?>
