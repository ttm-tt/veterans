<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Table;

use App\Model\Table\AppTable;

use Cake\Event\EventInterface;
use Cake\ Datasource\EntityInterface;
use ArrayObject;


class TournamentsTable extends AppTable {
	
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->addAssociations([
			'belongsTo' => [
				'Nations',
				'CompetitionManagers' => [
					'className' => 'Users',
					'foreignKey' => 'competition_manager_id'
				],
				'Organizers' => [
					'className' => 'Organisations',
					'foreignKey' => 'organizer_id'
				],
				'Committees' => [
					'className' => 'Organisations',
					'foreignKey' => 'committee_id'
				],
				'Hosts' => [
					'className' => 'Organisations',
					'foreignKey' => 'host_id'
				],
				'Contractors' => [
					'className' => 'Organisations',
					'foreignKey' => 'contractor_id'
				],
				// Should be DPA, but that would require definitions for Inflector
				'Dpas' => [
					'className' => 'Organisations',
					'foreignKey' => 'dpa_id'
				],
				
			],
			'hasMany' => [
				'Competitions',
				'Registrations',
				'Users'
			]
		]);
	}
	
	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		parent::beforeSave($event, $entity, $options);
		
		// Unset organisatoins without description
		// We are looking at the description because here the (short) name is 
		// optional but the description will always appear in the "contact us"
		if ($entity['committee'] && empty($entity['committee']['description'])) {
			if ($entity['committee']['id'])
				$this->Committees->delete($entity['committee']);
			$entity['committee_id'] = null;		
			unset($entity['committee']);
		}
		
		if ($entity['contractor'] && empty($entity['contractor']['description'])) {
			if ($entity['contractor']['id'])
				$this->Contractors->delete($entity['contractor']);
			$entity['contractor_id'] = null;	
			unset($entity['contractor']);
		}
		
		if ($entity['dpa'] && empty($entity['dpa']['description'])) {
			if ($entity['dpa']['id'])
				$this->Contractors->delete($entity['dpa']);
			$entity['dpa_id'] = null;	
			unset($entity['dpa']);
		}
	}			
}
?>
