<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;

class CountriesTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);

		$this->setTable('shop_countries');
		
		$this->hasMany('OrderAddresses', [
			'foreignKey' => 'country_id'
		]);
	}
}
?>
