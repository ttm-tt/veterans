<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Controller\Component;

use App\Model\Table\GroupsTable;
use App\Model\Table\RegistrationsTable;
use App\Model\Table\TypesTable;
use App\Model\Table\UsersTable;

use Cake\Controller\Component;
use Cake\I18n\I18n;
use Cake\Mailer\Email;
use Cake\Utility\Inflector;


class RegistrationUpdateComponent extends Component {
	public $components = ['MultipleFlash'];
	
	// Creates (or updates) a registration including the person
	// The user must be known, will not be created yet.
	function addParticipant($registration, $events = array('S', 'D', 'X', 'T'), $options = array()) {
		$this->getController()->loadModel('Users');
		$this->getController()->loadModel('People');
		$this->getController()->loadModel('Registrations');
		$this->getController()->loadModel('Participants');
		
		if (is_array($registration))
			return $this->addParticipant($this->getController()->Registrations->newEntity($registration));

		$tid = $this->getController()->getRequest()->getSession()->read('Tournaments.id');
		
		if (in_array($registration['type_id'], array(
				TypesTable::getPlayerId(),
				TypesTable::getAccId()
			))
		) {
			if (!empty($registration['person']['user_id']))
				$uid = $registration['person']['user_id'];
			else if (!empty($registration['person']['username']))
				$uid = $this->getController()->Users->fieldByConditions('id', array('username' => $registration['person']['username']));
			else
				$uid = false;

			if (empty($uid)) {
				$this->MultipleFlash->setFlash(__d('user', 'Unknown user {0}', $registration['person']['username']), 'error');
				$this->getController()->log('No user id ' . $registration['person']['username'], 'error');
				return false;
			}
			
			$user = $this->getController()->Users->find('all', array(
				'recursive' => 0,
				'conditions' => array('Users.id' => $uid)
			))->first();
			
			$prefix = $user['prefix_people'];
			
			// Save guard: we need a prefix. If the user doesn't have one it must be a special user (e.g. admin)
			if ($prefix === null)
				$prefix = 10001;
		} else {
			$uid = null;
			$prefix = 10000;
		}
		

		unset($registration['person']['username']);
		$registration['person']['user_id'] = $uid;	

		$registration['tournament_id'] = $tid;
		
		// Calculate extern_id, but only if it is a new person
		if (empty($registration['person']['id'])) {
			if ($registration['type_id'] == TypesTable::getPlayerId()) 
				$prefix .= '-P';
			else if ($registration['type_id'] == TypesTable::getAccId())
				$prefix .= '-A';
			else
				$prefix .= '-X';

			$max = $this->getController()->People->find('all', array(
				'fields' => array('max' => 'MAX(CAST(SUBSTR(People.extern_id, ' . (strlen($prefix) + 1) . ') AS SIGNED))'),
				'conditions' => array(
					'People.user_id' => $uid,
					'People.extern_id LIKE' => $prefix . '%'
				)
			))->first()->get('max');

			$idx = empty($max) ? 0 : intval($max);

			$registration['person']['extern_id'] = $prefix . sprintf('%03d', ++$idx);
		}

		// Set the events
		if ($registration['type_id'] == TypesTable::getPlayerId()) {
			$registration['participant'] = $this->getController()->Participants->newEmptyEntity();

			$registration['participant']['single_id'] = in_array('S', $events) ? $this->_selectEvent($registration, 'S', $tid) : null;
			$registration['participant']['double_id'] = in_array('D', $events) ? $this->_selectEvent($registration, 'D', $tid) : null;
			$registration['participant']['mixed_id'] = in_array('X', $events) ? $this->_selectEvent($registration, 'X', $tid) : null;
			$registration['participant']['team_id'] = in_array('T', $events) ? $this->_selectEvent($registration, 'T', $tid) : null;			
		}

		// If Person.id is  set check for an old registration
		if (!empty($registration['person']['id'])) {
			$oldRegistration = $this->getController()->Registrations->find('all', array(
				'contain' => array('People', 'Participants'),
				'conditions' => array(
					'tournament_id' => $tid,
					'person_id' => $registration['person']['id']
				)
			))->first();
			
			if (!empty($oldRegistration)) {
				if ($oldRegistration['type_id'] != $registration['type_id']) {
					// Cannot change type of registration
					$this->MultipleFlash->setFlash(__d('user', 'Cannot change type of registration'), 'error');
					return false;
				}
				
				$registration['id'] = $oldRegistration['id'];
				if (!empty($oldRegistration['participant']['id'])) {
					$registration['participant']['id'] = $oldRegistration['participant']['id'];
					$registration['participant']['registration_id'] = $oldRegistration['participant']['registration_id'];
				}
				
				$registration['cancelled'] = null;
				
				if ($registration['type_id'] == TypesTable::getPlayerId()) {
					$registration['participant']['cancelled'] = 0;
					$registration['participant']['single_cancelled'] = 0;
					$registration['participant']['double_cancelled'] = 0;
					$registration['participant']['mexed_cancelled'] = 0;
					$registration['participant']['team_cancelled'] = 0;

					$registration['participant']['double_partner_id'] = null;
					$registration['participant']['mixed_partner_id'] = null;
				}
			}
		}
		
		$ct = date('Y-m-d H:i:s');
		if (empty($options['modified']))
			$options['modified'] = $ct;
		
		if ($registration['type_id'] != TypesTable::getPlayerId())
			unset($registration['participant']);
		
		if (!$this->getController()->Registrations->save($registration, $options)) {
			$this->MultipleFlash->setFlash(__d('user', 'The new registration could not be created'), 'error');
			
			$this->getController()->log('Cannot create or update registration');
			$this->getController()->log(print_r($registration->errors(), true));
			$this->getController()->log(print_r($registration, true));
			return false;
		}
		
		return $registration->id;
	}
	
	
	function _selectEvent($person, $type, $tid = null) {
		if ($tid === null)
			$tid = $this->getController()->getRequest()->getSession()->read('Tournaments.id');
		
		if (empty($tid))
			return null;
		
		$this->getController()->loadModel('Competitions');
		return $this->getController()->Competitions->findEventForPerson($person, $type, $tid);
	}

