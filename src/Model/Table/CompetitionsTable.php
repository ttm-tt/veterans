<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Table;

use App\Model\Table\AppTable;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ Datasource\EntityInterface;
use Cake\Validation\Validator;


class CompetitionsTable extends AppTable {

	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->setTable('competitions');
		
		$this->hasMany('CompetitionNationEntries');
		$this->belongsTo('Tournaments');
	}
	
	public function validationDefault(Validator $validator) : Validator {
		
		$validator
			->requirePresence('name')
			->notBlank('name')
			->add('name', 'unique', [
				'rule' => ['validateUnique', ['scope' => 'tournament_id']],
				'provider' => 'table',
				'message' => __('must be unique within a tournament')
			])
			->requirePresence('description')
			->notBlank('description')
			->requirePresence('sex')
			->inList('sex', ['M', 'F', 'X'])
			->requirePresence('type_of')
			->inList('type_of', ['S', 'D', 'X', 'T', 'C'])
			->add('type_of', 'custom', [
				'message' => __('value must be "Mixed"'),
				'rule' => function($value, $context) {
					return ($value === 'X') === ($context['data']['sex'] === 'X');
				}
			])
			->allowEmptyString('born')
			->integer('born')
			->lessThan('born', date('Y'))
			->greaterThanOrEqual('born', date('Y') - 120)
			->allowEmptyString('entries')
			->nonNegativeInteger('entries')
			->allowEmptyString('entries_host')
			->nonNegativeInteger('entries_host')
		;
		
		return $validator;
	}

	// ======================================================================
	// Search for player
	function findForPerson($tournament, $person) {
		$tid = is_numeric($tournament) ? $tournament : $tournament['id'];
		$sex = $person['sex'];

		$conditions = ['Competitions.tournament_id' => $tid];

		$conditions[] = ['OR' => [
			'Competitions.type_of' => 'X',
			'Competitions.sex' => $sex
		]];

		// If person's birthday is known, compare it.
		// Else (if not root) use only those competitions which do not require an age
		if (!empty($person['born'])) {
			$born = $person['born'];
			$conditions[] = ['OR' => [
				'Competitions.born IS NULL',
				[
					'Competitions.born <' => (date('Y') - 30), 
					'Competitions.born >=' => $born
				],
				[
					'Competitions.born >' => (date('Y') - 30),
					'Competitions.born <=' => $born
				]
			]];
		} else {
			$conditions[] = 'Competitions.born IS NULL';
		}

		// ptt_class. 
		if (($person['ptt_class'] ?? 0) == 0) {
			$conditions[] = 'ptt_class = 0';			
		} else {
			$conditions[] = ['OR' => [
				'Competitions.ptt_class >=' => $person['ptt_class'],
				'Competitions.ptt_class =' => 0,
			]];
			// $conditions['ptt_class >= '] = $person['ptt_class'];
		}

		$ret = array();

		if (empty($person['born'])) {
			// No restriction, choose from any event
			$ret['singles'] = $this->find('list', array(
				'fields' => array('id', 'description'),
				'conditions' => ['Competitions.type_of' => 'S'] + $conditions,
				'order' => [
					'Competitions.ptt_class' => 'DESC',
					'Competitions.description' => 'ASC'
				]
			))->toArray();
		} else if ($person['born'] < date('Y') - 30) {
			// Veterans, only the oldest is eligable
			$ret['singles'] = $this->find('list', array(
				'fields' => array('id', 'description'),
				'conditions' => ['Competitions.type_of' => 'S'] + $conditions,
				'order' => [
					'Competitions.ptt_class' => 'DESC',
					'Competitions.born' => 'ASC'
				],
				'limit' => 1
			))->toArray();
		} else if ($person['born'] > date('Y') - 30) {
			// Youth, only the youngest is eligable
			$ret['singles'] = $this->find('list', array(
				'fields' => array('id', 'description'),
				'conditions' => ['Competitions.type_of' => 'S'] + $conditions,
				'order' => [
					'Competitions.ptt_class' => 'DESC',
					'Competitions.born' => 'DESC'
				],
				'limit' => 1
			))->toArray();
		} else {
			// Regular, all are eligable
			$ret['singles'] = $this->find('list', array(
				'fields' => array('id', 'description'),
				'conditions' => ['Competitions.type_of' => 'S'] + $conditions,
				'order' => [
					'Competitions.ptt_class' => 'DESC',
					'Competitions.description' => 'ASC'
				]
			))->toArray();
		}

		$ret['doubles'] = $this->find('list', array(
			'fields' => array('id', 'description'),
			'conditions' => ['Competitions.type_of' => 'D'] + $conditions,
			'order' => [
				'Competitions.ptt_class' => 'DESC',
				'Competitions.description' => 'ASC'
			]
		))->toArray();

		$ret['mixed'] = $this->find('list', array(
			'fields' => array('id', 'description'),
			'conditions' => ['Competitions.type_of' => 'X'] + $conditions,
			'order' => [
				'Competitions.ptt_class' => 'DESC',
				'Competitions.description' => 'ASC'
			]
		))->toArray();

		$ret['teams'] = $this->find('list', array(
			'fields' => array('id', 'description'),
			'conditions' => ['Competitions.type_of' => 'T'] + $conditions,
			'order' => [
				'Competitions.ptt_class' => 'DESC',
				'Competitions.description' => 'ASC'
			]
		))->toArray();

		return $ret;
	}
	
	
	function findEventForPerson($person, $type, $tid, $partner = null) {
		if (empty($tid))
			return null;
		
		if (isset($person['person']))
			return $this->findEventForPerson($person['person'], $type, $tid, $partner);
		
		$year = is_array($person['dob']) ? $person['dob']['year'] : date('Y', strtotime($person['dob']));
		$partnerYear = ($partner === null ? null : (is_array($partner['dob']) ? $partner['dob']['year'] : date('Y', strtotime($partner['dob']))));
		$born = ($year < date('Y') - 30 ? 'born >=' : 'born <=');
		
		$conditions = array(
			'tournament_id' => $tid,
			'sex' => ($type == 'X' ? 'X' : $person['sex']),
			'type_of' => $type,
			$born => $year < date('Y') - 30 ? max($year, $partnerYear ?? $year) : min($year, $partnerYear ?? $year)
		);
		
		if (($person['ptt_class'] ?? 0) == 0) {
			$conditions[] = 'ptt_class = 0';
		} else {
			$conditions[] = ['OR' => [
				'Competitions.ptt_class >=' => $person['ptt_class'],
				'Competitions.ptt_class =' => 0,
			]];
			// $conditions['ptt_class >= '] = $person['ptt_class'];
		}

		$c = $this->find('all', array(
			'fields' => array('id'),
			'conditions' => $conditions,
			'order' => [
				'ptt_class' => 'DESC',
				'born' => ($year < date('Y') - 30 ? 'ASC' : 'DESC')
			]
		))->first();

		if (!empty($c))
			return $c['id'];
		else
			return null;
	}


	// ======================================================================
	// Called before a record is saved
	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		parent::beforeSave($event, $entity, $options);
		
		// Check if 'sex' is compatible with 'type' 
		if ($entity->type_of == 'X')
			$entity->sex = 'X';
		else if ($entity->sex == 'X')
			return false;
		
		return true;
	}
}
?>
