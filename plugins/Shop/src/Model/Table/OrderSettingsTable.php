<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;

class OrderSettingsTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->setTable('shop_settings');
		
		$this->belongsTo('Tournaments');
		
		$this->hasMany('Shop.OrderCancellationFees', [
			'sort' => ['start' => 'ASC'],
			'foreignKey' => 'shop_settings_id',
			'saveStrategy' => 'replace'
		]);
	}
}
?>