	// Iterate over the registrations and change obsoleted / new doubles partners as well (if so allowed)
	// $data: collects the changes and serves as a "have been here" flag
	// $field: either 'mixed' or 'double' to distuingish between doubles and mixed
	// $newRegistration, $oldRegistration: Registration after / before change of doubles partner
	function _collect(&$data, $field, $newRegistration, $oldRegistration) {
		$event = Inflector::pluralize(Inflector::camelize($field));
		$event_id = $field . '_id';
		$partner = ucwords($field) . 'Partners';
		$partner_id = $field . '_partner_id';
		$partnerProp = $field . '_partner';
		$cancelled = $field . '_cancelled';
		$funConfirmed = 'is' . ucwords($field) . 'PartnerConfirmed';

		// Reset fields if double / mixed was cancelled, so we can compare $partner_id to check for changes
		// We can do this because we are working on a copy
		if ($oldRegistration && !empty($oldRegistration['participant'][$cancelled])) 
			unset($oldRegistration['participant'][$partner_id]);

		if ($newRegistration && !empty($newRegistration['participant'][$cancelled]))
			unset($newRegistration['participant'][$partner_id]);

		// Old registration: Remove doubles / mixed partner
		if ($oldRegistration != null && !empty($oldRegistration['participant'][$partner_id]) &&
		    ($newRegistration == null || empty($newRegistration['participant'][$partner_id]) || 
		     $newRegistration['participant'][$partner_id] != $oldRegistration['participant'][$partner_id]) ) {

			$oldPartner = $this->getController()->Registrations->find('all', array(
				'conditions' => array(
					'Registrations.id' => $oldRegistration['participant'][$partner_id]
				),
				// 'fields' => array('id', 'person_id', 'tournament_id', 'type_id'),
				'contain' => array(
					'Types',
					'People' => array('Nations'),
					'Tournaments',
					'Participants' => array(
						Inflector::pluralize(ucwords($field)),
						$partner => array('Participants', 'People'),
					)
				)
			))->first();

			// The partner information of $oldPartner is the updated one, so copy it from $oldRegistration
			// XXX: Sind die Felder richtig ('Registration' vs. 'Participants')?
			$oldPartner->participant->{$partnerProp} = $oldRegistration;

			// Check if the change is already included. No need to do it twice
			if (!isset($data[$oldPartner['id']]['participant'][$partner_id])) {
				// Notify our old partner that we are no longer partnering with him
				$this->_sendMail('partner_removed_partner', 'Partner withdrawn', $field, $oldPartner, $oldRegistration);

				// Remove us from our old partner to make him "partner wanted" again
				// If partner is affected at all (this was his partner)
				if ($oldPartner['participant'][$partner_id] == $oldRegistration['id']) {
					// Save 'id' and partner field
					$data[$oldPartner['id']]['participant']['id'] = $oldPartner['participant']['id'];
					$data[$oldPartner['id']]['participant'][$partner_id] = null;

					// Reset double / mixed event
					$this->getController()->loadModel('Competitions');
					$order = null;
					$conditions = array('Competitions.tournament_id' => $this->getController()->getRequest()->getSession()->read('Tournaments.id'));

					if ($oldPartner['person']['born'] < date('Y') - 30) {
						// Veterans
						$conditions['Competitions.born >='] = $oldPartner['person']['born'];
						$order = ['Competitions.born' => 'ASC'];
					} else if ($oldPartner['person']['born'] > date('Y') - 30) {
						// Youth
						$conditions['Competitions.born <='] = $oldPartner['person']['born'];
						$order = ['Competitions.born' => 'DESC'];
					}

					if ($field == 'double') {
						$conditions['Competitions.sex'] = $oldPartner['person']['sex'];
						$conditions['Competitions.type_of'] = 'D';
					} else if ($field == 'mixed') {
						$conditions['Competitions.sex'] = 'X';
						$conditions['Competitions.type_of'] = 'X';
					}

					$data[$oldPartner['id']]['participant'][$event_id] = $this->getController()->Competitions->fieldByConditions('id', $conditions, ['order' => $order]);
				}
				// No need to recurse further, there is no new partner
			}
		}

		// New Registration: Add doubles / mixed partner if they have changed
		if ($newRegistration != null && !empty($newRegistration['participant'][$partner_id]) &&
		    ($oldRegistration == null || empty($oldRegistration['participant'][$partner_id]) || 
		     $oldRegistration['participant'][$partner_id] != $newRegistration['participant'][$partner_id])) {

			$oldPartner = $this->getController()->Registrations->find('all', array(
				'conditions' => array('Registrations.id' => $newRegistration['participant'][$partner_id]),
				// 'fields' => array('id', 'person_id', 'tournament_id', 'type_id'),
				'contain' => array(
					'Types',
					'People' => array('Nations'),
					'Tournaments',
					'Participants' => array(
						$event,
						$partner => array('Participants', 'People'),
					)
				)
			))->first();

			// The partner information of $oldPartner is the updated one, so copy it from $oldRegistration
			if ($oldRegistration != null) {
				$oldPartner->participant->{$partnerProp} = $oldRegistration;
			} else {
				$oldPartner->participant->{$partnerProp} = null;
			}

			// Check if change is already included. No need to do it twice.
			if (!isset($data[$oldPartner['id']]['participant'][$partner_id])) {

				// If we are are allowed to edit this player
				if (!$this->fromImport && $this->_isEditAllowed($oldPartner)) {
					$data[$oldPartner['id']]['participant']['id'] = $oldPartner['participant']['id'];
					$data[$oldPartner['id']]['participant'][$partner_id] = $newRegistration['id'];
					$data[$oldPartner['id']]['participant'][$event_id] = $newRegistration['participant'][$event_id];

					$newPartner = $oldPartner;

					$newPartner['participant'][$partner_id] = $newRegistration['id'];
					$newPartner['participant'][$event_id] = $newRegistration['participant'][$event_id];
					$newPartner['participant'][$field] = $newRegistration['participant'][$event];

					if ($this->getController()->Registrations->$funConfirmed($newRegistration) || $this->_isEditAllowed($newPartner))
						$this->_sendMail('partner_confirmed_partner', 'Partner Confirmed', $field, $newPartner, $newRegistration);
					else
						$this->_sendMail('partner_requested_partner', 'Partner Requested', $field, $newPartner, $newRegistration);

					// Recurse to add changes there, too
					$this->_collect($data, $field, $newPartner, $oldPartner);
				} else {
					// Notify our new partner (wanted) that we want to play with him
					if ($this->getController()->Registrations->$funConfirmed($newRegistration))
						$this->_sendMail('partner_confirmed_partner', 'Partner Confirmed', $field, $oldPartner, $newRegistration);
					else
						$this->_sendMail('partner_requested_partner', 'Partner Requested', $field, $oldPartner, $newRegistration);
				}
			}
		}
		
		// If we want a partner now or we are cancelled, notify all who wanted us as a partner
		if ( $newRegistration != null && 
				(
					!empty($newRegistration['participant'][$partner_id]) || 
					!empty($newRegistration['participant'][$cancelled]) 
				) 
			) {			
			$oldWantedBy = $this->getController()->Registrations->find('all', array(
				'conditions' => array(
					'Participants.' . $partner_id => $newRegistration['id'],
					'Participants.' . $cancelled => 0
				),
				// 'fields' => array('id', 'person_id', 'tournament_id', 'type_id'),
				'contain' => array(
					'Types',
					'People' => array('Nations'),
					'Tournaments',
					'Participants' => array(
						$event,
						$partner => array(
							'Participants', 
							'People'
						),
					)
				)
			));
			
			foreach ($oldWantedBy as $oldPartner) {
				// We have selected a partner, which means we are not cancelled
				// If we find him here it means we have confirmed him
				if (!empty($newRegistration['participant'][$partner_id])) {
					if ($oldPartner['id'] == $newRegistration['participant'][$partner_id])
						continue;
				}
				
				// else: empty partner_id means we are cancelled and we have to include him
					
				if (isset($data[$oldPartner['id']]['participant'][$partner_id]))
					continue;
				
				$data[$oldPartner['id']]['participant']['id'] = $oldPartner['participant']['id'];
				$data[$oldPartner['id']]['participant'][$partner_id] = null;

				$this->_sendMail('partner_removed_partner', 'Partner Rejected', $field, $oldPartner, $oldRegistration);
			}			
		}
	}

