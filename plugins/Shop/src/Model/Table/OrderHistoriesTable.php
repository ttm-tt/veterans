<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;

class OrderHistoriesTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->setTable('shop_order_histories');
		
		$this->belongsTo('Shop.Orders', ['foreignKey' => 'order_id']);
		$this->belongsTo('Users');
	}
}
?>
