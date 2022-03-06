<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Table;

use App\Model\Table\AppTable;

use Cake\Event\EventInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\Http\Session;
use ArrayObject;

class ParticipantsTable extends AppTable {
	
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->belongsTo('Registrations');
		
		$this->belongsTo('Singles', [
			'className' => 'Competitions',
			'foreignKey' => 'single_id'
		]);
		
		
		$this->belongsTo('Doubles', [
			'className' => 'Competitions',
			'foreignKey' => 'double_id'
		]);
		
		$this->belongsTo('DoublePartners', [
			'className' => 'Registrations',
			'foreignKey' => 'double_partner_id'
		]);
		
		$this->belongsTo('Mixed', [
			'className' => 'Competitions',
			'foreignKey' => 'mixed_id'
		]);
		
		$this->belongsTo('MixedPartners', [
			'className' => 'Registrations',
			'foreign_key' => 'mixed_partner_id'
		]);
		
		$this->belongsTo('Teams', [
			'className' => 'Competitions',
			'foreignKey' => 'team_id'
		]);
	}

	var $oldData;

	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		parent::beforeSave($event, $entity, $options);
		
		if ($entity->isNew())
			$this->oldData = null;
		else 
			$this->oldData = $this->get($entity->id)->toArray();
	}


	public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		parent::afterSave($event, $entity, $options);

		$oldData = $this->oldData;

		$newData = $this->get($entity->id)->toArray();

		$histories = array();

		$tid = $this->Registrations->fieldByConditions('tournament_id', array(
					'id' => $newData['registration_id']
		));

		$uid = (new Session())->read('Auth.User.id');

		if (empty($oldData)) {
			$h = array();
			$h['registration_id'] = $newData['registration_id'];
			$h['tournament_id'] = $tid;
			$h['user_id'] = $uid;
			$h['field_name'] = 'created';
			$h['old_value'] = null;
			$h['new_value'] = serialize($newData);
			$h['created'] = $newData['modified'];

			$histories[] = $h;

			// Also records for wanted double / mixed partner, if any
			if (!empty($newData['double_partner_id'])) {
				$h['registration_id'] = $newData['double_partner_id'];
				$currentPartner = $this->fieldByConditions('double_partner_id', array('registration_id' => $h['registration_id']));
				if ($currentPartner == $newData['registration_id'])
					$h['field_name'] = 'double_partner_confirmed';
				else
					$h['field_name'] = 'double_partner_wanted';
				$h['old_value'] = null;
				$h['new_value'] = $newData['registration_id'];
				$h['created'] = $newData['modified'];

				$histories[] = $h;
			}

			if (!empty($newData['mixed_partner_id'])) {
				$h['registration_id'] = $newData['mixed_partner_id'];
				$currentPartner = $this->fieldByConditions('mixed_partner_id', array('registration_id' => $h['registration_id']));
				if ($currentPartner == $newData['registration_id'])
					$h['field_name'] = 'mixed_partner_confirmed';
				else
					$h['field_name'] = 'mixed_partner_wanted';
				$h['old_value'] = null;
				$h['new_value'] = $newData['registration_id'];
				$h['created'] = $newData['modified'];

				$histories[] = $h;
			}
		} else if ($oldData['cancelled'] && !$newData['cancelled']) {
			// Player resurrected: treat as created
			$h = array();
			$h['registration_id'] = $newData['registration_id'];
			$h['tournament_id'] = $tid;
			$h['user_id'] = $uid;
			$h['field_name'] = 'created';
			$h['old_value'] = null;
			$h['new_value'] = serialize($newData);
			$h['created'] = $newData['modified'];

			$histories[] = $h;

			// Also records for double / mixed partner, if any and if not still cancelled
			if (empty($newData['double_cancelled'])) {
				if (!empty($newData['double_partner_id'])) {
					$h['registration_id'] = $newData['double_partner_id'];
					$currentPartner = $this->fieldByConditions('double_partner_id', array('registration_id' => $h['registration_id']));
					if ($currentPartner == $newData['registration_id'])
						$h['field_name'] = 'double_partner_confirmed';
					else
						$h['field_name'] = 'double_partner_wanted';
					$h['old_value'] = null;
					$h['new_value'] = $newData['registration_id'];
					$h['created'] = $newData['modified'];

					$histories[] = $h;
				}
			}

			if (empty($newData['mixed_cancelled'])) {
				if (!empty($newData['mixed_partner_id'])) {
					$h['registration_id'] = $newData['mixed_partner_id'];
					$currentPartner = $this->fieldByConditions('mixed_partner_id', array('registration_id' => $h['registration_id']));
					if ($currentPartner == $newData['registration_id'])
						$h['field_name'] = 'mixed_partner_confirmed';
					else
						$h['field_name'] = 'mixed_partner_wanted';
					$h['old_value'] = null;
					$h['new_value'] = $newData['registration_id'];
					$h['created'] = $newData['modified'];

					$histories[] = $h;
				}
			}
		} else if (!$oldData['cancelled'] && $newData['cancelled']) {
			// Player cancelled
			$h = array();
			$h['registration_id'] = $newData['registration_id'];
			$h['tournament_id'] = $tid;
			$h['user_id'] = $uid;
			$h['field_name'] = 'cancelled';
			$h['new_value'] = serialize($newData);
			$h['old_value'] = null;
			$h['created'] = $newData['modified'];

			$histories[] = $h;

			// Also records for double / mixed partner, if not already cancelled
			if (empty($oldData['double_cancelled'])) {
				if (!empty($oldData['double_partner_id'])) {
					$h['registration_id'] = $oldData['double_partner_id'];
					$h['field_name'] = 'double_partner_withdrawn';
					$h['old_value'] = $oldData['registration_id'];
					$h['new_value'] = null;
					$h['created'] = $newData['modified'];

					$histories[] = $h;
				}
			}

			if (empty($oldData['mixed_cancelled'])) {
				if (!empty($oldData['mixed_partner_id'])) {
					$h['registration_id'] = $oldData['mixed_partner_id'];
					$h['field_name'] = 'mixed_partner_withdrawn';
					$h['old_value'] = $oldData['registration_id'];
					$h['new_value'] = null;
					$h['created'] = $newData['modified'];

					$histories[] = $h;
				}
			}
		} else {
			foreach ($newData as $k => $v) {
				if ($k == 'created' || $k == 'modified')
					continue;

				if ($oldData[$k] == $newData[$k])
					continue;

				$h = array();
				$h['registration_id'] = $newData['registration_id'];
				$h['tournament_id'] = $tid;
				$h['user_id'] = $uid;
				$h['field_name'] = $k;
				$h['old_value'] = $oldData[$k];
				$h['new_value'] = $newData[$k];
				$h['created'] = $newData['modified'];

				$histories[] = $h;

				if ($k == 'double_partner_id') {
					if (!empty($oldData['double_partner_id'])) {
						$h['registration_id'] = $oldData['double_partner_id'];
						$h['field_name'] = 'double_partner_withdrawn';
						$h['old_value'] = $oldData['registration_id'];
						$h['new_value'] = null;
						$h['created'] = $newData['modified'];

						$histories[] = $h;
					}

					if (!empty($newData['double_partner_id'])) {
						$h['registration_id'] = $newData['double_partner_id'];
						$currentPartner = $this->fieldByConditions('double_partner_id', array('registration_id' => $h['registration_id']));
						if ($currentPartner == $newData['registration_id'])
							$h['field_name'] = 'double_partner_confirmed';
						else
							$h['field_name'] = 'double_partner_wanted';
						$h['old_value'] = null;
						$h['new_value'] = $newData['registration_id'];
						$h['created'] = $newData['modified'];

						$histories[] = $h;
					}
				}

				if ($k == 'mixed_partner_id') {
					if (!empty($oldData['mixed_partner_id'])) {
						$h['registration_id'] = $oldData['mixed_partner_id'];
						$h['field_name'] = 'mixed_partner_withdrawn';
						$h['old_value'] = $oldData['registration_id'];
						$h['new_value'] = null;
						$h['created'] = $newData['modified'];

						$histories[] = $h;
					}

					if (!empty($newData['mixed_partner_id'])) {
						$h['registration_id'] = $newData['mixed_partner_id'];
						$currentPartner = $this->fieldByConditions('mixed_partner_id', array('registration_id' => $h['registration_id']));
						if ($currentPartner == $newData['registration_id'])
							$h['field_name'] = 'mixed_partner_confirmed';
						else
							$h['field_name'] = 'mixed_partner_wanted';
						$h['old_value'] = null;
						$h['new_value'] = $newData['registration_id'];
						$h['created'] = $newData['modified'];

						$histories[] = $h;
					}
				}

				if ($k == 'double_cancelled') {
					if ( empty($newData['double_cancelled']) && 
					     !empty($oldData['double_cancelled']) &&
					     !empty($newData['double_partner_id']) ) {

						$h['registration_id'] = $newData['double_partner_id'];
						$currentPartner = $this->fieldByConditions('double_partner_id', array('registration_id' => $h['registration_id']));
						if ($currentPartner == $newData['registration_id'])
							$h['field_name'] = 'double_partner_confirmed';
						else
							$h['field_name'] = 'double_partner_wanted';
						$h['old_value'] = null;
						$h['new_value'] = $newData['registration_id'];
						$h['created'] = $newData['modified'];

						$histories[] = $h;

					}	

					if ( !empty($newData['double_cancelled']) && 
					     empty($oldData['double_cancelled']) &&
					     !empty($oldData['double_partner_id']) ) {

						$h['registration_id'] = $oldData['double_partner_id'];
						$h['field_name'] = 'double_partner_withdrawn';
						$h['old_value'] = $oldData['registration_id'];
						$h['new_value'] = null;
						$h['created'] = $newData['modified'];

						$histories[] = $h;
					}	
				}

				if ($k == 'mixed_cancelled') {
					if ( empty($newData['mixed_cancelled']) && 
					     !empty($oldData['mixed_cancelled']) &&
					     !empty($newData['mixed_partner_id']) ) {

						$h['registration_id'] = $newData['mixed_partner_id'];
						$currentPartner = $this->fieldByConditions('mixed_partner_id', array('registration_id' => $h['registration_id']));
						if ($currentPartner == $newData['registration_id'])
							$h['field_name'] = 'mixed_partner_confirmed';
						else
							$h['field_name'] = 'mixed_partner_wanted';
						$h['old_value'] = null;
						$h['new_value'] = $newData['registration_id'];
						$h['created'] = $newData['modified'];

						$histories[] = $h;

					}	

					if ( !empty($newData['mixed_cancelled']) && 
					     empty($oldData['mixed_cancelled']) &&
					     !empty($oldData['mixed_partner_id']) ) {

						$h['registration_id'] = $oldData['mixed_partner_id'];
						$h['field_name'] = 'mixed_partner_withdrawn';
						$h['old_value'] = $oldData['registration_id'];
						$h['new_value'] = null;
						$h['created'] = $newData['modified'];

						$histories[] = $h;
					}	
				}
			}
		}

		if (!empty($histories)) {
			$participantHistory = TableRegistry::get('ParticipantHistories');
			$participantHistory->saveMany($participantHistory->newEntities($histories));
		}

		$this->oldData = false;
	}
}