	function _differs($oldRegistration, $newRegistration) {
		if ($oldRegistration == null)
			return true;

		if ($oldRegistration['type_id'] != $newRegistration['type_id'])
			return true;

		if ($oldRegistration['type_id'] != TypesTable::getPlayerId())
			return false;

		// Only players beyond this point
		$op = $oldRegistration['participant'];
		$np = $newRegistration['participant'];

		if ($op['single_id'] != $np['single_id'])
			return true;
		if ($op['single_cancelled'] != $np['single_cancelled'])
			return true;
		if ($op['double_id'] != $np['double_id'])
			return true;
		if ($op['double_cancelled'] != $np['double_cancelled'])
			return true;
		if ($op['double_partner_id'] != $np['double_partner_id'])
			return true;
		if ($op['mixed_id'] != $np['mixed_id'])
			return true;
		if ($op['mixed_cancelled'] != $np['mixed_cancelled'])
			return true;
		if ($op['mixed_partner_id'] != $np['mixed_partner_id'])
			return true;
		if ($op['team_id'] != $np['team_id'])
			return true;
		if ($op['team_cancelled'] != $np['team_cancelled'])
			return true;

		if ($oldRegistration['cancelled'] != $newRegistration['cancelled'])
			return true;

		return false;
	}

	function _isEditAllowed($data) {
		if (empty($data))
			return false;

		if (is_numeric($data)) {
			$partner = $this->getController()->Registrations->find('all', array(
				'contain' => 'People',
				'conditions' => array('Registrations.id' => $data)
			))->first();

			return $this->_isEditAllowed($partner);
		}

		$this->getController()->loadModel('Users');
		$this->getController()->loadModel('Groups');
		$this->getController()->loadModel('People');
		$this->getController()->loadModel('Tournaments');
		
		$current_user = $this->getController()->_getCurrentUser();
		
		if (UsersTable::hasRootPrivileges($current_user))
			return true;

		// Check type_id, if there is a restriction
		$type_ids = $this->getController()->Groups->fieldByConditions('type_ids', array('id' => $current_user['group_id']));
		$nation_id = $current_user['nation_id'];

		// type_ids is a "," seperated list
		if (!empty($type_ids))
			$type_ids = explode(',', $type_ids);

		if (!empty($type_ids) && !in_array($data['type_id'], $type_ids))
			return false;

		// Check nation_id
		$person_id = $data['person_id'];
		
		if (!empty($nation_id) && $this->getController()->People->fieldByConditions('nation_id', array('id' => $person_id)) != $nation_id)
			return false;

		// Check user_id
		$user_id = $this->getController()->People->fieldByConditions('user_id', array('id' => $person_id));
		if (!empty($user_id) && $user_id != $current_user['id'])
			return false;

		// Participants may edit only their people
		if (empty($user_id) && $current_user['group_id'] == GroupsTable::getParticipantId())
			return false;
		
		// The same for tour operators
		if (empty($user_id) && $current_user['group_id'] == GroupsTable::getTourOperatorId())
			return false;

		// Check modifcation deadline
		$tournament_id = $data['tournament_id'];
		if ($this->getController()->Tournaments->fieldByConditions('modify_before', array('id' => $tournament_id)) < date('Y-m-d'))
			return false;

		// We don't have to check Tournament.modify_after, because if the person
		// is in, it could only be entered after start of registrations.

		return true;
	}

