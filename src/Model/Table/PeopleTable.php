<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Table;

use App\Model\Table\AppTable;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;
use Cake\ORM\RulesChecker;
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
			->allowEmptyString('display_name')
			->inList('sex', ['M', 'F'])
			->date('dob', ['ymd', 'dmy', 'mdy'])
			->allowEmptyDateTime('dob', null, function($context) {
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
	

	// Application rules
	public function buildRules(RulesChecker $rules) : RulesChecker {
		$rules->addDelete(function($entity, $options) {
			return !$this->dispatchEvent('Person.deleteRule', null, $entity)->isStopped();
		});
		
		return $rules;
	}
	

	// Modify data
	public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options) {
		// Check para settings
		if (($data['is_para'] ?? 0) == 0) {
			$data['is_para'] = 0;
			$data['ptt_class'] = 0;
			$data['wchc'] = 0;
		}							
	}
	
	// ----------------------------------------------------------------------
	private $oldData;
	
	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		parent::beforeSave($event, $entity, $options);
		
		if (empty($entity['display_name'])) {
			if (!$this->_calculateDisplayName($entity, $options))
				return false;
		}

		if ($entity->isNew())
			$this->oldData = null;
		else
			$this->oldData = $this->get($entity->id)->toArray();

		return true;
	}

	// Store history
	public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		parent::afterSave($event, $entity, $options);
		
		$created = $entity->isNew();
		
		$oldData = $this->oldData;

		$newData = $this->get($entity->id)->toArray();

		$history = array();

		$uid = $this->_getUserId();

		if (!empty($oldData)) {
 			$count = TableRegistry::get('PersonHistories')->find('all', array(
				'conditions' => array('person_id' => $entity->id)))->count();

			if ($count === 0) {
				$h = array();
				$h['person_id'] = $newData['id'];
				$h['user_id'] = null;
				$h['field_name'] = 'created';
				$h['old_value'] = null;
				$h['new_value'] = serialize($oldData);
				$h['created'] = $oldData['modified'];

				$history[] = $h;
			}
		}

		if (empty($oldData) || $created) {
			$h = array();
			$h['person_id'] = $newData['id'];
			$h['user_id'] = $uid;
			$h['field_name'] = 'created';
			$h['old_value'] = null;
			$h['new_value'] = serialize($newData);
			$h['created'] = $newData['modified'];

			$history[] = $h;
		} else {
			foreach ($newData as $k => $v) {
				if ($k == 'created' || $k == 'modified')
					continue;

				if ($oldData[$k] == $newData[$k])
					continue;

				$h = array();
				$h['person_id'] = $newData['id'];
				$h['user_id'] = $uid;
				$h['field_name'] = $k;
				$h['old_value'] = $oldData[$k];
				$h['new_value'] = $newData[$k];
				$h['created'] = $newData['modified'];

				$history[] = $h;
			}
		}

		if (!empty($history)) {
			$personHistory = TableRegistry::get('PersonHistories');
			$personHistory->saveMany($personHistory->newEntities($history));
		}

		$this->oldData = false;
	}
	
	// -----------------------------------------------------------------------
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
