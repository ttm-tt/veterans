<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;
use Cake\Validation\Validator;


class OrderAddressesTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->setTable('shop_order_addresses');
		
		$this->belongsTo('Shop.Countries', ['foreignKey' => 'country_id']);
		
	}
	
	
	public function validationDefault(Validator $validator) : Validator {
		
		$validator
			->notBlank('first_name')
			->notBlank('last_name')
			->notBlank('city')
			->notBlank('country_id', __('You must select a country'))
		;
				
		return $validator;
	}
}
?>