	function _isDeleteAllowed(&$data) {
		if (!$this->_isEditAllowed($data))
			return false;

		return true;
	}

	// Save a record. I have to change several records but I need access to user info.
	// So I can't use "beforeSave" / "afterSave" of the model.
	function _save(&$data, $options = array()) {
		// Security check: May the current user edit this person?
		if (!$this->_isEditAllowed($data)) {
			$this->MultipleFlash->setFlash(__('You are not allowed to edit this registration'), 'error');
			return $this->getController()->redirect(array('action' => 'index'));
		}
		
		$ct = date('Y-m-d H:i:s');
		
		if (!isset($options['modified']))
			$options['modified'] = $ct;

		$type_id = $data['type_id'];

		// If this is not a player, do it the simple way
		if ($type_id != TypesTable::getPlayerId()) {
			// Remove unnecessary data
			unset($data['participant']);
			unset($data['person']);
			unset($data['type']);
			unset($data['tournament']);

			$oldRegistration = null;

			if (!empty($data['id']))
				$oldRegistration = $this->getController()->Registrations->find('all', array(
					'conditions' => array('Registrations.id' => $data['id'])
				))->first();

			if (empty($data['id']))
				$registration = $this->getController()->Registrations->newEmptyEntity();
			else
				$registration = $this->getController()->Registrations->get($data['id']);

			$registration = $this->getController()->Registrations->patchEntity($registration, $data);
			if (!$this->getController()->Registrations->save($registration, $options))
				return false;

			// Reload registration in case it was a new one
			$newRegistration = $this->getController()->Registrations->get($registration->id, array(
				'contain' => array(
					'Types',
					'People' => array('Nations'),
					'Tournaments',
				)
			));

			// success
			return true;
		}

		// Load required models
		$this->getController()->loadModel('Participants');

		if (UsersTable::hasRootPrivileges($this->getController()->_getCurrentUser())) {
			// Clear xxx_cancelled flag if a root user has selected an event
			if (!empty($data['participant']['single_id']))
				$data['participant']['single_cancelled'] = false;
			if (!empty($data['participant']['double_id']))
				$data['participant']['double_cancelled'] = false;
			if (!empty($data['participant']['mixed_id']))
				$data['participant']['mixed_cancelled'] = false;
			if (!empty($data['participant']['team_id']))
				$data['participant']['team_cancelled'] = false;


			// Clear cancelled-flag if a root user has selected any event
			if (!empty($data['participant']['single_id']) ||
			    !empty($data['participant']['double_id']) ||
			    !empty($data['participant']['mixed_id']) ||
			    !empty($data['participant']['team_id'])) {

				$data['cancelled'] = null;
				$data['cancelled'] = false;
				$data['replaced_by_id'] = null;
			}
		} 

		if (!empty($data['cancelled'])) {
			// Attempt to delete after first deadline: mark as cancelled

			if (!empty($data['participant']['single_id']))
				$data['participant']['single_cancelled'] = true;
			if (!empty($data['participant']['double_id']))
				$data['participant']['double_cancelled'] = true;
			if (!empty($data['participant']['mixed_id']))
				$data['participant']['mixed_cancelled'] = true;
			if (!empty($data['participant']['team_id']))
				$data['participant']['team_cancelled'] = true;

			// And mark all as cancelled
			$data['participant']['cancelled'] = true;
		} 

		// Don't store the ids if participation was cancelled.
		// We need to mark / remember the original event.
		if (!empty($data['participant']['single_cancelled'])) {
			unset($data['participant']['single_id']);
		}

		if (!empty($data['participant']['double_cancelled'])) {
			unset($data['participant']['double_id']);
			unset($data['participant']['double_partner_id']);
		}

		if (!empty($data['participant']['mixed_cancelled'])) {
			unset($data['participant']['mixed_id']);
			unset($data['participant']['mixed_partner_id']);
		}

		if (!empty($data['participant']['team_cancelled'])) {
			unset($data['participant']['team_id']);
		}

		$newRegistration = $data;
		$oldRegistration = null;

		if (!empty($data['id'])) {
			$oldRegistration = $this->getController()->Registrations->get($data['id'], array(
				'conditions' => array('Registrations.id' => $newRegistration['id']),
				// 'fields' => array('id', 'person_id', 'tournament_id', 'type_id'),
				'contain' => array(
					'Types',
					'People' => array('Nations'),
					'Tournaments',
					'Participants' => array(
						'Singles',
						'Doubles',
						'DoublePartners' => array('Participants', 'People'),
						'Mixed',
						'MixedPartners' => array('Participants', 'People'),
						'Teams'
					)
				)
			));
		}

		$this->getController()->loadModel('Tournaments');
		$tournament = $this->getController()->Tournaments->get($data['tournament_id']);
		$tid = $tournament['id'];

		// If a player was removed from an event after the deadline just mark him as team_cancelled. 
		if ($tournament['enter_before'] < date('Y-m-d')) {
			if (!empty($oldRegistration['participant']['single_id']) && empty($newRegistration['participant']['single_id'])) {
				$newRegistration['participant']['single_cancelled'] = true;
				unset($newRegistration['participant']['single_id']);
			}

			if (!empty($oldRegistration['participant']['double_id']) && empty($newRegistration['participant']['double_id'])) {
				$newRegistration['participant']['double_cancelled'] = true;
				unset($newRegistration['participant']['double_id']);
				unset($newRegistration['participant']['double_partner_id']);
			}

			if (!empty($oldRegistration['participant']['mixed_id']) && empty($newRegistration['participant']['mixed_id'])) {
				$newRegistration['participant']['mixed_cancelled'] = true;
				unset($newRegistration['participant']['mixed_id']);
				unset($newRegistration['participant']['mixed_partner_id']);
			}

			if (!empty($oldRegistration['participant']['team_id']) && empty($newRegistration['participant']['team_id'])) {
				$newRegistration['participant']['team_cancelled'] = true;
				unset($newRegistration['participant']['team_id']);
			}
		}

		// In veterans the player always defaults to his age class
		$this->getController()->loadModel('Competitions');
		$this->getController()->loadModel('People');
		
		$born = $this->getController()->People->fieldByConditions('born', array('id' => $newRegistration['person_id']));
		$conditions = array('Competitions.tournament_id' => $tid);
		$order = [];

		$bornCondition = false;

		if ($born < date('Y') - 30) {
			$bornCondition = 'Competitions.born >=';
			$order = ['Competitions.born' => 'ASC'];
		} else if ($born > date('Y') - 30) {
			$bornCondition = 'Competitions.born <=';
			$order = ['Competitions.born' => 'DESC'];
		}

		if (!empty($newRegistration['participant']['single_id']))
			$newRegistration['participant']['single_id'] = $this->_selectEvent($newRegistration, 'S', $tid);

		// If the player doesn't have a double / mixed partner, put him in his own competition
		// But if the player has a partner, choose the event carfully ...
		// XXX: double_id, mixed_id could be empty strings. Why does that bother us as admin but not as "participant"?
		if (isset($newRegistration['participant']['double_id']) && !empty($newRegistration['participant']['double_id'])) {
			if (!empty($newRegistration['participant']['double_partner_id'])) {
				$dpid = $this->getController()->Registrations->fieldByConditions('person_id', array('id' => $newRegistration['participant']['double_partner_id']));

				if ($born < date('Y') - 30)
					$born = max($born, $this->getController()->People->fieldByConditions('born', array('id' => $dpid)));
				else if ($born > date('Y') - 30)
					$born = min($born, $this->getController()->People->fieldByConditions('born', array('id' => $dpid)));
			}

			if (!empty($bornCondition))
				$conditions[$bornCondition] = $born;

			$conditions['sex'] = $newRegistration['person']['sex'];
			$conditions['Competitions.type_of'] = 'D';
			$newRegistration['participant']['double_id'] = 
					$this->getController()->Competitions->fieldByConditions('id',$conditions, ['order' => $order]				
			);
		}

		if (isset($newRegistration['participant']['mixed_id']) && !empty($newRegistration['participant']['mixed_id'])) {
			if (!empty($newRegistration['participant']['mixed_partner_id'])) {
				$mpid = $this->getController()->Registrations->fieldByConditions('person_id', array('id' => $newRegistration['participant']['mixed_partner_id']));

				if ($born < date('Y') - 30)
					$born = max($born, $this->getController()->People->fieldByConditions('born', array('id' => $mpid)));
				else if ($born > date('Y') - 30)
					$born = min($born, $this->getController()->People->fieldByConditions('born', array('id' => $mpid)));
			}

			if (!empty($bornCondition))
				$conditions[$bornCondition] = $born;

			$conditions['sex'] = 'X';
			$conditions['Competitions.type_of'] = 'X';
			$newRegistration['participant']['mixed_id'] = 
					$this->getController()->Competitions->fieldByConditions('id', $conditions, ['order' => $order]
			);
		}

		// We don't want to save the People record. It is included for the players name only.
		// The same is true for the Type record, it is included for the description only.
		unset($newRegistration['person']);
		unset($newRegistration['type']);
		unset($newRegistration['tournament']);

		if (empty($newRegistration['id']))
			$registration = $this->getController()->Registrations->newEmptyEntity();
		else
			$registration = $this->getController()->Registrations->get($newRegistration['id'], [
				'contain' => ['Participants']
			]);
		
		$registration = $this->getController()->Registrations->patchEntity($registration, $newRegistration);
		
		if (!$this->getController()->Registrations->save($registration, $options))
			return false;

		$newRegistration = $this->getController()->Registrations->get($registration->id, array(
			'contain' => array(
				'Types',
				'People' => array('Nations'),
				'Tournaments',
				'Participants' => array(
					'Singles',
					'Doubles',
					'DoublePartners' => array('Participants', 'People'),
					'Mixed',
					'MixedPartners' => array('Participants', 'People'),
					'Teams'
				)
			)
		));

		// Collect affected records for doubles
		if ($oldRegistration != null && $oldRegistration['participant']['double_partner_id'] == $newRegistration['participant']['double_partner_id'])
			; // no change
		else if (empty($newRegistration['participant']['double_partner_id']))
			; // partner removed
		else if (RegistrationsTable::isDoublePartnerConfirmed($newRegistration) || $this->_isEditAllowed($newRegistration['participant']['double_partner_id']))
			$this->_sendMail('partner_confirmed_player', 'Partner Confirmed', 'double', $newRegistration);
		else
			$this->_sendMail('partner_requested_player', 'Partner Requested', 'double', $newRegistration);	

		// Collect affected records for mixed
		if ($oldRegistration != null && $oldRegistration['participant']['mixed_partner_id'] == $newRegistration['participant']['mixed_partner_id'])
			; // no change
		else if (empty($newRegistration['participant']['mixed_partner_id']))
			; // partner removed
		else if (RegistrationsTable::isDoublePartnerConfirmed($newRegistration) || $this->_isEditAllowed($newRegistration['participant']['mixed_partner_id']))
			$this->_sendMail('partner_confirmed_player', 'Partner Confirmed', 'mixed', $newRegistration);
		else
			$this->_sendMail('partner_requested_player', 'Partner Requested', 'mixed', $newRegistration);	

		$id = $newRegistration['id'];
		$newData = array($id => $newRegistration);

		// Collect dependent records (doubles / mixed partners) 
		$this->_collect($newData, 'double', $newRegistration, $oldRegistration);
		$this->_collect($newData, 'mixed', $newRegistration, $oldRegistration);

		// This registration was already saved, remove it
		unset($newData[$id]);

		$registrations = array();
		
		// Remove obsolete 'Person' entry so that we don't store it
		foreach($newData as $k => $v) {
			// See above: Remove unecessary associated models
			unset($newData[$k]['person']);
			unset($newData[$k]['type']);
			unset($newData[$k]['tournament']);
			
			$registration = $this->getController()->Registrations->get($k, ['contain' => ['Participants']]);
			$registration = $this->getController()->Registrations->patchEntity($registration, $v);
			$registrations[] = $registration;
		}

		if (!empty($registrations)) {
			// We don't care if successful or not
			$this->getController()->Registrations->saveMany($registrations, $options);
		}

		return true;
	}

