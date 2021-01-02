<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Table;

use App\Model\Table\AppTable;

use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;


class NationsTable extends AppTable {
	
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->hasMany('People');
		$this->hasMany('Users');
		$this->hasMany('Tournaments');
	}
	
	public function validationDefault(Validator $validator) : Validator {
		
		$validator
			->notBlank('name')
			->notBlank('description')
		;
				
		return $validator;
	}


	// Application rules
	public function buildRules(RulesChecker $rules) : RulesChecker {
		$rules->addDelete($rules->isNotLinkedTo('Users'));
		$rules->addDelete($rules->isNotLinkedTo('Tournaments'));
		$rules->addDelete($rules->isNotLinkedTo('People'));
		
		return $rules;
	}	
}
?>
