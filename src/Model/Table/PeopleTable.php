<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Table;

use App\Model\Table\AppTable;
use ArrayObject;
use Cake\Event\EventInterface;
use Cake\Datasource\EntityInterface;
use Cake\Validation\Validator;

class PeopleTable extends AppTable {


	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->addAssociations([
			'hasMany' => [
				'Registrations'
			],
			'belongsTo' => [
				'Nations',
				'Users'
			]
		]);
	}
	
	public function validationDefault(Validator $validator) : Validator {
		
		// Validate combination last_name and first_name is unique
		// TODO: Better put that into a application rule
		$validator
			->notBlank('first_name')
			->notBlank('last_name')
/*	
 * beforeMarshal takes care of unique display names			
			->add('display_name', [
				'unique' => [
					'rule' => 'validateUnique', 
					'provider' => 'table',
					'message' => __('A person with that name already exists'),
				],
			])
 */
			->allowEmpty('display_name')
			->inList('sex', ['M', 'F'])
			->date('dob', ['ymd', 'dmy', 'mdy'])
			->allowEmpty('dob', function($context) {
					// Only players have a 'P' in the extern_id
					// Acc. have an 'A' instead
					if (!empty($context['data']['extern_id']) && strpos($context['data']['extern_id'], 'P') !== false)
						return !empty($context['data']['dob']);

					return true;
				}, __('Birthday cannot be empty for players')
			)
			->notBlank('nation_id', __('You must select an association'))
		;
				
		return $validator;
	}
	
	// ----------------------------------------------------------------------
	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		parent::beforeSave($event, $entity, $options);
		
		if (empty($entity['display_name'])) {
			if (!$this->_calculateDisplayName($entity, $options))
				return false;
		}
		
		return true;			
	}
	
	private function _calculateDisplayName($entity, ArrayObject $options) {
		if (!empty($entity['display_name'])) 
			return true;
		
		// We don't have first_name and last_name always set:
		// Sometimes we save some fields only
		if (!isset($entity['last_name']))
			return true;
		if (!isset($entity['first_name']))
			return true;
		
		// Names cannot be blank
		$entity['display_name'] = 
				$entity['last_name'] . ', ' . $entity['first_name']
		;
		
		$clashes = $this->find('all', array(
			'conditions' => array(
				'display_name' => $entity['display_name'],
				'id <>' => (empty($entity['id']) ? 0 : $entity['id'])
			)
		));
			
		if ($clashes === null || $clashes->count() === 0)
			return true;
		
		if (!empty($entity['extern_id']))
			$entity['display_name'] .= ' (' . $entity['extern_id'] . ')';
		else
			return false;
				
		foreach ($clashes as $p) {
			if (!empty($p['extern_id']))
				$p['display_name'] .= ' (' . $p['extern_id'] . ')';
			else
				return false;
			
			$this->save($p, empty($options['modified']) ? [] : ['modified' => $options['modified']]);
		}

		return true;		
	}
}
?>