	// ----------------------------------------------------------------------
	function _delete($id, $options = array()) {
		$this->getController()->loadModel('Registrations');

		$ct = date('Y-m-d H:i:s');
		
		if (!isset($options['modified']))
			$options['modified'] = $ct;

		// see "_save"
		$oldRegistration = $this->getController()->Registrations->find('all', array(
			'conditions' => array('Registrations.id' => $id),
			// 'fields' => array('id', 'person_id', 'tournament_id', 'type_id'),
			'contain' => array(
				'Types',
				'People' => array('Nations'),
				'Tournaments',
				'Participants' => array(
					'Singles',
					'Doubles',
					'DoublePartners' => array('Participants', 'People'),
					'Mixed',
					'MixedPartners' => array('Participants', 'People'),
					'Teams'
				),
			)
		))->first()->toArray();

		// Security check: May this person be deleted by the current user?
		if (!$this->_isDeleteAllowed($oldRegistration)) {
			
			$this->MultipleFlash->setFlash(__('You are not allowed to delete this person'), 'error');
			return $this->getController()->redirect(array('action' => 'index'));
		}

		// If this is not a player do it the simple way
		if ($oldRegistration['type_id'] != TypesTable::getPlayerId()) {
			// Always keep the record, but mark it as cancelled
			if (empty($oldRegistration['cancelled']))
				$oldRegistration['cancelled'] = date('Y-m-d H:i:s');

			if (!$this->_save($oldRegistration, $options))
				return false;

			// Success
			return true;
		}

		// Players are always marked as cancelled instead of actually deleting them
		$oldRegistration['cancelled'] = date('Y-m-d H:i:s');

		if (!empty($oldRegistration['participant']['single_id']))
			$oldRegistration['participant']['single_cancelled'] = true;
		if (!empty($oldRegistration['participant']['double_id']))
			$oldRegistration['participant']['double_cancelled'] = true;
		if (!empty($oldRegistration['participant']['mixed_id']))
			$oldRegistration['participant']['mixed_cancelled'] = true;
		if (!empty($oldRegistration['participant']['team_id']))
			$oldRegistration['participant']['team_cancelled'] = true;

		// And mark all as cancelled
		$oldRegistration['participant']['cancelled'] = true;

		// Reset event ids so save does not think the player has reentered
		unset($oldRegistration['participant']['single_id']);
		unset($oldRegistration['participant']['double_id']);
		unset($oldRegistration['participant']['mixed_id']);
		unset($oldRegistration['participant']['team_id']);

		// Save as if it were posted by POST
		if (! $this->_save($oldRegistration, $options))
			return false;

		// Notify users
		$this->_sendMail('delete_registration', 'Registration Cancelled', null, $oldRegistration);

		return true;
	}

