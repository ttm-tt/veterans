<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php

namespace App\Controller;

use App\Controller\AppController;
use App\Model\Table\TypesTable;
use App\Model\Table\GroupsTable;
use App\Model\Table\UsersTable;

use Cake\Core\Configure;
use Cake\Database\ConnectionManager;
use Cake\Utility\Hash;
use Cake\Event\EventInterface;


class RegistrationsController extends AppController {

	private $fromImport = false;  // set to true to not send mails
	
	function initialize() : void {
		parent::initialize();
		
		$this->loadComponent('WelcomeMail');
		$this->loadComponent('RegistrationUpdate');
		$this->loadComponent('Shop.OrderUpdate');
	}
	
	function beforeFilter(EventInterface $event) {
		$this->Auth->allow([
			// Allow it later: 'count'
		]);
		
		$this->Security->setConfig('unlockedActions', [
			'onChangePerson',
			'onChangeDouble',
			'onChangeMixed'
		]);

		parent::beforeFilter($event);
	}


	// This method adds a yet unknown person to the tournament, including the
	// people table
	public function add_participant() {
		if ($this->request->getData('cancel') !== null)
			return $this->redirect(array('action' => 'index'));

		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		}
		
		$this->loadModel('Users');
		$this->loadModel('Competitions');
		$tid = $this->request->getSession()->read('Tournaments.id');
			
		$registration = $this->Registrations->newEmptyEntity();
			
		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->getData();
			
			$registration = $this->Registrations->patchEntity($registration, $data);
			
			$registration['tournament_id'] = $tid;
			
			// data['Article'] is undefined if there are not articles
			$items = $data['Article'] ?? [];
			
			unset($registration['Article']);
			// foreach ($data['Article'])
			
			if ( empty($registration['person']['first_name']) || 
				 empty($registration['person']['last_name']) ||
				 empty($registration['person']['sex']) || 
				 empty($registration['person']['nation_id']) ||
				 empty($registration['type_id']) ) {
				$this->MultipleFlash->setFlash(__('You must fill out all required fields'), 'error');
				return $this->redirect(['action' => 'index']);
			}
				
			if ($registration['type_id'] == TypesTable::getPlayerId()) {
				if (empty($registration['person']['dob'])) {
					$this->MultipleFlash->setFlash(__('You must enter the date of birth for players'), 'error');
					return $this->redirect(['action' => 'index']);
				}
				
				$maxYear = $this->Competitions->fieldByConditions(
					'Competition.born', 
					array('tournament_id' => $tid), 
					['order' => ['born' => 'DESC']]
				);
		
				if ($maxYear > 0 && date('Y', strtotime($registration['person']['dob'])) > $maxYear) {
					$this->MultipleFlash->setFlash(sprintf(__('You must be born in %d or earlier'), $maxYear), 'error');
					return $this->redirect(['action' => 'index']);
				}
				
				if (date('Y') - date('Y', strtotime($registration['person']['dob'])) > 140) {
					$this->MultipleFlash->setFlash(__('Wrong birthday given'));
					return $this->redirect(['action' => 'index']);
				}
			}

			// Check para settings
			if (($registration['person']['is_para'] ?? 0) == 0) {
				$registrations['person']['ptt_class'] = 0;
				$registrations['person']['wchc'] = 0;
			}				
			
			// Start transaction. The model argument is a dummy, hopefully ...
			$db = $this->Registrations->getConnection();
			
			$db->begin();

