<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;

class OrderCancellationFeesTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->setTable('shop_cancellation_fees');
		
		$this->belongsTo('Shop.OrderSettings');
	}
}
?>