	// Send emails to the users responsible for $registration
	public function _sendMail($template, $subject, $field, &$registration, &$partner = null) {
		if ($this->fromImport)
			return;
	
		// I might need the those
		$this->getController()->loadModel('Nations');
		$nations = $this->getController()->Nations->find('list', array(
			'fields' => array('id', 'name')
		))->toArray();
		
		$this->getController()->loadModel('Tournaments');
		$tournament = $this->getController()->Tournaments->find('all', array(
			'conditions' => ['Tournaments.id' => $registration['tournament_id']],
			'contain' => ['Hosts', 'Organizers', 'Contractors']
		))->first();
		
		$this->getController()->loadModel('Users');
		$this->getController()->loadModel('Languages');
		$this->getController()->loadModel('Notifications');

		
		$languages = $this->getController()->Languages->find('list', array('fields' => array('id', 'name')))->toArray();
		$lang = 'en';
		
		if (!empty($registration['person']['user_id'])) {
			$lang_id = $this->getController()->Users->fieldByConditions('language_id', array('id' => $registration['person']['user_id']));
			if (!empty($languages[$lang_id]))
				$lang = $languages[$lang_id];
		}

		// Translate subject
		$oldLang = I18n::getLocale();
		I18n::setLocale($lang);
		
		$subjectTranslated = __d('user', $subject);
		
		if (!empty($oldLang))
			I18n::setLocale($oldLang);
		
		$notifications = array();
		$notifications['all_notifications'] = 1;
		$notifications[$template] = 1;
		if ($registration['type_id'] == TypesTable::getPlayerId())
			$notifications[$template . '_player'] = 1;
		if ($tournament['enter_before'] < date('Y-m-d')) {
			$notifications[$template . '_after'] = 1;

			if ($registration['type_id'] == TypesTable::getPlayerId())
				$notifications[$template . '_player_after'] = 1;
		}

		// Strip out all non-existing columns
		// columns() would return a numeric array with the column names,
		// which we cannot use in array_intersect_key. We could flip the array
		// (array_flip) to make an array column => idx. Or we use typeMap which
		// returns an array column => type
		$columns = $this->getController()->Notifications->getSchema()->typeMap();
		$tmp = array_intersect_key($notifications, $columns);

		// Normalize columns
		$notifications = array();
		foreach ($tmp as $key => $value) {
			$notifications['Notifications.' . $key] = $value;
		}

		if (!empty($tournament['contractor']['email']))
			$replyTo = $tournament['contractor']['email'];
		else if (!empty($tournament['host']['email']))
			$replyTo = $tournament['host']['email'];
		else
			$replyTo = $this->getController()->Users->fieldByConditions('email', array(
					'group_id' => GroupsTable::getOrganizerId(),
					'tournament_id' => $tournament['id'],
					'email IS NOT NULL'
			));

		$email = new Email('default');

		$email
			->setEmailFormat('both')
			->setSubject(
				'[' . $tournament['name'] . '] ' . 
				$registration['person']['display_name'] . ' (' . $nations[$registration['person']['nation_id']] . ')' . ': ' . 
				$subjectTranslated
			)
			->setReplyTo($replyTo)
			->addHeaders(array(
				'X-Tournament' => $tournament['name'],
				'X-Type' => 'Registration',
				'X-' . $tournament['name'] . '-Type' => 'Registration'
			))
			->viewBuilder()->setTemplate($lang . DS . $template)
		;

		// Not all user may view all people
		if ( !empty($registration['person']['user_id'])) {
			$addTo = $this->getController()->Users->fieldByConditions('email', array(
				'id' => $registration['person']['user_id'],
				'enabled' => 1
			));
			
			if (!empty($addTo) && filter_var($addTo, FILTER_VALIDATE_EMAIL))
				$email->addTo($addTo);

			$add = $this->getController()->Users->fieldByConditions('add_email', array(
				'id' => $registration['person']['user_id'],
				'enabled' => 1
			));

			if (!empty($add)) {
				foreach (explode("\n", $add) as $v) {
					if (filter_var($v, FILTER_VALIDATE_EMAIL))
						$email->addCc($v);
				}
			}
		} 

		if (count($email->getTo()) == 0 && count($email->getCc()) == 0)
			return;

		$tmp = $this->getController()->Users->fieldByConditions('email', array(
			'username' => 'admin',
			'enabled' => 1
		));
		if (filter_var($tmp, FILTER_VALIDATE_EMAIL))
			$email->addBcc($tmp);
		
		$add = array_values($this->getController()->Users->find('list', array(
				'fields' => array('id', 'email'),
				'contain' => ['Notifications'],
				'conditions' => array(
					'Users.email IS NOT NULL',
					'Users.group_id' => GroupsTable::getAdminId(),
					'Users.enabled' => 1,
					'OR' => $notifications
				)
		))->toArray());
		
		foreach ($add as $v) {
			if (filter_var($v, FILTER_VALIDATE_EMAIL))
				$email->addBcc($v);
		}

		$add = $this->getController()->Users->find('list', array(
			'fields' => array('id', 'add_email'),
			'contain' => ['Notifications'],
			'conditions' => array(
				'Users.add_email IS NOT NULL',
				'Users.group_id' => GroupsTable::getAdminId(),
				'Users.enabled' => 1,
				'OR' => $notifications
			)
		))->toArray();

		foreach($add as $v) {
			str_replace("\r", "", $v);
			
			foreach (explode("\n", $v) as $vv) {
				if (filter_var($vv, FILTER_VALIDATE_EMAIL))
					$email->addBcc($vv);
			}
		}

		$email->setViewVars(array(
			'hasRootPrivileges' => UsersTable::hasRootPrivileges($this->getController()->_getCurrentUser()),
			'registration' => $registration,
			'partner' => $partner,
			'nations' => $nations,
			'tournament' => $tournament,
			'field' => $field
		));

		if ( $this->getController()->Auth->user('username') != 'test' && 
		     $tournament['end_on'] >= date('Y-m-d') && 
		     $tournament['enter_after'] <= date('Y-m-d') ) {
			try {
				$email->send();
			} catch (SocketException $e) {
				$this->getController()->log(__('SocketException while sending email: {0}', $e->getMessage()), 'debug');
			}
		}
		
		// And the mails to the player
		if (empty($registration['person']['email']))
			return;
		
		// if the user is not the same as the player
		if (in_array($registration['person']['email'], $email->getTo()))
			return;
		
		$email = new Email('default');
		$email
			->setEmailFormat('both')
			->setSubject(
				'[' . $tournament['name'] . '] ' . 
				$registration['person']['display_name'] . ' (' . $nations[$registration['person']['nation_id']] . ')' . ': ' . 
				$subjectTranslated
			)
			->setReplyTo($replyTo)
			->viewBuilder()->setTemplate($lang . DS . 'players_' . $template)
		;
			
		if (filter_var($registration['person']['email'], FILTER_VALIDATE_EMAIL))
			$email->addTo($registration['person']['email']);
		
		$email->setViewVars(array(
			'hasRootPrivileges' => UsersTable::hasRootPrivileges($this->getController()->_getCurrentUser()),
			'registration' => $registration,
			'partner' => $partner,
			'nations' => $nations,
			'tournament' => $tournament,
			'field'=> $field
		));

		if ( $this->getController()->Auth->user('username') != 'test' && 
		     $tournament['end_on'] >= date('Y-m-d') && 
		     $tournament['enter_after'] <= date('Y-m-d') ) {
			try {
				if (count($email->getTo()) > 0)
					$email->send();
			} catch (SocketException $e) {
				$this->getController()->log(__('SocketException while sending email: {0}', $e->getMessage()), 'debug');
			}
		}
	}
}
