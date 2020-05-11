<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;

class OrderAddressesTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->setTable('shop_order_addresses');
		
		$this->belongsTo('Shop.Countries', ['foreignKey' => 'country_id']);
		
	}
}
?>