			if (!$this->_saveParticipant($registration, $items)) {
				$db->rollback();
				// saveParticipant already wrote to flash
				// $this->MultipleFlash->setFlash(__('The registration could not be saved.'), 'error');
			} else if (!$db->commit()) {
				$this->MultipleFlash->setFlash(__('The registration could not be saved.'), 'error');
			} else {
				$this->MultipleFlash->setFlash(__('The registration has been saved'), 'success');
				if ( !array_key_exists('continue', $this->request->getData()) )
					return $this->redirect(array('action' => 'index'));
				else
					return $this->redirect(array('action' => 'add_participant'));
			}
		}

		$this->loadModel('Competitions');
		$havePara = $this->Competitions->find()
				->where([
					'tournament_id' => $tid,
					'ptt_class > 0'
				]) 
				->count()
			> 0;
		
		$this->set('havePara', $havePara);
		
		$this->loadModel('Nations');
		
		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'description'),
			'conditions' => ['enabled' => 1],
			'order' => array('description' => 'ASC')			
		))->toArray());
		
		$types = $this->_findTypes();
		
		if (!UsersTable::hasRootPrivileges($this->_user)) {
			$waiting = $this->OrderUpdate->calculateWaiting();
			if ($waiting['PLA'] && isset($types[TypesTable::getPlayerId()])) {
				$this->MultipleFlash->setFlash(__('Maximum number of players reached, so you cannot enter them at this time'), 'warning');
				unset($types[TypesTable::getPlayerId()]);
			}
			
			if ($waiting['ACC'] && isset($types[TypesTable::getAccId()])) {
				$this->MultipleFlash->setFlash(__('Maximum number of accompanying persons reached, so you cannot enter them at this time'), 'warning');
				unset($types[TypesTable::getAccId()]);				
			}
		}
				
		$this->set('registration', $registration);
		$this->set('types', $types);
		
		$this->OrderUpdate->setVarsForRegistration();
		
	}
	
	private function _saveParticipant($registration, $items) {
		$rid = $this->RegistrationUpdate->addParticipant($registration);
		if (empty($rid)) {
			return false;
		}
		
		// And now the order
		if (in_array($registration['type_id'], array(
				TypesTable::getPlayerId(),
				TypesTable::getAccId()
			))
		) {
			if (!$this->OrderUpdate->addParticipant($rid, $items)) {
				return false;
			}
		}

		return true;
	}

	public function import() {
		if ($this->request->getData('cancel') !== null)
			return $this->redirect(array('action' => 'index'));
		
		$data = $this->request->getData();

		if ($this->request->is(['post', 'put']) && is_uploaded_file($data['File']['tmp_name'])) {
			// Don't send mails
			$this->fromImport = true;

			$file = $this->_openFile($data['File']['tmp_name'], 'rt', 'CP437');

			$this->_doImport($file);

			rewind($file);

			$this->_doImportPartner($file);

			fclose($file);
		}
	}

	private function _doImport($file) {
		// Load models
		$this->loadModel('People');
		$this->loadModel('Nations');
		$this->loadModel('Registrations');
		$this->loadModel('Participants');
		$this->loadModel('Tournaments');
		$this->loadModel('Users');

		$nations = $this->Nations->find('list', array('fields' => array('name', 'id')))->toArray();
		
		// Skip first line
		fgets($file);

		$lastId = '';
		$uid = null;
		$tid = $this->request->getSession()->read('Tournaments.id');

		$newUsers = array();

		while (!feof($file)) {
			// Restart execution timer
			set_time_limit(60);

			// XXX utf8_encode
			$line = fgets($file);
			$line = str_replace("\n", "", $line);
			$line = str_replace("\t", ";", $line);

			if (strpos($line, '#', 0) === 0)
				continue;

			$this->log($line, 'debug');

			$fields = explode(";", $line);

			if (count($fields) < 11)
				continue;

			$id = trim($fields[0]);
			$sex = trim($fields[1]);
			$lastName = trim($fields[2]);
			$firstName = trim($fields[3]);
			$dob = trim($fields[4]);
			$nation = trim($fields[5]);
			$single = trim($fields[6]);
			$double = trim($fields[8]);
			$email = trim($fields[10]);

			if (strpos($dob, '.') !== false) {
				$tmp = explode('.', $dob);
				$dob = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
			}

			if (strncmp($lastId, $id, 6)) {
				if (empty($email))
					continue;

				if (strpos($email, '@') === false) 
					$email = $firstName . '.' . $lastName . '@localhost';

				if (!$this->User->fieldByConditions('id', array('username' => $email))) {
					// New user
					$this->User->create();
					$data = array(
						'username' => $email,
						'password' => '', // $this->Auth->password($user['username']),
						'email' => $email,
						'group_id' => GroupsTable::getParticipantId(),
						'enabled' => true,
						'tournament_id' => $tid
					);

					// Return error if we can't create the user
					if (!$this->Users->saveAll($data)) {
						$this->log('Could not save user ' . $email, 'error');
						continue;
					}

					$this->log('User ' . $email . ' added with id ' . $this->Users->id, 'debug');

					$newUsers[] = $this->Users->fieldByConditions('id', array('username' => $email));
				}

				$uid = $this->Users->fieldByConditions('id', array('username' => $email));
			}

			$lastId = $id;

			$data = array(
				'last_name' => mb_strtoupper($lastName),
				'first_name' => mb_convert_case(mb_strtolower($firstName), MB_CASE_TITLE, "UTF-8"),
				'sex' => ($sex == 'M' ? 'M' : 'F'),
				'dob' => (empty($dob) ? null : $dob),
				'nation_id' => $nations[$nation],
				'user_id' => $uid
			);

			$data['extern_id'] = $id;
			
			// Start transaction. The model argument is a dummy, hopefully ...
			$db = ConnectionManager::get($this->People->useDbConfig);
			if (!$db->begin($this->People)) {
				$this->log('Could not start transaction to add ' . $lastName . ', ' . $firstName, 'error');

				continue;
			}

			// All save commands shall not end transactions
			$options = array('atomic' => false);

			$person = $this->People->find('all', array(
				'recursive' => -1,
				'conditions' => array(
					'first_name' => $data['first_name'],
					'last_name' => $data['last_name'],
					'OR' => array(
						'extern_id LIKE' => 'X-%',
						'extern_id' => $data['extern_id']
					)
				)
			))->first();

			if (!$person) {
				$this->People->create();
			} else {
				$data['id'] = $person['id'];
				$data['display_name'] = $person['display_name'];
			}

			if (!$this->People->saveAll($data, $options)) {
				$db->rollback($this->People);
				$this->log('Could not save person ' . $lastName . ', ' . $firstName, 'error');

				continue;
			}
		
			$pid = $this->People->fieldByConditions('id', array('extern_id' => $id));

			$person = $data;
			$person['single'] = (!empty($single) ? 'S' : false);
			$person['double'] = (!empty($double) ? 'D' : false);

			$data['registration'] = array(
					'person_id' => $pid,
					'type_id' => (strpos($id, '-A') === false ? TypesTable::getPlayerId() : TypesTable::getAccId()),
					'tournament_id' => $tid
			);

			if (strpos($id, '-A') === false) {
				$data['participant'] = array();

				if (!empty($person['single']))
					$data['participant']['single_id'] = $this->_selectEvent($person, 'S', $tid);
				else
					$data['participant']['single_id'] = null;

				if (!empty($person['double']))
					$data['participant']['double_id'] = $this->_selectEvent($person, 'D', $tid);
				else
					$data['participant']['double_id'] = null;

				if (!empty($person['mixed']))
					$data['participant']['mixed_id']  = $this->_selectEvent($person, 'X', $tid);
				else
					$data['participant']['mixed_id'] = null;

				if (!empty($person['team']))
					$data['participant']['team_id']   = $this->_selectEvent($person, 'T', $tid);
				else
					$data['participant']['team_id'] = null;
			}

			$registration = $this->Registrations->find('all', array(
				'contain' => 'Participants',
				'conditions' => array('person_id' => $pid)
			))->first();

			if (!$registration) {
				$this->Registrations->create();
			} else {
				$data['id'] = $registration['id'];
				if (!empty($data['participant']) && !empty($registration['participant'])) {
					$data['participant']['id'] = $registration['participant']['id'];
					$data['participant']['registration_id'] = $data['id'];
				}
			}
			
			if (!$this->RegistrationUpdate->_save($data, $options)) {
				$db->rollback($this->People);

				$this->log('Could not save participant ' . $lastName . ', ' . $firstName, 'error');
		
				continue;
			}

			if (!$db->commit($this->People)) {

				$this->log('Could not commit data for ' . $lastName . ', ' . $firstName, 'error');

				continue;
			}
		}

		if (!empty($newUsers))
			$this->WelcomeMail->sendWelcomeMail($newUsers);
	}

	function import_partner() {
		if ($this->request->getData('cancel') !== null)
			return $this->redirect(array('action' => 'index'));

		$data = $this->request->getData();
		
		if ($this->request->is(['post', 'put']) && is_uploaded_file($data['File']['tmp_name'])) {

			// Don't send mails
			$this->fromImport = true;

			$file = $this->_openFile($data['File']['tmp_name'], 'rt', 'CP437');

			$this->_doImportPartner($file);

			fclose($file);
		}
	 }

	function _doImportPartner($file) {
		// Load models
		$this->loadModel('People');
		$this->loadModel('Nations');
		$this->loadModel('Registrations');
		$this->loadModel('Participants');
		$this->loadModel('Tournaments');
		$this->loadModel('Users');

		// Skip first line
		fgets($file);

		$tid = $this->request->getSession()->read('Tournaments.id');

		while (!feof($file)) {
			// Restart execution timer
			set_time_limit(60);

			// XXX utf8_encode
			$line = fgets($file);
			$line = str_replace("\n", "", $line);
			$line = str_replace("\t", ";", $line);

			$fields = explode(";", $line);

			if (count($fields) < 18)
				continue;

			$id = trim($fields[0]);

			if (empty($id))
				continue;

			$this->log($line, 'debug');

			$doublePartner = trim($fields[17]);

			// Cut of trailing association, if present
			if (strpos($doublePartner, "(") > 0)
				$doublePartner = substr($doublePartner, 0, strpos($doublePartner, "(") - 1);

			$doublePartner = trim($doublePartner);

			if (empty($doublePartner))
				continue;

			$pid = $this->People->fieldByConditions('id', array('extern_id' => $id));

			if (empty($pid)) {
				$this->log($id . ' not found', 'debug');
				$this->log($id . ' not found', 'error');
				continue;
			}

			$registration = $this->Registrations->find('all', array(
				'contain' => array('Participants', 'People'),
				'conditions' => array(
					'Registration.tournament_id' => $tid,
					'Registration.person_id' => $pid
				)
			))->first();

			if (empty($registration['participant']['double_id']))
				continue;

			$conditions = array('Participant.double_id IS NOT NULL');

			if (is_numeric(substr($doublePartner, 0, 5))) {
				$partnerId = $this->People->fieldByConditions('id', array('Person.extern_id' => $doublePartner));

				if (empty($partnerId)) {
					$this->log($id . ': Partner ' . $doublePartner . ' not found', 'debug');
					$this->log($id . ': Partner ' . $doublePartner . ' not found', 'error');
					continue;
				}

				$conditions['Person.id'] = $partnerId;
			} else {
				$conditions['OR'] = array(
					'CONCAT(People.first_name, " ", People.last_name)' => $doublePartner,
					'CONCAT(People.last_name, " ", People.first_name)' => $doublePartner
				);
			}

			$partner = $this->Registrations->find('all', array(
				'contain' => array('People', 'Participants'),
				'conditions' => $conditions
			));

			if (empty($partner)) { 
				$this->log($id . ': Partner ' . $doublePartner . ' not found', 'debug');
				$this->log($id . ': Partner ' . $doublePartner . ' not found', 'error');
				continue;
			} else if (count($partner) != 1) {
				$this->log($id . ': Partner ' . $doublePartner . ' not unique', 'debug');
				$this->log($id . ': Partner ' . $doublePartner . ' not unique', 'error');
				continue;
			} else if (!empty($registration['participant']['double_partner_id']) &&
			           $registration['participant']['double_partner_id'] != $partner[0]['Registration']['id'] ) {
				$this->log($id . ' already has a partner', 'debug');
				$this->log($id . ' already has a partner', 'error');
				continue;
			} else if (!empty($partner['participant']['double_partner_id']) &&
			           $partner[0]['articipant']['double_partner_id'] != $registration['Registration']['id'] ) {
				$this->log($doublePartner . ' already has a partner', 'debug');
				$this->log($doublePartner . ' already has a partner', 'error');
				continue;
			} else {
				$this->log($id . ': Partner ' . $doublePartner . ' found', 'debug');
			}

			$data = $registration;

			$this->log('Partner ' . $fields[17] . ' found', 'debug');
			$data['participant']['double_partner_id'] = $partner[0]['Registration']['id'];

			if (!$this->RegistrationUpdate->_save($data)) {
				$this->log('Could not save participant ' . $registration['Person']['last_name'] . ', ' . $registration['Person']['first_name'], 'error');
			}
		}
	}

	// ----------------------------------------------------------------------
	function index() {
		if (!empty($this->request->getQuery('tournament_id'))) {
			$this->request->getSession()->write('Tournaments.id', $this->request->getQuery('tournament_id'));
		}
		
		// Silent cancellation
		if ($this->request->getQuery('silent')) {
			$this->request->getSession()->write('Registrations.silent', true);
		}
		
		if ($this->request->getSession()->check('Registrations.silent'))
			$this->fromImport = true;

		if ($this->request->getQuery('nation_id') !== null) {
			if ($this->request->getQuery('nation_id') === 'all')
				$this->request->getSession()->delete('Nations.id');
			else
				$this->request->getSession()->write('Nations.id', $this->request->getQuery('nation_id'));
		}

		if ($this->request->getQuery('type_id') !== null) {
			if ($this->request->getQuery('type_id') === 'all')
				$this->request->getSession()->delete('Types.id');
			else
				$this->request->getSession()->write('Types.id', $this->request->getQuery('type_id'));
		}

		if ($this->request->getQuery('competition_id') !== null) {
			if ($this->request->getQuery('competition_id') === 'all') 
				$this->request->getSession()->delete('Competitions.id');
			else
				$this->request->getSession()->write('Competitions.id', $this->request->getQuery('competition_id'));
		}

		if ($this->request->getQuery('para') !== null) {
			if ($this->request->getQuery('para') == 'all')
				$this->request->getSession()->delete('Participants.para');
			else
				$this->request->getSession()->write('Participants.para', $this->request->getQuery('para'));
		}

		if ($this->request->getQuery('age_category') !== null) {
			if ($this->request->getQuery('age_category') == 'all') 
				$this->request->getSession()->delete('Participants.age_category');
			else
				$this->request->getSession()->write('Participants.age_category', $this->request->getQuery('age_category'));
		}

		if ($this->request->getQuery('partner') !== null) {
			if ($this->request->getQuery('partner') == 'all')
				$this->request->getSession()->delete('Participants.partner');
			else
				$this->request->getSession()->write('Participants.partner', $this->request->getQuery('partner'));
		}
		
		if ($this->request->getQuery('cancelled') !== null) {
			if ($this->request->getQuery('cancelled') == 'all') 
				$this->request->getSession()->delete('Registrations.cancelled');
			else
				$this->request->getSession()->write('Registrations.cancelled', $this->request->getQuery('cancelled'));
		}

		if ($this->request->getQuery('last_name') !== null) {
			$last_name = urldecode($this->request->getQuery('last_name'));

			if ($last_name == '*')
				$this->request->getSession()->delete('People.last_name');
			else
				$this->request->getSession()->write('People.last_name', str_replace('_', ' ', $last_name));
		}

		if ($this->request->getQuery('user_id') !== null) {
			if ($this->request->getQuery('user_id') === 'all')
				$this->request->getSession()->delete('Users.id');
			else
				$this->request->getSession()->write('Users.id', $this->request->getQuery('user_id'));
		}

		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		}

		// The current tournament, must be set
		$tid = $this->request->getSession()->read('Tournaments.id');

		// XXX Siehe unten: Eigentlich auch fuer andere, die keine Personen anlegen duerfen
		if ( $this->Auth->user('group_id') == GroupsTable::getParticipantId() ) {
			if ($this->Registrations->find('all', array(
					'contain' => array('People'),
					'conditions' => array(
						'Registrations.tournament_id' => $tid,
						'People.user_id' => $this->Auth->user('id')
					)
				))->count() <= $this->Paginator->getConfig('limit')) {

				$this->request->getSession()->delete('Nations.id');
				$this->request->getSession()->delete('Competitions.id');
				$this->request->getSession()->delete('Types.id');
				$this->request->getSession()->delete('People.last_name');
			}
		}

		$conditions = array();

		// $tid (Tournament ID) cannot be empty here
		$conditions['Registrations.tournament_id'] = $this->request->getSession()->read('Tournaments.id');

		if ($this->request->getSession()->check('Users.id'))
			$conditions['People.user_id'] = $this->request->getSession()->read('Users.id');

		// XXX Eigentlich gilt das auch fuer andere. Aber nicht fuer Organizer, etc.
		if ( $this->Auth->user('group_id') == GroupsTable::getParticipantId() ||
			 $this->Auth->user('group_id') == GroupsTable::getTourOperatorId() ) {
/*
			$conditions[] = array('OR' => array(
				array('Person.user_id' => null),
				array('Person.user_id' => $this->Auth->user('id'))
			));
*/
			$conditions['People.user_id'] = $this->Auth->user('id');
		}

		if ($this->request->getSession()->check('Nations.id'))
			$conditions['People.nation_id'] = $this->request->getSession()->read('Nations.id');

		if ($this->request->getSession()->check('Groups.type_ids'))
			$conditions['Registrations.type_id IN'] = explode(',', $this->request->getSession()->read('Groups.type_ids'));

		if ($this->request->getSession()->check('Types.id')) 
			$conditions['Registrations.type_id'] = $this->request->getSession()->read('Types.id');
	
		if ($this->request->getSession()->check('Competitions.id')) {
			$cid = $this->request->getSession()->read('Competitions.id');

			$this->loadModel('Competitions');
			$type_of = $this->Competitions->fieldByConditions('type_of', array('id = ' => $cid));

			switch ($type_of) {
				case 'S' :
					$conditions['Participants.single_id'] = $cid;
					break;

				case 'D' :
					$conditions['Participants.double_id'] = $cid;
					break;

				case 'X' :
					$conditions['Participants.mixed_id'] = $cid;
					break;

				case 'T' :
					$conditions['Participants.team_id'] = $cid;
					break;
			}
		}

		if ($this->request->getSession()->check('Registrations.cancelled')) {
			if (empty($this->request->getSession()->read('Registrations.cancelled')))
				$conditions[] = 'Registrations.cancelled IS NULL';
			else
				$conditions[] = 'Registrations.cancelled IS NOT NULL';
		}

		// Filter for Name
		if ($this->request->getSession()->check('People.last_name'))
			$conditions['People.last_name COLLATE utf8_bin LIKE'] = $this->request->getSession()->read('People.last_name') . '%';

		if ($this->request->getSession()->check('Participants.partner') && empty($this->request->getSession()->read('Registrations.cancelled'))) {
			$this->loadModel('Competitions');
			
			$partner = $this->request->getSession()->read('Participants.partner');

			$or = [];
			
			if ($this->Competitions->find('all', array(
						'conditions' => [
							'tournament_id' => $tid,
							'type_of' => 'D'
						]
					))->count() > 0) {
				
				$partnerDoubles = [];
				
				if (in_array($partner, ['wanted', 'requested', 'multiple']))
					$partnerDoubles[] = 'Participants.double_partner_id IS NULL';
				else
					$partnerDoubles[] = 'Participants.double_partner_id IS NOT NULL';
				
				if (in_array($partner, ['requested', 'multiple']))
					$partnerDoubles[] = 'Registrations.id IN (SELECT double_partner_id FROM participants p WHERE NOT p.cancelled AND NOT p.double_cancelled)';
				
				if ($partner === 'multiple')
					$partnerDoubles[] = '(SELECT COUNT(double_partner_id) FROM participants p WHERE p.double_partner_id = Registrations.id AND NOT p.cancelled AND NOT p.double_cancelled) > 1';
				
				if ($partner === 'unconfirmed')
					$partnerDoubles[] = '(SELECT double_partner_id FROM participants p WHERE p.registration_id = Participants.double_partner_id AND NOT p.cancelled AND NOT p.double_cancelled) IS NULL';
				
				if ($partner === 'confirmed')
					$partnerDoubles[] = 'Registrations.id IN (SELECT double_partner_id FROM participants p WHERE p.registration_id = Participants.double_partner_id AND NOT p.cancelled AND NOT p.double_cancelled)';
				
				$or[] = $partnerDoubles;
			}
			
			if ($this->Competitions->find('all', array(
						'conditions' => [
							'tournament_id' => $tid,
							'type_of' => 'X'
						]
					))->count() > 0) {
				
				$partnerMixed = [];
				
				if (in_array($partner, ['wanted', 'requested', 'multiple']))
					$partnerMixed[] = 'Participants.mixed_partner_id IS NULL';
				else
					$partnerMixed[] = 'Participants.mixed_partner_id IS NOT NULL';
				
				if (in_array($partner, ['requested', 'multiple']))
					$partnerDoubles[] = 'Registrations.id IN (SELECT mixed_partner_id FROM participants p WHERE NOT p.cancelled AND NOT p.mixed_cancelled)';
				
				if ($partner === 'multiple')
					$partnerDoubles[] = '(SELECT COUNT(mixed_partner_id) FROM participants p WHERE p.double_partner_id = Registrations.id AND NOT p.cancelled AND NOT p.mixed_cancelled) > 1';
				
				if ($partner === 'unconfirmed')
					$partnerMixed[] = '(SELECT mixed_partner_id FROM participants p WHERE p.registration_id = Participants.mixed_partner_id AND NOT p.cancelled AND NOT p.mixed_cancelled) IS NULL';
				
				if ($partner === 'confirmed')
					$partnerMixed[] = 'Registrations.id IN (SELECT mixed_partner_id FROM participants p WHERE p.registration_id = Participants.mixed_partner_id AND NOT p.cancelled AND NOT p.mixed_cancelled)';
				
				$or[] = $partnerMixed;
			}
			
			if (count($or))
				$conditions[] = ['OR' => $or];
		}		
		
		if ($this->request->getSession()->check('Participants.para')) {
			if ($this->request->getSession()->read('Participants.para') == 'no')
				$conditions[] = 'People.ptt_class = 0';
			else if ($this->request->getSession()->read('Participants.para') == 'yes')
				$conditions[] = 'People.ptt_class <> 0';
		}
			
		if ($this->request->getSession()->read('Participants.age_category') == 'different')  {
			$conditions[] = array(
				array('OR' => array(
					array(
						'Participants.single_id IS NOT NULL',
						'(SELECT born FROM competitions WHERE id = single_id) <> '.
						'(SELECT MIN(born) FROM competitions ' .
						'  WHERE tournament_id = Registrations.tournament_id ' .
						'    AND type_of = \'S\' AND born >= YEAR(People.dob))'
					),
					array(
						'Participants.double_id IS NOT NULL',
						'(SELECT born FROM competitions WHERE id = double_id) <> '.
						'(SELECT MIN(born) FROM competitions ' .
						'  WHERE tournament_id = Registrations.tournament_id ' .
						'    AND type_of = \'D\' AND born >= YEAR(People.dob))'
					),
					array(
						'Participants.mixed_id IS NOT NULL',
						'(SELECT born FROM competitions WHERE id = mixed_id) <> '.
						'(SELECT MIN(born) FROM competitions ' .
						'  WHERE tournament_id = Registrations.tournament_id ' .
						'    AND type_of = \'X\' AND born >= YEAR(People.dob))'
					),
					array(
						'Participants.team_id IS NOT NULL',
						'(SELECT born FROM competitions WHERE id = team_id) <> '.
						'(SELECT MIN(born) FROM competitions ' .
						'  WHERE tournament_id = Registrations.tournament_id ' .
						'    AND type_of = \'T\' AND born >= YEAR(People.dob))'
					)
				))
			);
		}
		
		if ($this->request->getSession()->read('Participants.age_category') == 'missing')  {
			$this->loadModel('Competitions');
			$or = array();
			
			if ($this->Competitions->find('all', [
						'conditions' => [
							'tournament_id' => $tid,
							'type_of' => 'S'
						]
					])->count() > 0) {
				$or[] = 'Participants.single_id IS NULL';
			}
			
			if ($this->Competitions->find('all', [
						'conditions' => [
							'tournament_id' => $tid,
							'type_of' => 'D'
						]
					])->count() > 0) {
				$or[] = 'Participants.double_id IS NULL';
			}
			
			if ($this->Competitions->find('all', [
						'conditions' => [
							'tournament_id' => $tid,
							'type_of' => 'X'
						]
					])->count() > 0) {
				$or[] = 'Participants.mixed_id IS NULL';
			}
			
			if ($this->Competitions->find('all', [
						'conditions' => [
							'tournament_id' => $tid,
							'type_of' => 'T'
						]
					])->count() > 0) {
				$or[] = 'Participants.team_id IS NULL';
			}

			$conditions[] = ['OR' => $or];
		}

		if ($this->request->getSession()->read('Participants.age_category') == 'wrong')  {
			$conditions[] = ['OR' => [
					'double_id <> (SELECT double_id FROM participants p WHERE p.registration_id = Participants.double_partner_id)',
					'mixed_id <> (SELECT mixed_id FROM participants p WHERE p.registration_id = Participants.mixed_partner_id)'
				]];
		}
		
		$this->paginate = array(
			'sortableFields' => [
				'People.display_name',
				'Nations.name',
				'Types.name',
				'People.extern_id',
				'Participants.start_no',
				'Registrations.modified'
			],
			'contain' => array(
				'Types',
				'Participants' => array(
					'DoublePartners' => array(
						'People', 
						'Participants'
					),
					'MixedPartners' => array(
						'People', 
						'Participants'
					),
				),  
				'People' => array('Nations'),
			),
			'conditions' => $conditions,
			'order' => ['People.display_name' => 'ASC']
		);
		
		$registrations = $this->paginate()->toArray();
				
		$this->loadModel('Participants');
		$this->loadModel('Shop.OrderStatus');
		$this->loadModel('Shop.OrderArticles');
		$this->loadModel('Shop.Orders');
		
		$orderStati = $this->OrderStatus->find('list', array(
			'fields' => array('id', 'name')
		))->toArray();
		
		foreach($registrations as $k => $r) {
			$order_id = $this->OrderArticles->fieldByConditions('order_id', array(
				'person_id' => $r['person_id']
			));
			
			if (!empty($order_id)) {
				$order_status_id = $this->Orders->fieldByConditions('order_status_id', array(
					'id' => $order_id
				));
				
				if (!empty($order_status_id))
					$registrations[$k]['OrderStatus'] = $orderStati[$order_status_id];
			}
			
			if ($r['type_id'] != TypesTable::getPlayerId()) {
				$registrations[$k]['requests'] = 0;
				continue;				
			}
			
			$rid = $r['id'];
			
			$registrations[$k]['requests'] = $this->Participants->find('all', array(
				'conditions' => array(
					'OR' => array(
						array(
							'Participants.double_partner_id' => $rid,
							'Participants.double_cancelled' => 0
						),
						array(
							'Participants.mixed_partner_id' => $rid,
							'Participants.mixed_cancelled' => 0
						),
					)
				)
			))->count();
		}
		$this->set('registrations', $registrations);

		// Count the number of competitions per type to hide columns
		$this->loadModel('Competitions');
		$count = array();
		$count['S'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "S"')))->count();
		$count['D'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "D"')))->count();
		$count['X'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "X"')))->count();
		$count['T'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "T"')))->count();

		$this->set('count', $count);
		
		$conditions = array();
		
		if ($this->request->getSession()->check('Users.id'))
			$conditions['People.user_id'] = $this->request->getSession()->read('Users.id');
		else if ($this->Auth->user('group_id') == GroupsTable::getTourOperatorId())
			$conditions['People.user_id'] = $this->Auth->user('id');
		else if ($this->Auth->user('group_id') == GroupsTable::getParticipantId())
			$conditions['People.user_id'] = $this->Auth->user('id');

		$this->loadModel('Nations');
		$this->loadModel('People');
		$nations = $this->Nations->find('list', array(
			'fields' => array('Nations.id', 'Nations.name'),
			'conditions' => [
				'id IN' => $this->People->find()->where($conditions)->select('nation_id')
			],
			'order' => array('name')
		))->toArray();
		
		$this->set('nations', $nations);
		$this->set('nation_id', $this->request->getSession()->read('Nations.id'));
		
		// For references in double / mixed partners
		$this->loadModel('Nations');
		$this->set('allNations', $this->Nations->find('list', array(
			'fields' => array('id', 'name')
		))->toArray());

		$this->loadModel('Types');
		$conditions = array();
		if ($this->request->getSession()->check('Groups.type_ids'))
			$conditions['Types.id IN'] = explode(',', $this->request->getSession()->read('Groups.type_ids'));

		$this->set('types', $this->Types->find('list', array(
			'order' => 'name', 
			'fields' => array('id', 'name'),
			'conditions' => $conditions
		))->toArray());
		$this->set('type_id', $this->request->getSession()->check('Types.id') ? $this->request->getSession()->read('Types.id') : false);

		$this->loadModel('Competitions');
		$this->set('competitions', $this->Competitions->find('list', array(
			'order' => 'name', 
			'fields' => array('id', 'name'), 
			'conditions' => [
				'tournament_id' => $tid
			]
		))->toArray());
		$this->set('competition_id', $this->request->getSession()->read('Competitions.id') ?: false);
		
		$this->set('para', $this->request->getSession()->read('Participants.para') ?: false);
		
		// Don't set if not in session
		if ($this->request->getSession()->check('Participants.partner'))
			$this->set('partner', $this->request->getSession()->read('Participants.partner'));
		
		if ($this->request->getSession()->check('Registrations.cancelled'))
			$this->set('cancelled', $this->request->getSession()->read('Registrations.cancelled'));
		
		if ($this->request->getSession()->check('Participants.age_category'))
			$this->set('age_category', $this->request->getSession()->read('Participants.age_category'));
		
		$this->loadModel('Users');
		$this->set('user_id', $this->request->getSession()->read('Users.id'));
		$this->set('username', $this->Users->fieldByConditions('username', array(
			'id' => $this->request->getSession()->read('Users.id') ?: 0)
		));

		$this->loadModel('People');
		$last_name = $this->request->getSession()->read('People.last_name');
		$allchars = array();

		if (!$last_name)
			$last_name = '';

		for ($count = 0; $count <= mb_strlen($last_name); $count++) {
			$conditions = array();
			if ($this->request->getSession()->check('Users.id'))
				$conditions['People.user_id'] = $this->request->getSession()->read('Users.id');
			else if ($this->Auth->user('group_id') == GroupsTable::getTourOperatorId())
				$conditions['People.user_id'] = $this->Auth->user('id');
			else if ($this->Auth->user('group_id') == GroupsTable::getParticipantId())
				$conditions['People.user_id'] = $this->Auth->user('id');
			
			if ($this->request->getSession()->check('Nations.id'))
				$conditions['People.nation_id'] = $this->request->getSession()->read('Nations.id');

			if ($this->request->getSession()->check('People.sex'))
				$conditions['People.sex'] = $this->request->getSession()->read('People.sex');

			if ($count > 0)
				$conditions['People.last_name COLLATE utf8_bin LIKE'] = mb_substr($last_name, 0, $count) . '%';

			$tmp = $this->People->find('all', array(
				'fields' => ['firstchar' =>  'DISTINCT LEFT(People.last_name COLLATE utf8_bin, ' . ($count + 1) . ')'],
				'conditions' => $conditions,
				'order' => 'firstchar COLLATE utf8_unicode_ci'
			));

			$tmp = Hash::extract($tmp->toArray(), '{n}.firstchar');
			
			if (!is_array($tmp))
				$tmp = array();

			// Make sure any selected sequence of characters is in the list
			if (mb_strlen($last_name) > $count && !in_array(mb_substr($last_name, 0, $count + 1), $tmp))
				$tmp[] = mb_substr($last_name, 0, $count + 1);

			// sort($tmp);

			$allchars[] = $tmp;
		}

		$this->set('allchars', $allchars);

		$this->set('last_name', $this->request->getSession()->read('People.last_name'));
	}

	function view($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid registration'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		$current_user = $this->_user;

		$registration = $this->Registrations->find('all', array(
			'conditions' => array('Registrations.id' => $id),
			'contain' => array(
				'Tournaments' => array('fields' => array('id', 'description', 'enter_before', 'modify_before')), 
				'People' => array('fields' => array('id', 'display_name', 'nation_id', 'user_id')), 
				'Types' => array('fields' => array('id', 'description')),
				'Participants' => array(
						'Singles' => array('fields' => array('id', 'description')), 
						'Doubles' => array('fields' => array('id', 'description')),
						'DoublePartners' => array(
							'Participants' => array('fields' => array('id', 'double_partner_id', 'registration_id')), 
							'People' => array('fields' => array('id', 'display_name', 'nation_id'),
								'Nations' => array('fields' => array('id', 'name')))),
						'Mixed' => array('fields' => array('id', 'description')),
						'MixedPartners' => array(
							'Participants' => array('fields' => array('id', 'mixed_partner_id', 'registration_id')),
							'People' => array('fields' => array('id', 'display_name', 'nation_id'),
								'Nations' => array('fields' => array('id', 'name')))),
						'Teams' => array('fields' => array('id', 'description'))
				)
			)
		))->first();


		if (!empty($registration['participant']['replaced_by_id'])) {
			$replaced_by = $this->Registrations->find('all', array(
				'conditions' => array('Registrations.id' => $registration['participant']['replaced_by_id']),
				'contain' => array('People' => array('Nations'))
			))->first();

			$registration['ReplacedBy'] = $replaced_by;
		}

		// Security check: May this person be viewed by the current user?
		if (!empty($current_user['nation_id']) && $registration['person']['nation_id'] != $current_user['nation_id'] ||
		    !empty($current_user['group']['type_ids']) && !in_array($registration['type_id'], explode(',', $current_user['group']['type_ids'])) ) {

			$this->MultipleFlash->setFlash(__('You are not allowed to view this registration'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		// 'contain' above does not work if there is no double / mixed partner
		if (empty($registration['participant']['double_partner_id'])) {
			$registration['participant']['double_partner']['person'] = array('display_name' => false);
		}
		
		if (empty($registration['participant']['mixed_partner_id'])) {
			$registration['participant']['mixed_partner']['person'] = array('display_name' => false);
		}

		$this->set('registration', $registration);

		// Count the number of competitions per type to hide rows
		$this->loadModel('Competitions');
		$tid = $this->request->getSession()->read('Tournaments.id');
		$count = array();
		$count['S'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "S"')))->count();
		$count['D'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "D"')))->count();
		$count['X'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "X"')))->count();
		$count['T'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "T"')))->count();

		$this->set('count', $count);
	}

	function history($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid registration'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		$this->loadModel('ParticipantHistories');
		$this->loadModel('Competitions');
		$this->loadModel('People');

		$registration = $this->Registrations->find('all', array(
			'contain' => array('People', 'Participants'),
			'conditions' => array('Registrations.id' => $id)
		))->first();

		$this->set('registration', $registration);

		$this->loadModel('Competitions');
		$competitions = $this->Competitions->find('list', array(
			'conditions' => array('tournament_id' => $this->request->getSession()->read('Tournaments.id')),
			'fields' => array('id', 'description')
		))->toArray();

		$histories = $this->ParticipantHistories->find('all', array(
			'contain' => array('Users'),
			'conditions' => array('registration_id' => $id),
			'order' => 'ParticipantHistories.created DESC'
		))->toArray();

		foreach ($histories as $k => $v) {
			$when = $v['created'];
			$field_name = $v['field_name'];
			$old_value = $v['old_value'];
			$new_value = $v['new_value'];

			$histories[$k]['old_name'] = $old_value;
			$histories[$k]['new_name'] = $new_value;

			if ( $field_name == 'single_id' || $field_name == 'double_id' || 
				 $field_name == 'mixed_id' || $field_name == 'team_id' ) {

				if (!empty($old_value)) {
					$histories[$k]['old_name'] = $competitions[$old_value];
				}

				if (!empty($new_value)) {
					$histories[$k]['new_name'] = $competitions[$new_value];
				}
			} 

			if ( $field_name == 'double_partner_id' || $field_name == 'mixed_partner_id' || 
			     $field_name == 'double_partner_withdrawn' || $field_name == 'mixed_partner_withdrawn' ||
			     $field_name == 'double_partner_wanted' || $field_name == 'mixed_partner_wanted' ||
			     $field_name == 'double_partner_confirmed' || $field_name == 'mixed_partner_confirmed' ||
			     $field_name == 'replaced_by_id'
			   ) {

				$partnerField = '';
				$cancelledField = '';

				if (strncmp($field_name, 'double_', strlen('double_')) == 0) {
					$partnerField = 'double_partner_id';
					$cancelledField = 'double_cancelled';
				} else {
					$partnerField = 'mixed_partner_id';
					$cancelledField = 'mixed_cancelled';
				}
				
				$oldRegistration = $this->_findRevision($id, $when);

				if (!empty($old_value)) {
					$partner = $this->_findRevision($old_value, $when);

					$name = $partner['person']['display_name'];

					if ($partner['person']['nation_id'] != $registration['person']['nation_id'])
						$name .= ' (' . $partner['person']['nation']['name'] . ')';

					if ( empty($partner['participant'][$partnerField]) || 
					     $oldRegistration['participant'][$partnerField] != $partner['id'] ||
						 $oldRegistration['id'] != $partner['participant'][$partnerField]) {

						if ($field_name != 'replaced_by_id')
							$name = $name . ' (' . __('wanted') . ')';
					}

					$histories[$k]['old_name'] = array($name, array('action' => 'history', $old_value));
				}

				if (!empty($new_value)) {
					$partner = $this->_findRevision($new_value, $when);

					$name = $partner['person']['display_name'];

					if ($partner['person']['nation_id'] != $registration['person']['nation_id'])
						$name .= ' (' . $partner['person']['nation']['name'] . ')';

					if ( empty($partner['participant'][$partnerField]) || 
					     $oldRegistration['participant'][$partnerField] != $partner['id'] ||
						 $oldRegistration['id'] != $partner['participant'][$partnerField]) {

						if ($field_name != 'replaced_by_id')
							$name = $name . ' (' . __('wanted') . ')';
					}

					$histories[$k]['new_name'] = array($name, array('action' => 'history', $new_value));
				}
			}
		}

		$this->set('histories', $histories);
	}

	function revision($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid registration'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		if (empty($this->request->getQuery('date'))) {
			$this->MultipleFlash->setFlash(__('Invalid date'), 'error');
			return $this->redirect(array('action' => 'history', $id));
		}

		$when = $this->request->getQuery('date');

		$current_user = $this->_user;

		$registration = $this->Registrations->find('all', array(
			'conditions' => array('Registrations.id' => $id),
			'contain' => array(
				'Tournaments' => array('fields' => array('id', 'description', 'enter_before', 'modify_before')), 
				'People' => array('fields' => array('id', 'display_name', 'nation_id', 'user_id')), 
				'Types' => array('fields' => array('id', 'description'))
			)
		))->first();

		$registration['participant'] = array();


		// Security check: May this person be viewed by the current user?
		if (!empty($current_user['nation_id']) && $registration['person']['nation_id'] != $current_user['nation_id'] ||
		    !empty($current_user['group']['type_ids']) && !in_array($registration['type_id'], explode(',', $current_user['group']['type_ids'])) ) {

			$this->MultipleFlash->setFlash(__('You are not allowed to view this registration'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		// Count the number of competitions per type to hide rows
		$this->loadModel('Competitions');
		$tid = $this->request->getSession()->read('Tournaments.id');
		$count = array();
		$count['S'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "S"')))->count();
		$count['D'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "D"')))->count();
		$count['X'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "X"')))->count();
		$count['T'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "T"')))->count();

		$this->set('count', $count);

		$this->loadModel('ParticipantHistories');
		$histories = $this->ParticipantHistories->find('all', array(
			'contain' => array('Users'),
			'conditions' => array(
				'ParticipantHistories.registration_id' => $id,
				'ParticipantHistories.created <=' => $when
			),
			'order' => ['ParticipantHistories.created ASC']
		));

		foreach ($histories as $history) {
			$field_name = $history['field_name'];
			$old_value  = $history['old_value'];
			$new_value  = $history['new_value'];

			if ($field_name == 'created') {
				$registration['participant'] = unserialize($history['new_value']);
			} else if ($field_name == 'cancelled') {
				$registration['participant'] = unserialize($history['new_value']);
			} else {
				$registration['participant'][$field_name] = $new_value;
			}
		}

		// Add empty partner if there is none
		if (!$registration['participant']['double_partner_id']) {
			$registration['participant']['double_partner']['person'] = array('display_name' => false);
		}
		
		if (!$registration['participant']['mixed_partner_id']) {
			$registration['participant']['mixed_partner']['person'] = array('display_name' => false);
		}

		$this->loadModel('Competitions');

		if (!empty($registration['participant']['single_id'])) {
			$single = $this->Competitions->get($registration['participant']['single_id']);
			$registration['participant']['single'] = $single;
		}

		if (!empty($registration['participant']['double_id'])) {
			$double = $this->Competitions->get($registration['participant']['double_id']);
			$registration['participant']['double'] = $double;
		}

		if (!empty($registration['participant']['mixed_id'])) {
			$mixed = $this->Competitions->get($registration['participant']['mixed_id']);
			$registration['participant']['mixed'] = $mixed;
		}

		if (!empty($registration['participant']['team_id'])) {
			$team = $this->Competitions->get($registration['participant']['team_id']);
			$registration['participant']['team'] = $team;
		}

		if (!empty($registration['participant']['double_partner_id'])) {
			$doublePartner = $this->_findRevision($registration['participant']['double_partner_id'], $when);

			$registration['participant']['double_partner'] = $doublePartner;
		}
		
		if (!empty($registration['participant']['mixed_partner_id'])) {
			$mixedPartner = $this->_findRevision($registration['participant']['mixed_partner_id'], $when);

			$registration['participant']['mixed_partner'] = $mixedPartner;
		}
		
		if (!empty($registration['participant']['replaced_by_id'])) {
			$replaced_by = $this->Registrations->find('all', array(
				'conditions' => array('Registrations.id' => $registration['participant']['replaced_by_id']),
				'contain' => array('People' => array('Nations'))
			))->first();

			$registration['ReplacedBy'] = $replaced_by;
		}

		$this->set('registration', $registration);

		$this->set('revision', $when);

		$this->render('view');
	}
	

	// This method adds a known person from the people table to a tournament
	function add() {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		}

		$tid = $this->request->getSession()->read('Tournaments.id');
		
		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->getData();
			
			$this->loadModel('People');

			$tid = $data['tournament_id'];
			$pid = $data['person_id'];

			$rid = $this->Registrations->fieldByConditions('id', array(
				'tournament_id' => $tid,
				'person_id' => $pid
			));

			// The registration has not cancelled
			$data['cancelled'] = null;
			
			if (empty($data['person'])) {
				$person = $this->People->find('all', array(
					'recursive' => -1,
					'conditions' => array('People.id' => $pid)
				))->first();
				
				$data['person'] = $person;
			}

			if (!empty($rid)) 
				$data['id'] = $rid;

			// Verify uniqueness of start number	
			if ($data['type_id'] == TypesTable::getPlayerId()) {
				// Start no of player must be unique
				if (!empty($data['participant']['start_no'])) {
					$count = $this->Registrations->find('all', array(
						'conditions' => array(
							'Registrations.tournament_id' => $tid, 
							'Registrations.person_id <>' => $pid, 
							'Participants.start_no' => $data['participant']['start_no']
						),
						'contain' => array('Participants')
					))->count();

					if ($count > 0) {
						$this->MultipleFlash->setFlash(__('Start number must be unique'), 'error');
						return $this->redirect(array('action' => 'index'));
					}
				}

				// If should not be possible to reactivate a player via add, but who knows ...
				if (!empty($rid))
					$data['participant']['registration_id'] = $rid;
			}

			if ($this->RegistrationUpdate->_save($data)) {
				$this->MultipleFlash->setFlash(__('The registration has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The registration could not be saved.'), 'error');
				return $this->redirect(array('action' => 'index'));
			}
		} 
		
		if (UsersTable::hasRootPrivileges($this->_user) || $this->Auth->user('group_id') == GroupsTable::getOrganizerId()) {
			// Only root  and organizer can add people after the second deadline or players after 1st deadline
		} else {
			$enter_before = $this->_tournament['enter_before'];
			$modify_before = $this->_tournament['modify_before'];

			if ($modify_before < date('Y-m-d')) {
				$this->MultipleFlash->setFlash(__('You cannot enter new people at this time any more'), 'error');
				return $this->redirect(array('action' => 'index'));
			}
			if ($enter_before < date('Y-m-d')) {
				$this->MultipleFlash->setFlash(__('You cannot enter new players at this time any more'), 'warning');
			}
		}			

		// No need to set types, they will be set by an Ajax callback

		$this->set('registration', $this->Registrations->newEmptyEntity());
		$this->set('people', $this->_findPeople());
	}

	function edit($id = null) {
		if ($this->request->getData('cancel') !== null)
			return $this->redirect(array('action' => 'index'));

		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid registration'), 'error');
			return $this->redirect(array('action' => 'index'));
		} 
		
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		}

		$this->loadModel('Competitions');
		$this->loadModel('People');
		$registration = $this->Registrations->find('all', array(
			'conditions' => ['Registrations.id' => $id],
			'contain' => array(
				'Types',
				'People' => array('Nations'),
				'Participants' => array(
					'Singles',
					'Doubles',
					'DoublePartners' => array('Participants', 'People'),
					'Mixed',
					'MixedPartners' => array('Participants', 'People'),
					'Teams'
				)
			)
		))->first();

		if (!$this->RegistrationUpdate->_isEditAllowed($registration)) {
			$this->MultipleFlash->setFlash(__('You are not allowed to edit this registration'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		// If participation in an event was cancelled, reset to null.
		// But keep the partner around, we will need the id to initialize the combo box, so when
		// the player is added again to the event the old partner is selected by default (if still available)
		if ($registration->type_id == TypesTable::getPlayerId()) {
			if ($registration['participant']['single_cancelled']) {
				$registration['participant']['single_id'] = null;
			}

			if ($registration['participant']['double_cancelled']) {
				$registration['participant']['double_id'] = null;
				// $registration['participant']['double_partner_id'] = null;
			}

			if ($registration['participant']['mixed_cancelled']) {
				$registration['participant']['mixed_id'] = null;
				// $registration['participant']['mixed_partner_id'] = null;
			}

			if ($registration['participant']['team_cancelled']) {
				$registration['participant']['team_id'] = null;
			}

			// For veterans select age category of double / mixed partner
			if (!$registration['participant']['double_cancelled'] && !empty($registration['participant']['double_partner_id']))
				$registration['participant']['double_id'] = 
					$registration['participant']['double_partner']['participant']['double_id'];
			if (!$registration['participant']['mixed_cancelled'] && !empty($registration['participant']['mixed_partnerid']))
				$registration['participant']['mixed_id'] =
					$registration['participant']['mixed_partner']['participant']['mixed_id'];
		}

		$tid = $registration['tournament_id'];
		$pid = $registration['person_id'];

		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->getData();
			
			$this->loadModel('Registrations');
			$this->loadModel('Participants');
			$this->loadModel('People');

			$tid = $data['tournament_id'];
			$pid = $data['person']['id'];
	
			$person_id = $this->Registrations->fieldByConditions('person_id', array('id = ' => $id));

			// Verify uniqueness of start number	
			if ($data['type_id'] == TypesTable::getPlayerId()) {
				// Start no of player must be unique
				// But mind if the player changed
				if (!empty($data['participant']['start_no'])) {
					$count = $this->Registrations->find('all', array(
						'conditions' => array(
							'Registrations.tournament_id' => $tid, 
							'Registrations.person_id <> ' => $pid, 
							'Participants.start_no' => $data['participant']['start_no']
						),
						'contain' => array('Participants')
					))->count();

					if ($count > 0) {
						if ($person_id == $pid) {
							$this->MultipleFlash->setFlash(__('Start number must be unique'), 'error');
							return $this->redirect(array('action' => 'index'));
						} else {
							$start_no = $this->Participants->find()
									->select(['start_no' => 'MAX(start_no) + 1'])
									->contain(['Registrations'])
									->where(['Registrations.tournament_id' => $tid])
									->first()
									->start_no
							;
							
							$data['participant']['start_no'] = $start_no;
						}
					} 
				}
			}

			$person = $this->People->get($pid);
			$person = $this->People->patchEntity($person, $data['person']);
			
			if ($person->isDirty('first_name') || $person->isDirty('last_name')) {
				// If the name has changed reset display name
				$person->display_name = '';
			}

			// Start transaction. The model argument is a dummy, hopefully ...
			$db = $this->Registrations->getConnection();

			$db->begin();
			$commit = true;
			
			if ($person->isDirty()) {
				if ($commit && !$this->People->save($person)) {
					$this->MultipleFlash->setFlash(__('Could not update person'), 'error');
					$commit = false;
					debug($person); die();
				}
			}

			if ($commit && !$this->RegistrationUpdate->_save($data)) {
				$this->MultipleFlash->setFlash(__('The registration could not be saved.'), 'error');
				$commit = false;
			} 
			
			// Not all users see the articles
			if (isset($data['Article']))
				$items = $data['Article'];
			else
				$items = array();

			if ($commit && !$this->OrderUpdate->editParticipant($id, $items)) {
				$this->MultipleFlash->setFlash(__('Could not update order'), 'error');
				$commit = false;
			}
			
			if ($commit) {
				if ($db->commit()) {
					$this->MultipleFlash->setFlash(__('Registration updated'), 'success');
					return $this->redirect(array('action' => 'index'));		
				}
				
				$this->MultipleFlash->setFlash(__('Could not save changes'), 'error');
			}
			
			if (!$commit)
				$db->rollback();
		}
		
		$registration['person']['is_para'] = (($registration['person']['ptt_class'] ?: 0) > 0);
		
		$this->loadModel('Competitions');
		$havePara = $this->Competitions->find()
				->where([
					'tournament_id' => $tid,
					'ptt_class > 0'
				]) 
				->count()
			> 0;
		
		$this->set('havePara', $havePara);

		$this->set('registration', $registration);

		$this->loadModel('Nations');
		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'description')
		))->toArray());

		$this->_setFields($registration);

		$this->OrderUpdate->setVarsForRegistration($pid);		 
	}
	
	
	function edit_participant($id = null) {
		return $this->edit($id);
	}

	function delete($id = null) {
		if (!$this->request->is(['post']))
			return $this->redirect(['action' => 'index']);
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid id for registration'), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		
		if ($this->request->getSession()->check('Registrations.silent'))
			$this->RegistrationUpdate->fromImport = true;

		$pid = $this->Registrations->fieldByConditions('person_id', array(
			'Registrations.id' => $id
		));
		
		// Could not happen
		if (empty($pid))
			return $this->redirect(array('action'=>'index'));
			
		// Start transaction. The model argument is a dummy, hopefully ...
		$db = $this->Registrations->getConnection();

		$db->begin();

		if (!$this->RegistrationUpdate->_delete($id)) {
			$db->rollback();
			$this->MultipleFlash->setFlash(__('Registration could not be deleted'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		if ( $this->Acl->check($this->_user, 'Registrations/add_participant') ) {
			// Update Order		
			if (!$this->OrderUpdate->deleteParticipant($pid)) {
				$db->rollback();
				$this->MultipleFlash->setFlash(__('Could not update order'), 'error');
				return $this->redirect(array('action' => 'index'));
			}
		}
		
		if (!$db->commit()) {
			$this->MultipleFlash->setFlash(__('Could not save changes'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		$this->MultipleFlash->setFlash(__('Registration deleted'), 'success');
		
		return $this->redirect(array('action' => 'index'));
	}
	
	
	function delete_participant($id = null) {
		$this->delete($id);
	}
	
	
	// Lists all players who requested this player as a double / mixed partner
	function requests($id = null) {
		if (!empty($this->request->getQuery('tournament_id'))) {
			$this->request->getSession()->write('Tournaments.id', $this->request->getQuery('tournament_id'));
		}

		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		}

		// The current tournament, must be set
		$tid = $this->request->getSession()->read('Tournaments.id');

		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid id for partner requests'), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		
		$me = $this->Registrations->find('all', array(
			'conditions' => array('Registrations.id' => $id),
			'contain' => array(
				'People',
				'Participants'
			)
		))->first();
		
		$conditions = array(
			'OR' => array(
				array('Participants.double_partner_id' => $id, 'Participants.double_cancelled = 0'),
				array('Participants.mixed_partner_id' => $id, 'Participants.mixed_cancelled = 0')
			)			
		);
		
		if (!empty($me['participant']['double_partner_id']))
			$conditions[] = 'Registrations.id <> ' . $me['participant']['double_partner_id'];

		if (!empty($me['participant']['mixed_partner_id']))
			$conditions[] = 'Registrations.id <> ' . $me['participant']['mixed_partner_id'];
		
		// I need all of Participants to test if the Double- / Mixed- Partner is confirmed or not
		$registrations = $this->Registrations->find('all', array(
			'contain' => array(
				'People' => array('Nations'),
				'Participants' => array(
					'Singles',
					'Doubles',
					'DoublePartners' => array('Participants', 'People'),
					'Mixed',
					'MixedPartners' => array('Participants', 'People'),
					'Teams'
				)
			),
			'conditions' => $conditions
		))->toArray();
		
		$this->loadModel('Competitions');
		
		foreach ($registrations as $k => $r) {
			if ($r['person']['sex'] == $me['person']['sex']) {
				unset($registrations[$k]['participant']['mixed_id']);
				
				if ($r['participant']['double_id'] != $me['participant']['double_id']) {
					// Veterans
					if ($me['person']['born'] < date('Y') - 30) {
						$registrations[$k]['participant']['double_id'] = 
							$this->Competitions->fieldByConditions('id', array(
									'Competitions.born >=' => max($me['person']['born'], $r['person']['born']),
									'Competitions.tournament_id' => $tid,
									'Competitions.type_of' => 'D',
									'Competitions.sex' => $me['person']['sex'],
									'Competitions.ptt_class >=' => max($me['person']['ptt_class'], $r['person']['ptt_class'])
								) + ($me['person']['ptt_class'] == 0 ? array('Competitions.ptt_class = 0') : 'Competitions.ptt_class > 0'),
								['order' => [
									'Competitions.ptt_class' => 'DESC',
									'Competitions.born' => 'ASC'
								]]
							);
					} else {
						// Youth
						$registrations[$k]['participant']['double_id'] = 
							$this->Competitions->fieldByConditions('id', array(
									'Competitions.born <=' => min($me['person']['born'], $r['person']['born']),
									'Competitions.tournament_id' => $tid,
									'Competitions.type_of' => 'D',
									'Competitions.sex' => $me['person']['sex'],
									'Competitions.ptt_class >=' => max($me['person']['ptt_class'], $r['person']['ptt_class'])
								) + ($me['person']['ptt_class'] == 0 ? array('Competitions.ptt_class = 0') : 'Competitions.ptt_class > 0'),
								['order' => [
									'Competitions.ptt_class' => 'DESC',
									'Competitions.born' => 'ASC'
								]]
							);						
					}
				}
			} else {
				unset($registrations[$k]['participant']['double_id']);
				
				if ($r['participant']['mixed_id'] != $me['participant']['mixed_id']) {
					// Veterans
					if ($me['person']['born'] < date('Y') - 30) {
						$registrations[$k]['participant']['mixed_id'] = 
							$this->Competitions->fieldByConditions('id', array(
									'Competitions.born >=' => max($me['person']['born'], $r['person']['born']),
									'Competitions.tournament_id' => $tid,
									'Competitions.type_of' => 'X',								
									'Competitions.ptt_class >=' => max($me['person']['ptt_class'], $r['person']['ptt_class'])
								) + ($me['person']['ptt_class'] == 0 ? array('Competitions.ptt_class = 0') : 'Competitions.ptt_class > 0'),
								['order' => [
									'Competitions.ptt_class' => 'DESC',
									'Competitions.born' => 'ASC'
								]]
							);
					} else {
						// Youth
						$registrations[$k]['participant']['mixed_id'] = 
							$this->Competitions->fieldBConditions('id', array(
									'Competitions.born <= ' . min($me['person']['born'], $r['person']['born']),
									'Competitions.tournament_id' => $tid,
									'Competitions.type_of' => 'X',
									'Competitions.ptt_class >=' => max($me['person']['ptt_class'], $r['person']['ptt_class'])
								) + ($me['person']['ptt_class'] == 0 ? array('Competitions.ptt_class = 0') : 'Competitions.ptt_class > 0'),
								['order' => [
									'Competitions.ptt_class' => 'DESC',
									'Competitions.born' => 'ASC'
								]]
							);						
					}
				}
			}
		}
		
		$this->set('registrations', $registrations);
		$this->set('me', $me);

		// Count the number of competitions per type to hide columns
		$this->loadModel('Competitions');
		$count = array();
		$count['S'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "S"')))->count();
		$count['D'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "D"')))->count();
		$count['X'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "X"')))->count();
		$count['T'] = $this->Competitions->find('all', array('conditions' => array('tournament_id' => $tid, 'type_of = "T"')))->count();

		$this->set('count', $count);

		$this->set('competitions', $this->Competitions->find('list', array(
			'fields' => array('id', 'name'), 
			'conditions' => array('tournament_id' => $tid)
		))->toArray());
		
		$this->loadModel('Nations');
		$this->set('nations', $this->Nations->find('list', array(
			'order' => 'name', 
			'fields' => array('id', 'name')
		))->toArray());		
	}
	
	function accept($id = null, $partner_id = null) {
		if (!empty($this->request->getQuery('tournament_id'))) {
			$this->request->getSession()->write('Tournaments.id', $this->request->getQuery('tournament_id'));
		}

		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		}

		if (!$id || !$partner_id) {
			$this->MultipleFlash->setFlash(__('Invalid id for registration'), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		
		$me = $this->Registrations->find('all', array(
			'conditions' => array('Registrations.id' => $id),
				'contain' => array(
					'Types',
					'People' => array('Nations'),
					'Participants' => array(
						'Singles',
						'Doubles',
						'DoublePartners' => array('Participants', 'People'),
						'Mixed',
						'MixedPartners' => array('Participants', 'People'),
						'Teams'
					)
				)
		))->first()->toArray();
		
		if (!$this->RegistrationUpdate->_isEditAllowed($me)) {
			$this->MultipleFlash->setFlash(__('You are not allowed to edit this registration'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		$partner  = $this->Registrations->find('all', array(
			'conditions' => array('Registrations.id' => $partner_id),
				'contain' => array(
					'People',
					'Participants'
				)
		))->first()->toArray();
		
		if ($partner['participant']['double_partner_id'] == $me['id'])
			$me['participant']['double_partner_id'] = $partner_id;
		if ($partner['participant']['mixed_partner_id'] == $me['id'])
			$me['participant']['mixed_partner_id'] = $partner_id;
		
		if (!$this->RegistrationUpdate->_save($me)) {
			$this->MultipleFlash->setFlash(__('The registration could not be saved.'), 'error');
			return $this->redirect(array('action' => 'requests', $id));
		} else {
			$this->MultipleFlash->setFlash(__('You have accepted {0} as the partner for {1}', $partner['person']['display_name'], $me['person']['display_name']), 'success');
			return $this->redirect(array('action' => 'requests', $id));
		}
	}
	
	function reject($id = null, $partner_id = null) {
		if (!$id || !$partner_id) {
			$this->MultipleFlash->setFlash(__('Invalid id for registration'), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		
		if (!empty($this->request->getQuery('tournament_id'))) {
			$this->request->getSession()->write('Tournaments.id', $this->request->getQuery('tournament_id'));
		}

		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		}
		
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$me = $this->Registrations->find('all', array(
			'conditions' => array('Registrations.id' => $id),
			'contain' => array(
				'Types',
				'People' => array('Nations'),
				'Participants' => array(
					'Singles',
					'Doubles',
					'DoublePartners' => array('Participants', 'People'),
					'Mixed',
					'MixedPartners' => array('Participants', 'People'),
					'Teams'
				)
			)
		))->first();
		
		if (!$this->RegistrationUpdate->_isEditAllowed($me)) {
			$this->MultipleFlash->setFlash(__('You are not allowed to edit this registration'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		$partner  = $this->Registrations->find('all', array(
			'conditions' => array('Registrations.id' => $partner_id),
				'contain' => array(
					'People',
					'Participants'
				)
		))->first();
		
		$type = null;
		
		if ($partner->participant->double_partner_id == $me['id']) {
			$partner->participant->double_partner_id = null;
			$partner->participant->double_id = $this->_selectEvent($partner['person'], 'D', $tid);
			
			// Mark $partner explicitely as dirty, because without using patchEntity
			// this is not done automatically
			$partner->setDirty('participant', true);
			$type = 'double';
		}
		
		if ($partner->participant->mixed_partner_id == $me['id']) {
			$partner->participant->mixed_partner_id = null;
			$partner->participant->mixed_id = $this->_selectEvent($partner['person'], 'X', $tid);
			
			// Mark $partner explicitely as dirty, because without using patchEntity
			// this is not done automatically
			$partner->setDirty('participant', true);
			$type = 'mixed';
		}
		
		if ($this->Registrations->save($partner)) {
			// $partner und $me vertauschen: Die Mail geht an den Partner und nicht an den Spieler
			$this->RegistrationUpdate->_sendMail('partner_removed_partner', 'Partner Rejected', $type, $partner, $me);
			$this->MultipleFlash->setFlash(__('You have rejected {0} as a partner for {1}', $partner['person']['display_name'], $me['person']['display_name']), 'success');
			return $this->redirect(array('action' => 'requests', $id));
		} else {
			$this->MultipleFlash->setFlash(__('The registration could not be saved.'), 'error');
			return $this->redirect(array('action' => 'requests', $id));
		}
	}

	function list_partner_wanted() {
		if (!empty($this->request->getQuery('tournament_id'))) {
			$this->request->getSession()->write('Tournaments.id', $this->request->getQuery('tournament_id'));
			return $this->redirect(array('action' => 'list_partner_wanted'));
		}

		if ($this->request->getQuery('nation_id') !== null) {
			if ($this->request->getQuery('nation_id') === 'all')
				$this->request->getSession()->delete('Nations.id');
			else
				$this->request->getSession()->write('Nations.id', $this->request->getQuery('nation_id'));
		}

		if ($this->request->getQuery('competition_id') !== null) {
			if ($this->request->getQuery('competition_id') === 'all') 
				$this->request->getSession()->delete('Competitions.id');
			else
				$this->request->getSession()->write('Competitions.id', $this->request->getQuery('competition_id'));
		}

		if ($this->request->getQuery('last_name') !== null) {
			$last_name = urldecode($this->request->getQuery('last_name'));

			if ($last_name == '*')
				$this->request->getSession()->delete('People.last_name');
			else
				$this->request->getSession()->write('People.last_name', str_replace('_', ' ', $last_name));
		}

		// The current tournament, must be set
		$tid = $this->request->getSession()->read('Tournaments.id');

		$conditions = array();

		$conditions['Registrations.tournament_id'] = $tid;

		// No constraint to nation, I want to see them all
		// Unless we are root and want to filter
		if ($this->request->getSession()->check('Nations.id'))
			$conditions['People.nation_id'] = $this->request->getSession()->read('Nations.id');

		// Player has entered one of the doubles events, not cancelled and has no partner
		$or = array(
			array('NOT Participants.double_cancelled', 'Participants.double_partner_id IS NULL'),
			array('NOT Participants.mixed_cancelled', 'Participants.mixed_partner_id IS NULL')
		);

		if ($this->request->getSession()->check('Competitions.id')) {
			$or[0]['Participants.double_id'] = $this->request->getSession()->read('Competitions.id');
			$or[1]['Participants.mixed_id'] = $this->request->getSession()->read('Competitions.id');
		} else {
			$or[0][] = 'Participants.double_ID IS NOT NULL';
			$or[1][] = 'Participants.mixed_ID IS NOT NULL';
		}

		// Filter for Name
		if ($this->request->getSession()->check('People.last_name'))
			$conditions['People.last_name COLLATE utf8_bin LIKE'] =  $this->request->getSession()->read('People.last_name') . '%';

		$conditions[] = array('OR' => $or);

		$this->paginate = array(
			'sortableFields' => [
				'People.display_name',
				'Nations.name',
				'Types.name',
				'People.extern_id',
				'Participants.start_no',
				'Registrations.modified'
			],
			'contain' => array(
				'People' => ['Nations'],
				'Types',
				'Participants' => array(
						'DoublePartners' => array(
							'Participants', 
							'People',
						),
						'MixedPartners' => array(
							'Participants',
							'People',
						)
				)
			),
			'conditions' => $conditions,
			'order' => ['People.display_name' => 'ASC']
		);
		
		$registrations = $this->paginate();
		
		$this->set('registrations', $registrations);

		// Count the number of competitions per type to hide columns
		$this->loadModel('Competitions');
		$count = array();
		$conditions = array('tournament_id' => $tid);

		if ($this->request->getSession()->check('Competitions.id'))
			$conditions['Competitions.id'] = $this->request->getSession()->read('Competitions.id');
	
		$count['D'] = $this->Competitions->find('all', array(
			'conditions' => array_merge($conditions, array('type_of = "D"'))
		))->count();
		$count['X'] = $this->Competitions->find('all', array(
			'conditions' => array_merge(array($conditions, 'type_of = "X"'))
		))->count();

		$this->set('count', $count);

		$this->loadModel('Nations');
		$this->loadModel('People');
		$nations = $this->Nations->find('list', array(
			'fields' => array('Nations.id', 'Nations.name'),
			'conditions' => [
				'id IN' => $this->People->find()->select('nation_id')
			],
			'order' => array('name')
		))->toArray();

		$this->set('nations', $nations);
		$this->set('nation_id', $this->request->getSession()->read('Nations.id'));

		// For references in double / mixed partners
		$this->loadModel('Nations');
		$this->set('allNations', $this->Nations->find('list', array(
			'fields' => array('id', 'name')
		))->toArray());

		$this->loadModel('Types');
		$this->set('types', $this->Types->find('list', array('fields' => array('id', 'name')))->toArray());
		$this->set('type_id', $this->request->getSession()->check('Types.id') ? $this->request->getSession()->read('Types.id') : false);

		$this->loadModel('Competitions');
		$this->set('competitions', $this->Competitions->find('list', array(
			'order' => ['name' => 'ASC'], 
			'fields' => array('id', 'name'), 
			'conditions' => array(
				'tournament_id' => $tid, 
				'type_of IN' => array('D', 'X')
			)
		))->toArray());
		$this->set('competition_id', $this->request->getSession()->check('Competitions.id') ? $this->request->getSession()->read('Competitions.id') : false);

		$this->loadModel('People');
		$last_name = $this->request->getSession()->read('People.last_name');
		$allchars = array();

		if (!$last_name)
			$last_name = '';

		for ($count = 0; $count <= mb_strlen($last_name); $count++) {
			$conditions = array();
			if ($this->request->getSession()->check('Nations.id'))
				$conditions['People.nation_id'] = $this->request->getSession()->read('Nations.id');

			if ($this->request->getSession()->check('People.sex'))
				$conditions['People.sex'] = $this->request->getSession()->read('People.sex');

			if ($count > 0)
				$conditions['People.last_name COLLATE utf8_bin LIKE'] = mb_substr($last_name, 0, $count) . '%';

			$tmp = $this->People->find('all', array(
				'fields' => ['firstchar' => 'DISTINCT LEFT(last_name COLLATE utf8_bin, ' . ($count + 1) . ')'],
				'conditions' => $conditions,
				'order' => ['firstchar COLLATE utf8_unicode_ci' => 'ASC']
			))->toArray();

			$tmp = Hash::extract($tmp, '{n}.firstchar');
			
			// Set::extract may return null
			if ($tmp === null)
				$tmp = array();

			// Make sure any selected sequence of characters is in the list
			if (mb_strlen($last_name) > $count && !in_array(mb_substr($last_name, 0, $count + 1), $tmp))
				$tmp[] = mb_substr($last_name, 0, $count + 1);

			// sort($tmp);

			$allchars[] = $tmp;
		}

		$this->set('allchars', $allchars);

		$this->set('last_name', $this->request->getSession()->read('People.last_name'));

		$this->render('index');
	}


	function _sortCompetitions($a, $b) {
		$types = array('S' => 1, 'D' => 2, 'X' => 3, 'T' => 4);
		$sex   = array('M' => 1, 'F' => 2, 'X' => 3);
		
		$ret = $types[$a['type_of']] - $types[$b['type_of']];
		if ($ret == 0)
			$ret = $sex[$a['sex']] - $sex[$b['sex']];
		if ($ret == 0)
			$ret = $a['born'] - $b['born'];
		return $ret;
	}	

	function count() {
		if (!empty($this->request->getQuery('tournament_id'))) {
			$this->request->getSession()->write('Tournaments.id', $this->request->getQuery('tournament_id'));
			return $this->redirect(array('action' => 'index'));
		}

		if ($this->request->getQuery('nation_id') !== null) {
			if ($this->request->getQuery('nation_id') === 'all')
				$this->request->getSession()->delete('Nations.id');
			else
				$this->request->getSession()->write('Nations.id', $this->request->getQuery('nation_id'));
		}
		
		if ($this->request->getQuery('sex') !== null) {
			if ($this->request->getQuery('sex') === 'all')
				$this->request->getSession()->delete('Competitions.sex');
			else {
				$this->request->getSession()->write('Competitions.sex', $this->request->getQuery('sex'));
				
				// Type X and Sex not X cannot coexist
				if ( $this->request->getSession()->read('Competitions.sex') === 'X' && 
						$this->request->getSession()->read('Competitions.type_of') !== 'X') 
					$this->request->getSession()->delete('Competitions.type_of');
				
				if ($this->request->getSession()->read('Competitions.type_of') === 'X' &&
						$this->request->getSession()->read('Competitions.sex') !== 'X')
					$this->request->getSession()->delete('Competitions.type_of');
			}
		}

		if ($this->request->getQuery('type_of') !== null) {
			if ($this->request->getQuery('type_of') == 'all')
				$this->request->getSession()->delete('Competitions.type_of');
			else {
				$this->request->getSession()->write('Competitions.type_of', $this->request->getQuery('type_of'));
				
				// Type X and Sex not X cannot coexist
				if ( $this->request->getSession()->read('Competitions.type_of') === 'X' && 
						$this->request->getSession()->read('Competitions.sex') !== 'X') 
					$this->request->getSession()->delete('Competitions.sex');
				
				if ($this->request->getSession()->read('Competitions.sex') === 'X' &&
						$this->request->getSession()->read('Competitions.type_of') !== 'X')
					$this->request->getSession()->delete('Competitions.sex');
			}
		}

		if ($this->request->getQuery('partner') !== null) {
			if ($this->request->getQuery('partner') == 'all')
				$this->request->getSession()->delete('Participants.partner');
			else
				$this->request->getSession()->write('Participants.partner', $this->request->getQuery('partner'));
		}

		$tid = $this->request->getSession()->read('Tournaments.id');

		$this->loadModel('Competitions');

		$conditions = array('Competitions.tournament_id' => $tid);

		if ($this->request->getSession()->check('Competitions.type_of'))
			$conditions['Competitions.type_of'] = $this->request->getSession()->read('Competitions.type_of');

		if ($this->request->getSession()->check('Competitions.sex'))
			$conditions['Competitions.sex'] = $this->request->getSession()->read('Competitions.sex');
		
		$competitions = $this->Competitions->find('all', array(
			'fields' => array('id', 'name', 'description', 'type_of', 'born', 'sex'),
			'conditions' => $conditions,
		))->toArray();

		uasort($competitions, array($this, '_sortCompetitions')); 

		$this->set('competitions', $competitions);

		$conditions = 'Nations.id IN (' . 
				'SELECT DISTINCT nation_id FROM registrations r INNER JOIN people p ON r.person_id = p.id ' .
			    ' WHERE r.tournament_id = ' . $tid . ' AND r.type_id = ' . TypesTable::getPlayerId() . ')';

		// if ($this->request->getSession()->check('Nations.id'))
		// 	$conditions = 'Nations.id = ' . $this->request->getSession()->read('Nations.id');

		$this->loadModel('Nations');
		$nations = $this->Nations->find('all', array(
			'order' => ['description' => 'ASC'],
			'conditions' => $conditions
		));

		$types = array('S' => 'single', 'D' => 'double', 'X' => 'mixed', 'T' => 'team');

		$nationCounts = array();

		foreach ($nations as $n) {
			$nationCounts[$n['id']] = array(
				'Nation' => $n, 
				'Count' => array('total' => 0)
			);
		}

		// And Total
		$nationCounts[0] = array(
			'Nation' => array(
				'name' => __('Total'), 
				'description' => __('Total'), 
				'id' => 0
			), 
			'Count' => array('total' => 0)
		);

		foreach ($competitions as $c) {
			$conditions = array();
			$conditions['Registrations.tournament_id'] = $tid;
			$conditions['Participants.' . $types[$c['type_of']] . '_id'] = $c['id'];
			$conditions[] = 'NOT Participants.' . $types[$c['type_of']] . '_cancelled';
			$conditions[] = 'NOT Participants.cancelled';
			
			if ($this->request->getSession()->check('Participants.partner')) {
				$partner = $this->request->getSession()->read('Participants.partner');
				
				$or = [];
				
				if ($c['type_of'] == 'D') {
					$partnerDoubles = [];

					if (in_array($partner, ['wanted', 'requested', 'multiple']))
						$partnerDoubles[] = 'Participants.double_partner_id IS NULL';
					else
						$partnerDoubles[] = 'Participants.double_partner_id IS NOT NULL';

					if (in_array($partner, ['requested', 'multiple']))
						$partnerDoubles[] = 'Registrations.id IN (SELECT double_partner_id FROM participants p WHERE NOT p.cancelled AND NOT p.double_cancelled)';

					if ($partner === 'multiple')
						$partnerDoubles[] = '(SELECT COUNT(double_partner_id) FROM participants p WHERE p.double_partner_id = Registrations.id AND NOT p.cancelled AND NOT p.double_cancelled) > 1';

					if ($partner === 'unconfirmed')
						$partnerDoubles[] = '(SELECT double_partner_id FROM participants p WHERE p.registration_id = Participants.double_partner_id AND NOT p.cancelled AND NOT p.double_cancelled) IS NULL';

					if ($partner === 'confirmed')
						$partnerDoubles[] = 'Registrations.id IN (SELECT double_partner_id FROM participants p WHERE p.registration_id = Participants.double_partner_id AND NOT p.cancelled AND NOT p.double_cancelled)';

					$or[] = $partnerDoubles;
				}

				if ($c['type_of'] == 'X') {
					$partnerMixed = [];

					if (in_array($partner, ['wanted', 'requested', 'multiple']))
						$partnerMixed[] = 'Participants.mixed_partner_id IS NULL';
					else
						$partnerMixed[] = 'Participants.mixed_partner_id IS NOT NULL';

					if (in_array($partner, ['requested', 'multiple']))
						$partnerDoubles[] = 'Registrations.id IN (SELECT mixed_partner_id FROM participants p WHERE NOT p.cancelled AND NOT p.mixed_cancelled)';

					if ($partner === 'multiple')
						$partnerDoubles[] = '(SELECT COUNT(mixed_partner_id) FROM participants p WHERE p.double_partner_id = Registrations.id AND NOT p.cancelled AND NOT p.mixed_cancelled) > 1';

					if ($partner === 'unconfirmed')
						$partnerMixed[] = '(SELECT mixed_partner_id FROM participants p WHERE p.registration_id = Participants.mixed_partner_id AND NOT p.cancelled AND NOT p.mixed_cancelled) IS NULL';

					if ($partner === 'confirmed')
						$partnerMixed[] = 'Registrations.id IN (SELECT mixed_partner_id FROM participants p WHERE p.registration_id = Participants.mixed_partner_id AND NOT p.cancelled AND NOT p.mixed_cancelled)';

					$or[] = $partnerMixed;
				}
				
				if (count($or))
					$conditions[] = ['OR' => $or];				
			}
			
			if ($this->request->getSession()->check('Nations.id'))
				$conditions['People.nation_id'] = $this->request->getSession()->read('Nations.id');

			$counts = $this->Registrations->find('all', array(
				'conditions' => $conditions,
				'group' => 'People.nation_id',
				'contain' => array('Participants', 'People'),
				'fields' => array('nation_id' => 'People.nation_id', 'count' => 'COUNT(person_id)')
			));

			foreach ($nationCounts as &$n) {
				$n['Count'][$c['id']] = 0;
			}

			if (!$counts)
				continue;

			foreach ($counts as $count) {
				$nationCounts[$count['nation_id']]['Count'][$c['id']] = $count['count'];
				if ($c['type_of'] == 'T')
					$nationCounts[0]['Count'][$c['id']] += ($count['count'] > 0 ? 1 : 0);
				else
					$nationCounts[0]['Count'][$c['id']] += $count['count'];
			}
		}

		$this->loadModel('Types');

		$conditions = array(
			'Registrations.tournament_id' => $tid,
			'Registrations.type_id' => TypesTable::getPlayerId(),
			'NOT Participants.cancelled'
		);

		if ($this->request->getSession()->check('Competitions.type_of')) {
			$conditions[] = 'Participants.' . $types[$this->request->getSession()->read('Competitions.type_of')] . '_id IS NOT NULL';
			$conditions[] = 'NOT Participants.' . $types[$this->request->getSession()->read('Competitions.type_of')] . '_cancelled';

			if ($this->request->getSession()->check('Participants.partner')) {
				if ($this->request->getSession()->read('Competitions.type_of') == 'D') {
					if ($c['type_of'] == 'D') {
						if ($partner == 'wanted')
							$conditions[] = 'Participants.double_partner_id IS NULL';
						else
							$conditions[] = 'Participants.double_partner_id IS NOT NULL';
						
						if ($partner == 'unconfirmed')
							$conditions[] = '(SELECT double_partner_id FROM participants p WHERE p.registration_id = Participants.double_partner_id AND NOT p.cancelled AND NOT p.double_cancelled) IS NULL';
						else if ($partner == 'confirmed')
							$conditions[] = '(SELECT double_partner_id FROM participants p WHERE p.registration_id = Participants.double_partner_id AND NOT p.cancelled AND NOT p.double_cancelled) IS NOT NULL';
					}
				} else if ($this->request->getSession()->read('Competitions.type_of') == 'X') {
					if ($c['type_of'] == 'X') {
						if ($partner == 'wanted')
							$conditions[] = 'Participants.mixed_partner_id IS NULL';
						else
							$conditions[] = 'Participants.mixed_partner_id IS NOT NULL';
						
						if ($partner == 'unconfirmed')
							$conditions[] = '(SELECT mixed_partner_id FROM participants p WHERE p.registration_id = Participants.mixed_partner_id AND NOT p.cancelled AND NOT p.mixed_cancelled) IS NULL';
						else if ($partner == 'confirmed')
							$conditions[] = '(SELECT mixed_partner_id FROM participants p WHERE p.registration_id = Participants.mixed_partner_id AND NOT p.cancelled AND NOT p.mixed_cancelled) IS NOT NULL';
					}				
				}
			}
		}
		
		if ($this->request->getSession()->check('Competitions.sex')) {
			$ids = ['S' => [0], 'D' => [0], 'X' => [0], 'T' => [0]];

			foreach ($competitions as $c) {
				if ($c['sex'] === $this->request->getSession()->read('Competitions.sex'))
					$ids[$c['type_of']][] = $c['id'];
			}
					
			$conditions[] = array('OR' => array(
				array('Participants.single_id IS NOT NULL', 'NOT Participants.single_cancelled', 'Participants.single_id IN' => $ids['S']),
				array('Participants.double_id IS NOT NULL', 'NOT Participants.double_cancelled', 'Participants.double_id IN' => $ids['D']),
				array('Participants.mixed_id IS NOT NULL', 'NOT Participants.mixed_cancelled', 'Participants.mixed_id IN' => $ids['X']),
				array('Participants.team_id IS NOT NULL', 'NOT Participants.team_cancelled', 'Participants.team_id IN' => $ids['T']),
			));
		}

		if ($this->request->getSession()->check('Nations.id'))
			$conditions['People.nation_id'] = $this->request->getSession()->read('Nations.id');

		$counts = $this->Registrations->find('all', array(
				'conditions' => $conditions,
				'group' => 'People.nation_id',
				'contain' => array('Participants', 'People'),
				'fields' => array('nation_id' => 'People.nation_id', 'count' => 'COUNT(person_id)')
		));

		foreach ($counts as $count) {
			$nationCounts[$count['nation_id']]['Count']['total'] = $count['count'];
			$nationCounts[0]['Count']['total'] += $count['count'];
		} 

		$this->set('nationCounts', $nationCounts);

		// Filter Association
		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'name'), 
			'order' => 'name')
		)->toArray());
		$this->set('nation_id', $this->request->getSession()->read('Nations.id'));

		$this->set('partner', $this->request->getSession()->read('Participants.partner'));
		
		$types = $this->Competitions->find('all', array(
			'conditions' => array('tournament_id' => $tid),
			'fields' => array('type_of' => 'DISTINCT(type_of)')
		))->toArray();
		
		$this->set('types',  Hash::extract($types, '{n}.type_of'));
		$this->set('type_of', $this->request->getSession()->read('Competitions.type_of'));
		
		$sexes = $this->Competitions->find('all', array(
			'conditions' => array('tournament_id' => $tid),
			'fields' => array('sex' => 'DISTINCT(sex)'),
			'order' => array('sex' => 'ASC')
		))->toArray();
		
		$this->set('sexes', Hash::extract($sexes, '{n}.sex'));
		$this->set('sex', $this->request->getSession()->read('Competitions.sex'));
	}

	function export_count() {
		// Disable debug output
		Configure::write('debug', false);

		$this->count();
	}

	var $ageCategories = array();
	
	var $collator = null;

	// Compare two participants $a and $b by selected order $what
	function _compareBy($what, $a, $b) {
		if ($a == null || $b == null)
			return false;

		$sex = array('F' => 1, 'M' => 2);

		switch ($what) {
			case 1 : // Men before women
				return -($sex[$a['person']['sex']] - $sex[$b['person']['sex']]);
			case 2 : // Women before men
				return +($sex[$a['person']['sex']] - $sex[$b['person']['sex']]);
			case 3 : // Association
				return strcmp($a['person']['nation']['name'], $b['person']['nation']['name']);
			case 4 : // Name
				if ($this->collator === null)
					$this->collator = collator_create('en_US');  
				
				$ret = collator_compare($this->collator, $a['person']['last_name'], $b['person']['last_name']);
				if ($ret != 0)
					return $ret;

				return collator_compare($this->collator, $a['person']['first_name'], $b['person']['first_name']);
			case 5 : // Age category asc
			case 6 : // Age cagegory desc
				$bornA = 0;
				$bornB = 0;
				$yA = $a['person']['born'];
				$yB = $b['person']['born'];
				for ($idx = 0; $idx < count($this->ageCategories); $idx++) {
					if ( ($bornA = $this->ageCategories[$idx]) >= $yA )
						break;
				}

				for ($idx = 0; $idx < count($this->ageCategories); $idx++) {
					if ( ($bornB = $this->ageCategories[$idx]) >= $yB )
						break;
				}

				return ($what == 6 ? $bornA - $bornB : -($bornA - $bornB));
				
			case 7 : // Extern ID
				return strcmp($a['person']['extern_id'], $b['person']['extern_id']);
		}

		return 0;
	}

	// Compare functions to sort list of participants
	function _compareForAssignNumbers($a, $b) {
		$ret = 0;
		if ($ret == 0)
			$ret = $this->_compareBy($this->request->getData('first'), $a, $b);
		if ($ret == 0)
			$ret = $this->_compareBy($this->request->getData('second'), $a, $b);
		if ($ret == 0)
			$ret = $this->_compareBy($this->request->getData('third'), $a, $b);

		return $ret;
	}
					

	function assign_numbers() {
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		}

		if (array_key_exists('cancel', $this->request->getData())) {
			return $this->redirect(array('action' => 'index'));
		} 		
		
		$tid = $this->request->getSession()->read('Tournaments.id');

		if ($this->request->is(['get'])) {
			$this->set('sort_options', array(
				1 => __('Men before Women'),
				2 => __('Women before Men'),
				3 => __('Association'),
				4 => __('Name'),
				5 => __('Age category ascending'),
				6 => __('Age category descending'),
				7 => __('Extern ID')
			));

			$warnOverwrite = $this->Registrations->find('all', array(
				'contain' => array('Participants'),
				'conditions' => [
					'Participants.start_no > 0', 
					'Registrations.tournament_id' => $tid
				]
			))->count() > 0;

			$this->set('warnOverwrite', $warnOverwrite);
		}  
		
		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->getData();
			
			// Increase time limit
			set_time_limit(60);
			
			// For later use
			$this->loadModel('Participants');

			$participants = $this->Registrations->find('all', array(
				'fields' => array(
					'Participants.id',         // To save data
					'Participants.start_no',   // To save data
					'Registrations.cancelled', // To filter players
					'People.extern_id',       // Sort by Extern ID
					'People.last_name',       // Sort by Name
					'People.first_name',      // Sort by Name
					'People.sex',             // Sort by Sex
					'People__born' => 'YEAR(People.dob)', // Sort by Age category
					'Nations.name'             // Sort by Assocation
				),
				'conditions' => array(
					'Registrations.tournament_id' => $tid,
					'Registrations.type_id' => TypesTable::getPlayerId()
				),
				'contain' => [
					'Participants',
					'People'=> ['Nations']
				]
			))->toArray();

			$this->loadModel('Competitions');
			$tmp = $this->Competitions->find('all', array(
				'fields' => array('born' => 'DISTINCT born'),
				'conditions' => array('tournament_id' => $tid),
				'order' => ['Competitions.born' =>  'ASC']
			))->toArray();

			$this->ageCategories = Hash::extract($tmp, 'born');

			$last = null;

			$nr = $data['offset'] - 1;

			usort($participants, array($this, '_compareForAssignNumbers'));

			$toSave = array();

			foreach ($participants as $p) {
				// Clear all numbers if no order is selected
				if ($data['first'] == 0) {
					$toSave[] = array(
						'id' => $p['participant']['id'],
						'start_no' => null
					);

					continue;
				}

				// Clear number of cancelled participants
				if ( !empty($p['cancelled']) ) {
					$toSave[] = array(
						'id' => $p['participant']['id'], 
						'start_no' => null
					);
						
					continue;
				}

				// Group numbers: Calculate range reserved for that group
				$count = 0;

				if ($this->_compareBy($data['first'], $last, $p))
					$count = $data['first_grouping'];
				else if ($this->_compareBy($data['second'], $last, $p))
					$count = $data['second_grouping'];
				else if($this->_compareBy($data['third'], $last, $p))
					$count = $data['third_grouping'];
				else if($this->_compareBy($data['fourth'], $last, $p))
					$count = $data['fourth_grouping'];

				// Increment number to next multiple of count
			    if ($count > 0) 
					$nr = floor( ($nr + $count - 1) /	$count) * $count;

				// And always start with +1
				$nr += 1;

				$toSave[] = array(
					'id' => $p['participant']['id'], 
					'start_no' => $nr
				);
				
				$last = $p;
			}

			$this->Participants->saveAll($this->Participants->newEntities($toSave));

			return $this->redirect(array('action' => 'index'));
		}				
	}


	// Export as csv
	function export() {
		if (!empty($this->request->getQuery('tournament_id'))) {
			$this->request->getSession()->write('Tournaments.id', $this->request->getQuery('tournament_id'));
			return $this->redirect(array('action' => 'index'));
		}

		ini_set('memory_limit', '512M');

		// Disable debug output
		// Configure::write('debug', false);

		$tid = $this->request->getSession()->read('Tournaments.id');

		$this->loadModel('Nations');
		$nations = $this->Nations->find('list', array('fields' => array('id', 'name')))->toArray();
		$nations[0] = '';

		$this->loadModel('Shop.Articles');
		$articles = $this->Articles->find('list', array(
			'fields' => array('name', 'id'),
			'conditions' => array('tournament_id' => $tid)
		))->toArray();
				
		$conditions = array();
		$conditions['Registrations.tournament_id'] = $tid;

		if ($this->request->getSession()->check('Nations.id'))
			$conditions['People.nation_id'] = $this->request->getSession()->read('Nations.id');

		if ($this->request->getSession()->check('Groups.type_ids'))
			$conditions['Registrations.type_id IN'] = explode(',', $this->request->getSession()->read('Groups.type_ids'));

		if ($this->request->getSession()->check('Types.id')) 
			$conditions['Registrations.type_id'] = $this->request->getSession()->read('Types.id');

		if ($this->request->getSession()->check('Users.id'))
			$conditions['People.user_id'] = $this->request->getSession()->read('Users.id');

		if ($this->request->getSession()->check('Competitions.id')) {
			$cid = $this->request->getSession()->read('Competitions.id');

			$this->loadModel('Competitions');
			$type_of = $this->Competitions->fieldByConditions('type_of', array('id = ' => $cid));

			switch ($type_of) {
				case 'S' :
					$conditions['Participants.single_id'] = $cid;
					break;

				case 'D' :
					$conditions['Participants.double_id'] = $cid;
					break;

				case 'X' :
					$conditions['Participants.mixed_id'] = $cid;
					break;

				case 'T' :
					$conditions['Participants.team_id'] = $cid;
					break;
			}
		}
		
		if ($this->request->getSession()->check('People.last_name'))
			$conditions['People.last_name LIKE'] = $this->request->getSession()->read('People.last_name') . '%';

		if ($this->request->getSession()->check('Registrations.cancelled')) {
			if (empty($this->request->getSession()->read('Registrations.cancelled')))
				$conditions[] = 'Registrations.cancelled IS NULL';
			else
				$conditions[] = 'Registrations.cancelled IS NOT NULL';
		}
		
		if ($this->request->getSession()->check('Participants.partner') && empty($this->request->getSession()->read('Registrations.cancelled'))) {
			$this->loadModel('Competitions');
			
			$partner = $this->request->getSession()->read('Participants.partner');

			$or = [];
			
			if ($this->Competitions->find('all', array(
						'conditions' => [
							'tournament_id' => $tid,
							'type_of' => 'D'
						]
					))->count() > 0) {
				
				$partnerDoubles = [];
				
				if (in_array($partner, ['wanted', 'requested', 'multiple']))
					$partnerDoubles[] = 'Participants.double_partner_id IS NULL';
				else
					$partnerDoubles[] = 'Participants.double_partner_id IS NOT NULL';
				
				if (in_array($partner, ['requested', 'multiple']))
					$partnerDoubles[] = 'Registrations.id IN (SELECT double_partner_id FROM participants p WHERE NOT p.cancelled AND NOT p.double_cancelled)';
				
				if ($partner === 'multiple')
					$partnerDoubles[] = '(SELECT COUNT(double_partner_id) FROM participants p WHERE p.double_partner_id = Registrations.id AND NOT p.cancelled AND NOT p.double_cancelled) > 1';
				
				if ($partner === 'unconfirmed')
					$partnerDoubles[] = '(SELECT double_partner_id FROM participants p WHERE p.registration_id = Participants.double_partner_id AND NOT p.cancelled AND NOT p.double_cancelled) IS NULL';
				
				if ($partner === 'confirmed')
					$partnerDoubles[] = 'Registrations.id IN (SELECT double_partner_id FROM participants p WHERE p.registration_id = Participants.double_partner_id AND NOT p.cancelled AND NOT p.double_cancelled)';
				
				$or[] = $partnerDoubles;
			}
			
			if ($this->Competitions->find('all', array(
						'conditions' => [
							'tournament_id' => $tid,
							'type_of' => 'X'
						]
					))->count() > 0) {
				
				$partnerMixed = [];
				
				if (in_array($partner, ['wanted', 'requested', 'multiple']))
					$partnerMixed[] = 'Participants.mixed_partner_id IS NULL';
				else
					$partnerMixed[] = 'Participants.mixed_partner_id IS NOT NULL';
				
				if (in_array($partner, ['requested', 'multiple']))
					$partnerDoubles[] = 'Registrations.id IN (SELECT mixed_partner_id FROM participants p WHERE NOT p.cancelled AND NOT p.mixed_cancelled)';
				
				if ($partner === 'multiple')
					$partnerDoubles[] = '(SELECT COUNT(mixed_partner_id) FROM participants p WHERE p.double_partner_id = Registrations.id AND NOT p.cancelled AND NOT p.mixed_cancelled) > 1';
				
				if ($partner === 'unconfirmed')
					$partnerMixed[] = '(SELECT mixed_partner_id FROM participants p WHERE p.registration_id = Participants.mixed_partner_id AND NOT p.cancelled AND NOT p.mixed_cancelled) IS NULL';
				
				if ($partner === 'confirmed')
					$partnerMixed[] = 'Registrations.id IN (SELECT mixed_partner_id FROM participants p WHERE p.registration_id = Participants.mixed_partner_id AND NOT p.cancelled AND NOT p.mixed_cancelled)';
				
				$or[] = $partnerMixed;
			}
			
			if (count($or))
				$conditions[] = ['OR' => $or];
		}			
			
		if ($this->request->getSession()->read('Participants.age_category') == 'different')  {
			$conditions[] = array(
				array('OR' => array(
					array(
						'Participants.single_id IS NOT NULL',
						'(SELECT born FROM competitions WHERE id = single_id) <> '.
						'(SELECT MIN(born) FROM competitions ' .
						'  WHERE tournament_id = Registrations.tournament_id ' .
						'    AND type_of = \'S\' AND born >= YEAR(People.dob))'
					),
					array(
						'Participants.double_id IS NOT NULL',
						'(SELECT born FROM competitions WHERE id = double_id) <> '.
						'(SELECT MIN(born) FROM competitions ' .
						'  WHERE tournament_id = Registrations.tournament_id ' .
						'    AND type_of = \'D\' AND born >= YEAR(People.dob))'
					),
					array(
						'Participants.mixed_id IS NOT NULL',
						'(SELECT born FROM competitions WHERE id = mixed_id) <> '.
						'(SELECT MIN(born) FROM competitions ' .
						'  WHERE tournament_id = Registrations.tournament_id ' .
						'    AND type_of = \'X\' AND born >= YEAR(People.dob))'
					),
					array(
						'Participants.team_id IS NOT NULL',
						'(SELECT born FROM competitions WHERE id = team_id) <> '.
						'(SELECT MIN(born) FROM competitions ' .
						'  WHERE tournament_id = Registrations.tournament_id ' .
						'    AND type_of = \'T\' AND born >= YEAR(People.dob))'
					)
				))
			);
		}
		
		// Select invoices
		$this->loadModel('Shop.OrderArticles');
		$invoices = $this->OrderArticles->find('all', array(
			'fields' => ['OrderArticles.person_id', 'Orders.invoice'],
			'contain' => ['Orders'],
			'conditions' => [
				// 'OrderArticles.cancelled IS NULL',
				'OrderArticles.article_id IN' => [$articles['PLA'], $articles['ACC']]
			]
		));
		
		$this->set('invoices', Hash::combine($invoices->toArray(), '{n}.person_id', '{n}.order.invoice'));
		
		$data = $this->Registrations->find('all', array(
			'conditions' => $conditions,
			'contain' => [
				'Participants',
				'People' 
			]
 		));

		$registrations = array();

		foreach ($data as $d) {
			$registrations[$d['id']] = $d;
		}

		$this->loadModel('Competitions');
		$competitions = $this->Competitions->find('list', array(
			'fields' => array('id', 'name'),
			'conditions' => 'Competitions.tournament_id = ' . $tid
		))->toArray();

		$dummyPerson = array(
			'person' => array(
				'first_name' => '',
				'last_name' => '',
				'nation_id' => 0,
				'extern_id' => ''
			),
			'participant' => array(
				'start_no' => '',
				'double_partner_id' => null,
				'mixed_partner_id' => null,
				'extern_id' => ''
			),
			'confirmed' => false
		);

		$data = array();

		foreach ($registrations as $r) {
			if (empty($r['participant'])) {
				$data[] = $r;
				continue;
			}

			// Set competitions
			if ($r['participant']['single_id'] && !$r['participant']['single_cancelled'])
				$r['Single'] = $competitions[$r['participant']['single_id']];
			else
				$r['Single'] = '';

			if ($r['participant']['double_id'] && !$r['participant']['double_cancelled'])
				$r['Double'] = $competitions[$r['participant']['double_id']];
			else
				$r['Double'] = '';

			if ($r['participant']['mixed_id'] && !$r['participant']['mixed_cancelled'])
				$r['Mixed'] = $competitions[$r['participant']['mixed_id']];
			else
				$r['Mixed'] = '';

			if ($r['participant']['team_id'] && !$r['participant']['team_cancelled'])
				$r['Team'] = $competitions[$r['participant']['team_id']];
			else
				$r['Team'] = '';

			// Set partner
			if ($r['participant']['double_partner_id'] && !$r['participant']['double_cancelled']) {
				$double_partner_id = $r['participant']['double_partner_id'];
				
				if (empty($registrations[$double_partner_id]))
					$registrations[$double_partner_id] = $this->Registrations->find('all', array(
						'conditions' => array('Registrations.id' => $double_partner_id),
						'contain' => ['People', 'Participants']
					))->first();
				
				$double_partner = $registrations[$double_partner_id];

				$r['DoublePartner'] = array(
					'person' => $double_partner['person'],
					'participant' => $double_partner['participant'],
					'confirmed' => $r['id'] == $double_partner['participant']['double_partner_id'],
				);
			} else
				$r['DoublePartner'] = $dummyPerson;

			if ($r['participant']['mixed_partner_id'] && !$r['participant']['mixed_cancelled']) {
				$mixed_partner_id = $r['participant']['mixed_partner_id'];
				
				if (empty($registrations[$mixed_partner_id]))
					$registrations[$mixed_partner_id] = $this->Registrations->find('all', array(
						'conditions' => array('Registrations.id' => $mixed_partner_id),
						'contain' => ['People', 'Participants']
					))->first();
				
				$mixed_partner = $registrations[$mixed_partner_id];

				$r['MixedPartner'] = array(
					'person' => $mixed_partner['person'],
					'participant' => $mixed_partner['participant'],
					'confirmed' => $r['id'] == $mixed_partner['participant']['mixed_partner_id'],
				);
			} else
				$r['MixedPartner'] = $dummyPerson;

			$data[] = $r;
		}

		$this->set('nations', $nations);
		$this->set(compact('data'));

		$this->loadModel('Types');
		$this->set('types', $this->Types->find('list', array('fields' => array('id', 'name')))->toArray());
	}
	
	
	function export_participants() {
		$this->loadModel('Shop.OrderArticles');
		$this->loadModel('Shop.Articles');
		$this->loadModel('People');
		
		$tid = $this->request->getSession()->read('Tournaments.id');
		
		$articles = $this->Articles->find('all', array(
			'conditions' => array(
				'tournament_id' => $tid
			),
			'order' => array('sort_order' => 'ASC')
		));
		
		$uid = $this->Auth->user('id');
		
		$this->OrderArticles->belongsTo('People');
		
		$tmp = $this->OrderArticles->find('all', array(
			'contain' => array('People'),
			'conditions' => array(
				'People.user_id' => $uid
			)
		));
		
		$items = array();
		
		foreach ($tmp as $t) {
			$id = $t['person_id'];
			if (empty($items[$id]))
				$items[$id] = array();
			$items[$id][] = $t;
		}
		
		$conditions = array();
		if (!UsersTable::hasRootPrivileges($this->_user))
			$conditions['People.user_id'] = $uid;
		else if ($this->request->getSession()->check('Users.id'))
			$conditions['People.user_id'] = $this->request->getSession()->read('Users.id');
		
		if ($this->request->getSession()->check('Nations.id'))
			$conditions['People.nation_id'] = $this->request->getSession()->read('Nations.id');

		if ($this->request->getSession()->check('Types.id')) 
			$conditions['Registrations.type_id'] = $this->request->getSession()->read('Types.id');
	
		if ($this->request->getSession()->check('Competitions.id')) {
			$cid = $this->request->getSession()->read('Competitions.id');

			$this->loadModel('Competitions');
			$type_of = $this->Competitions->fieldByConditions('type_of', array('id = ' => $cid));

			switch ($type_of) {
				case 'S' :
					$conditions['Participants.single_id'] = $cid;
					break;

				case 'D' :
					$conditions['Participants.double_id'] = $cid;
					break;

				case 'X' :
					$conditions['Participants.mixed_id'] = $cid;
					break;

				case 'T' :
					$conditions['Participants.team_id'] = $cid;
					break;
			}
		}

		if ($this->request->getSession()->check('Registrations.cancelled')) {
			if (empty($this->request->getSession()->read('Registrations.cancelled')))
				$conditions[] = 'Registrations.cancelled IS NULL';
			else
				$conditions[] = 'Registrations.cancelled IS NOT NULL';
		}

		// Filter for Name
		if ($this->request->getSession()->check('People.last_name'))
			$conditions['People.last_name COLLATE utf8_bin LIKE '] = $this->request->getSession()->read('People.last_name') . '%';

		$registrations = $this->Registrations->find('all', array(
			// 'contain' => array('People', 'Participants'),
			'conditions' => $conditions,
			'contain' => [
				'People',
				'Participants' => [
					'DoublePartners' => ['People'],
					'MixedPartners' => ['People']
				]
			]
		));
				
		foreach ($registrations as $r) {
			if (empty($items[$r['person']['id']]))
				$r['OrderArticle'] = array();
			else
				$r['OrderArticle'] = $items[$r['person']['id']];
		}				
		
		$this->set('articles', $articles);
		$this->set('registrations', $registrations);

		$this->loadModel('Nations');
		$nations = $this->Nations->find('list', array('fields' => array('id', 'name')))->toArray();
		$nations[0] = '';
		$this->set('nations', $nations);

		$this->loadModel('Types');
		$this->set('types', $this->Types->find('list', array('fields' => array('id', 'name')))->toArray());
		
		$this->loadModel('Competitions');
		$this->set('competitions', $this->Competitions->find('list', array('fields' => array('id', 'name')))->toArray());
	}

	// Print accreditation settings
	function print_accreditation_settings() {
		if ($this->request->getData('cancel') !== null)
			return $this->redirect(array('action' => 'index'));

		$this->loadModel('Tournaments');
		$tid = $this->request->getSession()->read('Tournaments.id');

		$dirname = WWW_ROOT . $this->Tournaments->fieldByConditions('name', ['id' => $tid]);

		$data = $this->request->getData();
		
		if (empty($data)) {
			$data = array();

			if (file_exists($dirname . DS . 'css' . DS . 'print_accreditation.css'))
				$data['css'] = file_get_contents($dirname . DS . 'css' . DS . 'print_accreditation.css');
			else
				$data['css'] = file_get_contents(WWW_ROOT . 'css' . DS . 'print_accreditation.css');
		} else {
			if (!is_dir($dirname))
				mkdir($dirname);

			if (!is_dir($dirname . DS . 'css'))
				mkdir($dirname . DS . 'css');

			if (!is_dir($dirname . DS . 'img'))
				mkdir($dirname . DS . 'img');

			$file = $data['logo']['tmp_name'];
			if (is_uploaded_file($file)) {
				list($width, $height, $imagetype) = getimagesize($file);

				switch ($imagetype) {
					case 1 :
						$src = imagecreatefromgif($file);
						break;

					case 2 :
						$src = imagecreatefromjpeg($file);
						break;

					case 3 :
						$src = imagecreatefrompng($file);
						break;

					default :
						unlink($file);

						$this->MultipleFlash->setFlash(__('Only JPG, GIF and PNG images are allowed.'), 'error');
						return;
				}

				imagepng($src, $dirname . DS . 'img' . DS . 'logo.png');
			}

			if (!empty($data['css']))
				file_put_contents($dirname . DS . 'css' . DS . 'print_accreditation.css', $data['css']);
			else if (file_exists($dirname . DS . 'css' . DS . 'print_accreditation.css'))
				unlink($dirname . DS . 'css' . DS . 'print_accreditation.css');
		}
	}


	// Print accreditation cards
	function print_accreditation($id = null) {
		$this->layout = 'print';

		$this->loadModel('Nation');
		$nations = $this->Nation->find('list', array('fields' => array('id', 'name')))->toArray();

		$this->loadModel('Type');
		$types = $this->Type->find('list', array('fields' => array('id', 'name')))->toArray();

		$conditions = array('tournament_id' => $this->request->getSession()->read('Tournaments.id'));

		if ($this->request->getSession()->check('Nations.id'))
			$conditions['Person.nation_id'] = $this->request->getSession()->read('Nations.id');

		if ($this->request->getSession()->check('Group.type_ids'))
			$conditions['Registreation.type_id'] = explode(',', $this->request->getSession()->read('Group.type_ids'));

		// But if we have a filter for a type, that has precedence (overwriting the condition above)
		if ($this->request->getSession()->check('Type.id'))
			$conditions['Registration.type_id'] = $this->request->getSession()->read('Type.id');

		if ($this->request->getSession()->check('Competition.id')) {
			$cid = $this->request->getSession()->read('Competition.id');

			$this->loadModel('Competition');
			$type_of = $this->Competition->fieldByConditions('type_of', array('id = ' => $cid));

			switch ($type_of) {
				case 'S' :
					$conditions['Participant.single_id'] = $cid;
					break;

				case 'D' :
					$conditions['Participant.double_id'] = $cid;
					break;

				case 'X' :
					$conditions['Participant.mixed_id'] = $cid;
					break;

				case 'T' :
					$conditions['Participant.team_id'] = $cid;
					break;
			}
		}

		// Filter for Name
		if ($this->request->getSession()->check('People.last_name'))
			$conditions['People.last_name LIKE'] = $this->request->getSession()->read('People.last_name') . '%';

		if (!empty($id))
			$conditions['Registration.id'] = $id;

		$data = $this->Registrations->find('all', array(
			'conditions' => $conditions,
			'contains' => array( 'People', 'Participants' )
		));

		$this->set('nations', $nations);
		$this->set('types', $types);
		$this->set(compact('data'));
	}

	// ======================================================================
	// Ajax
	function onChangePerson() {
		if (!$this->request->is('ajax'))
			return;

		$data = $this->request->getData();
		
		if (empty($data['person_id'])) {
			$this->set('json_object', array('Competitions' => array(), 'Types' => array()));

			$this->render('json');

			return;
		}

		$tid = $data['tournament_id'];
		$pid = $data['person_id'];

		$this->loadModel('Competitions');
		$this->loadModel('People');
		$this->loadModel('Types');
		$this->loadModel('Tournaments');

		$person = $this->People->get($pid);
		$tournament = $this->Tournaments->get($tid);

		// $competitions = $this->Competition->findForPerson($tournament['Tournament'], $person['Person']);
		$competitions = $this->_findCompetitions($tid, $pid);

		$types = $this->_findTypes();

		// Only root may add a player after the deadline or if the player has no extern_id
		if (!UsersTable::hasRootPrivileges($this->_user)) {
			if ($tournament['enter_before'] < date('Y-m-d') ||
			    empty($person['extern_id'])) {
				
				unset($types[TypesTable::getPlayerId()]);
			}
		}

		// If the player has no competitions, remove player, too
	    if ( empty($competitions['singles']) && 
		     empty($competitions['doubles']) &&
		     empty($competitions['mixed']) && 
		     empty($competitions['teams']) ) {

			if (empty($data['id']))
				unset($types[TypesTable::getPlayerId()]);
		}

		$this->set('json_object', array(
			'Competitions' => $competitions, 
			'Types' => $types
		));

		$this->render('json');
	}

	
	// Callback, wenn the doubles event has changed
	function onChangeDouble() {
		$this->_onChangeEvent('double');
	}


	// Callback, when the mixed event has changed
	function onChangeMixed() {
		$this->_onChangeEvent('mixed');
	}


	// Common implementation of both: Collect all possible partners
	function _onChangeEvent($field) {
		if (!$this->request->is('ajax'))
			return;
		
		$data = $this->request->getData();

		if (empty($data['participant'][$field . '_id'])) {
			$this->set('json_object', array());
		} else {
			$this->set('json_object', $this->_findPartner($data, $field));
		}

		$this->render('json');
	}


	// ======================================================================
	// Business logic
	// Find all people not yet in tournament
	function _findPeople() {
		$this->loadModel('Types');
		$this->loadModel('People');

		$conditions = 1;
		if ($this->request->getSession()->check('Nations.id'))
			$conditions .= ' AND People.nation_id = ' . $this->request->getSession()->read('Nations.id');

		// Referee will see umpires only. The same for filter for umpire
		if (GroupsTable::getRefereeId() == $this->Auth->user('group_id'))
			$conditions .= ' AND People.id IN (SELECT person_id FROM umpires)';
		else if ($this->request->getSession()->check('Type.id') && $this->request->getSession()->read('Type.id') == TypesTable::getUmpireId()) 
			$conditions .= ' AND People.id IN (SELECT person_id FROM umpires)';
			
		// Don't select people who are still in the tournament (cancelled IS NULL) or are (cancelled) players (type_id = 1)	
		$notentered  = 'SELECT person_id FROM registrations WHERE registrations.tournament_id = ' . $this->request->getSession()->read('Tournaments.id');
		$notentered .= ' AND (registrations.cancelled IS NULL OR registrations.type_id = ' . TypesTable::getPlayerId() . ')';

		$conditions .= ' AND People.id NOT IN (' . $notentered . ')';

		$tmp = $this->People->find('all', array(
			'order' => ['display_name' => 'ASC'], 
			'conditions' => $conditions,
			'fields' => array('People.id', 'People.display_name', 'People.nation_id')
		));

		if (empty($tmp))
			return array();

		$this->loadModel('Nations');
		$nations = $this->Nations->find('list', array('fields' => array('id', 'name')))->toArray();

		$people = array();

		$nid = $this->request->getSession()->read('Nations.id');

		foreach ($tmp as $t) {
			if ($t['nation_id'] == $nid)
				$people[$t['id']] = $t['display_name'];
			else
				$people[$t['id']] = $t['display_name'] . ' (' . $nations[$t['nation_id']] . ')';
		}	

		return $people;
	}

	
	// Return the content of the "types" database table.
	// Ordinary user can see only applicable types, that is no umpire or referee and no players after deadline
	function _findTypes() {
		$conditions = array();

		if ($this->request->getSession()->check('Groups.type_ids'))
			$conditions['Types.id IN'] = explode(',', $this->request->getSession()->read('Groups.type_ids'));

		$tid = $this->request->getSession()->read('Tournaments.id');
				
		$this->loadModel('Types');
		$types = $this->Types->find('list', array(
			'fields' => array('id', 'description'), 
			'conditions' => $conditions,
			'order' => ['description' => 'ASC']
		))->toArray();

		if (!UsersTable::hasRootPrivileges($this->_user)) {
			$this->loadModel('Tournaments');
			$enter_before = $this->Tournaments->fieldByConditions('enter_before', ['id' => $tid]);
			$date = date('Y-m-d');

			if (isset($types[TypesTable::getPlayerId()]) && $enter_before < $date) {
				// $this->MultipleFlash->setFlash(__('You cannot enter new players at this time any more'), 'error');

				unset($types[TypesTable::getPlayerId()]);
			}
		}

		return $types;
	}

	function _findCompetitions($tid, $pid) {
		$data = $this->request->getData();
		
		$this->loadModel('Competitions');
		$this->loadModel('Tournaments');
		$this->loadModel('People');
		$this->loadModel('Participants');

		$person = $this->People->get($pid);
		$tournament = $this->Tournaments->get($tid);
		$participant = $this->Participants->find('all', array(
			'contain' => array('Registrations'),
			'conditions' => array(
				'Registrations.person_id' => $pid,
				'Registrations.tournament_id' => $tid
			)
		))->first();


		$competitions = $this->Competitions->findForPerson($tournament, $person);

		// Participants may choose any double / mixed event
		// TODO: clear array for competition types the player did not enter.
		// Only an admin can enter him after he registered, if he did not do so at registration.
		// ptt_class:
		if ($person['ptt_class'] == 0)
			$ptt_class = array('ptt_class = 0');
		else
			$ptt_class = ['OR' => [
				'Competitions.ptt_class >=' => $person['ptt_class'],
				'Competitions.ptt_class =' => 0,
			]];
			// $ptt_class = array('ptt_class >= ' => $person['ptt_class']);

		$competitions['doubles'] = $this->Competitions->find('list', array(
			'fields' => array('id', 'description'),
			'conditions' => array(
				'Competitions.tournament_id' => $tid, 
				'Competitions.type_of' => 'D', 
				'sex' => $person['sex']
			) + $ptt_class,
			'order' => [
				'Competitions.ptt_class' => 'DESC',
				'Competitions.born' => 'DESC'
			]
		))->toArray();

		$competitions['mixed'] = $this->Competitions->find('list', array(
			'fields' => array('id', 'description'),
			'conditions' => array(
				'Competitions.tournament_id' => $tid, 
				'Competitions.type_of' => 'X'
			) + $ptt_class,
			'order' => [
				'Competitions.ptt_class' => 'DESC',
				'Competitions.born' => 'DESC'
			]
		))->toArray();

		$enter_before = $tournament->enter_before;

		$mayChange = 
			UsersTable::hasRootPrivileges($this->_user) || 
			$enter_before >= date('Y-m-d'); 

		if (!$mayChange) {
			// After first deadline do not enter new events
			if (empty($participant['single_id']) || $participant['single_cancelled'])
				$competitions['singles'] = array();
			else {
				$single_id = $participant['single_id'];
				$competitions['singles'] = array($single_id => $competitions['singles'][$single_id]);
			}

			if (empty($participant['double_id']) || $participant['double_cancelled'])
				$competitions['doubles'] = array();

			if (empty($participant['mixed_id']) || $participant['mixed_cancelled'])
				$competitions['mixed'] = array();

			if (empty($participant['team_id']) || $participant['team_cancelled'])
				$competitions['teams'] = array();
		} else {
			// Which entry to use
			$entries_key = ($person['nation_id'] == $tournament['nation_id'] ? 'entries_host' : 'entries');

			$conditions = ['Registrations.tournament_id' => $tid];

			$nid = $person['nation_id'];
			if (empty($nid))
				$conditions[] = 'People.nation_id IS NULL';
			else
			 	$conditions['People.nation_id'] = $nid;

			$counts = array();

			$singles = $this->Registrations->find('all', array(
				'contain' => array('Participants', 'People'),
				'group' => 'Participants.single_id',
				'fields' => array('Participants.single_id',  'count' => 'COUNT(Registrations.id)'),
				'conditions' => $conditions + [
					'NOT Participants.single_cancelled',
					'Participants.single_id IS NOT NULL'
				]
			));

			foreach ($singles as $s) 
				$counts[$s['participant']['single_id']] = $s['count'];

			$doubles = $this->Registrations->find('all', array(
				'contain' => array('Participants', 'People'),
				'group' => 'Participants.double_id',
				'fields' => array('Participants.double_id', 'count' => 'COUNT(Registrations.id)'),
				'conditions' => $conditions + [
					'NOT Participants.double_cancelled',
					'Participants.double_id IS NOT NULL'
				]
			));

			foreach ($doubles as $d) 
				$counts[$d['participant']['double_id']] = $d['count'];

			$mixed = $this->Registrations->find('all', array(
				'contain' => array('Participants', 'People'),
				'group' => 'Participants.mixed_id',
				'fields' => array('Participants.mixed_id', 'count' => 'COUNT(Registrations.id)'),
				'conditions' => $conditions + [
					'NOT Participants.mixed_cancelled',
					'Participants.mixed_id IS NOT NULL'
				]
			));

			foreach ($mixed as $m) 
				$counts[$m['participant']['mixed_id']] = $m['count'];

			$teams = $this->Registrations->find('all', array(
				'contain' => array('Participants', 'People'),
				'group' => 'Participants.team_id',
				'fields' => array('Participants.team_id', 'count' => 'COUNT(Registrations.id)'),
				'conditions' => $conditions + [
					'NOT Participants.team_cancelled',
					'Participants.team_id IS NOT NULL'
				]
			));

			foreach ($teams as $t) 
				$counts[$t['participant']['team_id']] = $t['count'];

			// $competitions is organized in 'singles', 'doubles', ...
			// The keys are named 'single_id', 'single_cancelled', ...
			// So we have to map from one name to the other
			$comp_types = array('singles' => 'single', 'doubles' => 'double', 'mixed' => 'mixed', 'teams' => 'team');

			// $competitions is a list 'id' => 'description'.
			// We now need a list 'id' => 'entries' / 'entries_host'
			// But only those competitions which have an limit set
			$entries = $this->Competitions->find('list', array(
				'fields' => array('id', $entries_key),
				'conditions' => [
					'Competitions.tournament_id' => $tid,
					'Competitions.' . $entries_key . ' IS NOT NULL',
					'Competitions.' . $entries_key . ' > 0'
				]
			))->toArray();

			foreach ($competitions as $type => $what) {
				// Count all participants of this association in competitions of that type
				$tmp = $this->Registrations->find('all', array(
					'contain' => array('Participants', 'People'),
					'group' => 'Participants.' . $comp_types[$type] . '_id',
					'fields' => array('Participants.' . $comp_types[$type] . '_id', 'count' => 'COUNT(Registrations.id)'),
					'conditions' => $conditions + [
						'Participants.' . $comp_types[$type] . '_id IS NOT NULL' ,
						'NOT Participants.' . $comp_types[$type] . '_cancelled'
					]
				));

				// Reorganize so that we can access the count via the id
				$counts = array();
				foreach ($tmp as $t) 
					$counts[$t['participant'][$comp_types[$type] . '_id']] = $t['count'];

				foreach ($what as $k => $c) {
					// Skip own competition
					if ( empty($data['participant'][$comp_types[$type] . '_cancelled']) && 
						 isset($data['participant'][$comp_types[$type] . '_id']) &&
                          $data['participant'][$comp_types[$type] . '_id'] == $k )
						continue;

					// Check if there are any entries
					if (empty($counts[$k]))
						continue;

					// Is the competition listed? If not everything is allowed
					if (empty($entries[$k]))
						continue;

					// Still below limit
					if ($entries[$k] > $counts[$k])
						continue;

					// Remove from list
					unset($competitions[$type][$k]);
				}
			}
		}

		return $competitions;
	}


	function _setFields($registration) {
		$this->loadModel('Tournaments');
		$this->loadModel('Types');
		
		$tid = $registration['tournament_id'];
		$pid = $registration['person_id'];

		$types = $this->_findTypes();

		// An existing registration for a non-player may not be changed to a player and v.v.
		if ($pid) {
			$type_id = $this->Registrations->fieldByConditions('type_id', array(
				'tournament_id' => $tid, 'person_id' => $pid
			));
			if ($type_id != TypesTable::getPlayerId())
				unset($types[TypesTable::getPlayerId()]);
			else 
				$types = array($type_id => $this->Types->fieldByConditions('description', array('id' => $type_id)));
		}

		$this->set('types', $types);

		$competitions = $this->_findCompetitions($tid, $pid);
		
		$this->set('competitions', $competitions);

		$mayChangePartner = 
			UsersTable::hasRootPrivileges($this->_user) || 
			$this->Tournaments->fieldByConditions('modify_before', ['id' => $tid]) >= date('Y-m-d');

		$mayChangePlayer = 
			UsersTable::hasRootPrivileges($this->_user) || 
			$this->Tournaments->fieldByConditions('enter_before', ['id' => $tid]) >= date('Y-m-d');

		// After the deadline you may not change a confirmed partner in Doubles
		// And you may not enter doubles if you are not already in
		if (empty($registration['participant']['double_id']))
			$this->set('double_partner', array());
		else if ($mayChangePartner || empty($registration['participant']['double_partner_id']) || !RegistrationsTable::isDoublePartnerConfirmed($registration))
			$this->set('double_partner', Hash::combine($this->_findPartner($registration, 'double'), '{n}.id', '{n}.display_name'));
		else {
			$double_partner_id = $registration['participant']['double_partner_id'];
			$double_partner_name = $this->People->fieldByConditions('display_name', array(
				'id' => $registration['participant']['double_partner']['person_id'])
			);
			$this->set('double_partner', array($double_partner_id => $double_partner_name));
		}

		// Same for Mixed
		if (empty($registration['participant']['mixed_id']))
			$this->set('mixed_partner', array());
		else if ($mayChangePartner || empty($registration['participant']['mixed_partner_id']) || !RegistrationsTable::isMixedPartnerConfirmed($registration))
			$this->set('mixed_partner', Hash::combine($this->_findPartner($registration, 'mixed'), '{n}.id', '{n}.display_name'));
		else {
			$mixed_partner_id = $registration['participant']['mixed_partner_id'];
			$mixed_partner_name = $this->People->fieldByConditions('display_name', array(
				'id' => $registration['participant']['mixed_partner']['person_id'])
			);
			$this->set('mixed_partner', array($mixed_partner_id => $mixed_partner_name));
		}
	}

	// ----------------------------------------------------------------------
	// Find possible partners for registration '$data' in double / mixed '$field'
	// $options are additional options for the find method
	function _findPartner($data, $field, $options = array()) {
		$event_id = $field . '_id';               // ID of this event
		$partner = $field . '_partner';           // Field DoublePartner or MixedPartner 
		$partner_id = $field . '_partner_id';     // ID of the partner (double_partner_id or mixed_partner_id)
		$event_cancelled = $field . '_cancelled'; // Flag if participation was cancelled

		$this->loadModel('People');
		$this->loadModel('Participants');
			
		$conditions = [];  

		// Not this player (if edit)
		if (!empty($data['id']))
		 	$conditions['Registrations.id !='] = $data['id'];

		// In mixed: opposite sex
		if ($field == 'mixed')
			$conditions['People.sex <>'] =  $this->People->find()->where(['People.id' => $data['person']['id']])->select('People.sex');
		else
			$conditions['People.sex'] =  $this->People->find()->where(['People.id' => $data['person']['id']])->select('People.sex');

		// Playes in this event
		$conditions['Participants.' . $event_id] = $data['participant'][$event_id];

		// And player is not cancelled
		$conditions[] = 'Participants.' . $event_cancelled . ' IS NOT TRUE';

		// Select players who don't want to play with someone else.
		// This means, ...
		$conditions['OR'] = [];

		// ... they don't have a (wanted) partner yet
		$conditions['OR'][] = 'Participants.' . $partner_id . ' IS NULL';

		if (!empty($data['id'])) {
			// ... we are his (wanted) partner
			$conditions['OR']['Participants.' . $partner_id] = $data['id'];
		}

		// ... they don't belong to a confirmed pair
		/* 
		 * For some reasons this becomes 'Registrations.id <> 0', which, in OR, 
		 * would cover all players. But if they are in a confirmed pair 
		 * Participants.${partner_id} IS NOT NULL so that condition is already 
		 * covered by the first condition
		 */
		
		/*
			$conditions['OR']['Registrations.' . 'id <>'] =
					'(SELECT ' . $partner_id . ' FROM participants p WHERE p.registration_id = Participants.' . $partner_id . ')';
		 */

		// ... he is our (wanted) partner
		if ($data['participant'][$partner_id])
			$conditions['OR']['Registrations.' . 'id'] = $data['participant'][$partner_id];

		$tmp = $this->Registrations->find('all', array_merge(
			array( 
				'contain' => array('People', 'Participants'),
				'fields' => array('Registrations.id', 'People.display_name', 'People.nation_id'), 
				'conditions' => $conditions,
				'order' => ['People.display_name' => 'ASC']
			), $options
		));

		// If there are no possible partner, return empty array
		if (empty($tmp))
			return array();

		$this->loadModel('Nations');
		$nations = $this->Nations->find('list', array('fields' => array('id', 'name')))->toArray();

		$nid = $this->request->getSession()->read('Nations.id');

		$partner = array();

		foreach ($tmp as $t) {
			$id = $t['id'];
			$name = $t['person']['display_name'];

			if ($t['person']['nation_id'] != $nid)
				$name .= ' (' . $nations[$t['person']['nation_id']] . ')';

			$partner[] = array('id' => $id, 'display_name' => $name);
		}

		return $partner;		
	}
	// -------------------------------------------------------------------
	function _findRevision($id, $when) {
		$registration = $this->Registrations->find('all', array(
			'conditions' => array('Registrations.id' => $id),
			'contain' => array('People' => array('Nations')),
		))->first();

		$registration['participant'] = array();

		$histories = $this->ParticipantHistories->find('all', array(
			'contain' => ['Registrations'],
			'conditions' => array(
				'ParticipantHistories.registration_id' => $id,
				'ParticipantHistories.created <=' => $when				
			),
			'order' => ['ParticipantHistories.created ASC']
		));

		foreach ($histories as $history) {
			$field_name = $history['field_name'];
			$old_value  = $history['old_value'];
			$new_value  = $history['new_value'];

			if ($field_name == 'created') {
				$registration['participant'] = unserialize($history['new_value']);
			} else if ($field_name == 'cancelled') {
				$registration['participant'] = unserialize($history['new_value']);
			} else {
				$registration['participant'][$field_name] = $new_value;
			}
		}

		return $registration;
	}
	
	function _selectEvent($person, $type, $tid, $partner = null) {
		return $this->RegistrationUpdate->_selectEvent($person, $type, $tid, $partner);
	}
}
