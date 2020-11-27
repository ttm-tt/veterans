<?php
namespace Shop\Model\Table;

use Shop\Model\Table\ShopAppModelTable;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

class AllotmentsTable extends ShopAppModelTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
	
		$this->setTable('shop_allotments');
		
		$this->belongsTo('Shop.Articles');
		$this->belongsTo('Users');
	}
	
	// Validation rules
	public function validationDefault(Validator $validator) : Validator {		
		$validator
			->notEmpty('article_id', __('You must select an article'))
			->notEmpty('user_id', __('You must select an user'))
			->notEmpty('allotment', __('You must enter the number of alloted articles'))
		;
		
		return $validator;
	}
	
	
	// Application Rules
	public function buildRulers(RulesChecker $rules) {
		// Allotment must be unique
		$rules->add($rules->isUnique(['article_id', 'user_id']));
		
		return $rules;
	}
}
?>
