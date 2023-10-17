<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php

namespace App\Controller;

use App\Controller\AppController;
use App\Model\Table\TypesTable;

use Cake\Event\EventInterface;

// XXX: Why do I have to do this manually?
require_once ROOT . '/vendor/greenfieldtech-nirs/ixr-xmlrpc/ixr_xmlrpc.php';


class Rpc2Controller extends AppController
{
	// Models loaded on the fly
	public $Registrations = null;
	
	function beforeFilter(EventInterface $event) {
		parent::beforeFilter($event);
		
		$this->Auth->setConfig('authenticate', array('Basic' => [
				'passwordHasher' => [
					'className' => 'Fallback',
					'hashers' => [
						'Default',
						'Weak' => ['hashType' => 'sha1']
					]
				]
			]
		));
		
		$this->Auth->identify();
		
		$this->Security->setConfig('validatePost', false);

		$this->RequestHandler->respondAs('xml');
		
		$this->request->allowMethod(['post']);
	}


	function index() {
		// Configure::write('debug', 2);
		// Fuer die Spieler mehr Speicher allozieren
		ini_set('memory_limit', 256*1024*1024);
		
		$this->autoRender = false;

		$server = new \IXR_server(array(
			'onlineentries.echo' => array(&$this, '_echo'),
			'onlineentries.listTournaments' => array(&$this, '_listTournaments'),
			'onlineentries.listCompetitions' => array(&$this, '_listCompetitions'),
			'onlineentries.listNations' => array(&$this, '_listNations'),
			'onlineentries.listPlayers' => array(&$this, '_listPlayers'),
			'onlineentries.listRankingpoints' => array(&$this, '_listRankingpoints'),
			'onlineentries.listRegistrations' => array(&$this, '_listRegistrations'),
			'onlinenetries.listTeams' => array(&$this, '_listTeams'),
			'onlineentries.addPeople' => array(&$this, '_addPeople'),
			'onlineentries.sendWelcomeMail' => array(&$this, '_sendWelcomeMail'),
		), false, true);
		
		$ret = $server->serve($this->request->getBody());
		
		// Setup a response with the returned data
		$this->response = $this->response
				->withType('text/xml')
				->withStringBody($ret)
		;		
	}


	// -------------------------------------------------------------------
	function _echo($what) {
		return $what;
	}	


	function _listTournaments() {
		$this->loadModel('Tournaments');
		return $this->Tournaments->find('all', array(
			'conditions' => 'start_on > ' . date('Y-m-d'),
			'order' => 'start_on DESC'
		))->disableHydration()->all()->toArray();
	}

	function _listCompetitions($tid) {
		$this->loadModel('Competitions');
		return $this->Competitions->find('all', array(
			'conditions' => array('tournament_id' => $tid)
		))->disableHydration()->all()->toArray();
	}


	function _listNations($tid) {
		$this->loadModel('Nations');
		$this->loadModel('Registrations');
		
		return $this->Nations->find('all', array(
			'conditions' => [
				'Nations.id IN' => $this->Registrations->find('all', array(
					'contain' => ['People'],
					'fields' => ['nation_id' => 'DISTINCT People.nation_id'],
					'conditions' => [
						'Registrations.tournament_id' => $tid,
						'Registrations.type_id' => TypesTable::getPlayerId()
					]
				))
			]
		))->disableHydration()->all()->toArray();
	}

	
	function _listPlayers($tid) {
		$this->loadModel('Registrations');
		return $this->Registrations->find('all', array(
			'contain' => array(
				'Participants',
				'People'
			),
			'conditions' => array(
				'Registrations.tournament_id' => $tid,
				'Registrations.type_id' => TypesTable::getPlayerId(),
				'Registrations.cancelled IS NULL'
			)
		))->disableHydration()->all()->toArray();
	}

	
	function _listRankingpoints($tid) {
		// Dummy, there are no ranking points in veterans yet
		return [];
	}
	

	function _listRegistrations($tid) {
		$this->loadModel('Registrations');
		return $this->Registrations->find('all', array(
			// 'recursive' => 1,
			'contain' => array(
				'Participants',
				'People'
			),
			'conditions' => array(
				'Registrations.tournament_id' => $tid, 
				'Registrations.type_id' => TypesTable::getPlayerId(),
				'Registrations.cancelled IS NULL'
			),
		))->disableHydration()->all()->toArray();
	}
	
	
	function _listTeams($tid) {
		// TODO: Build random team name, construct description
		$this->loadModel('Team');
		
		$teams = $this->Team->find('all', array(
			'contain' => 'RegistrationTeam',
			'conditions' => array(
				'Registration.tournament_id' => $tid				
			)
		));
		
		return $teams;
	}
	

	function _addPeople($arg) {
		$user = $arg['user'];
		$people = $arg['people'];

		$this->loadModel('User');
		$this->loadModel('Person');
		$this->loadModel('Registration');
		$this->loadModel('Tournament');
		$this->loadModel('Nation');

		$types = array(
			'PLA' => Type::getPlayerId(),
			'ACC' => Type::getAccId()
		);

		// Return array: for user and every person a flag if the record
		// could be created. It is OK if it is already in the database.
		$return = array('user' => false, 'people' => array());
		foreach ($people as $person)
			$return['people'][] = false;

		if (empty($arg['tournament']))
			return $return;

		$tid = $this->Tournament->fieldByConditions('id', array('name' => $arg['tournament']));
		$uid = null;

		// Test if user is in the array.
		// It is (or may be) an error if there is no user.
		// Optional we could create people anonymous, i.e. without user.
		if (empty($arg['user']))
			return $return;

		$count = $this->User->find('all', array(
			'recursive' => -1,
			'conditions' => array('username' => $user['username'])
		))->count();

		if ($count == 0) {
			// Create new User
			$this->User->create();
			$data = array('User' => array(
				'username' => $user['username'],
				'password' => '', 
				'email' => $user['email'],
				'group_id' => Group::getParticipantId(),
				'enabled' => true,
				'tournament_id' => $tid
			));

			// Return error if we can't create the user
			if (!$this->User->saveAll($data))
				return $return;

			$uid = $this->User->fieldByConditions('id', array('username' => $user['username']));
		
			if ($this->Tournament->fieldByConditions('enter_after', ['id' => $tid]) <= date('Y-m-d')) {
				if (!empty($uid))
					$this->_sendWelcomeMail($uid);
			}

			$return['user'] = true;
		} else {
			$uid = $this->User->fieldByConditions('id', array('username' => $user['username']));
		
			$return['user'] = true;
		}

		// We don't return from within the loop, so reset the $return['people'] and rebuild it
		$return['people'] = array();

		$nations = $this->Nation->find('list', array('fields' => array('name', 'id')))->toArray();

		foreach ($people as $person) {
			$this->log(
				$user['username'] . ': ' .
				$person['last_name'] . ';' . $person['first_name'] . ';' . $person['id'] . ';' . 
				$person['function'] . ';' .
				(empty($person['association']) ? '' : $person['association']) . ';' . 
				(empty($person['dob']) ? '' : $person['dob']) . ';' .
				(empty($person['single']) ? '' : $person['single']) . ';' . 
				(empty($person['double']) ? '' : $person['double']) . ';' . ';' . 
				(empty($person['mixed']) ? '' : $person['mixed']) . ';' . 
				(empty($person['team']) ? '' : $person['team']),
				'debug');

			// Strings are in the wrong format (utf8_encode of an UTF-8 string)
			// So temporarily convert them back.
			// // $person['last_name'] = mb_strtoupper(utf8_decode($person['last_name']));
			// // $person['first_name'] = mb_convert_case(mb_strtolower(utf8_decode($person['first_name'])), MB_CASE_TITLE, "UTF-8");
			$person['last_name'] = mb_strtoupper(($person['last_name']));
			$person['first_name'] = mb_convert_case(mb_strtolower(($person['first_name'])), MB_CASE_TITLE, "UTF-8");

			// Date is not in ISO format but german. Convert to ISO
			if (strpos($person['dob'], '.') > 0) {
				$tmp = explode('.', $person['dob']);
				$person['dob'] = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
			}

			$idCount = $this->Person->find('all', array(
					'recursive' => -1,
					'conditions' => array('extern_id' => $person['id'])
			))->count();

			$fullName = $person['last_name'] . ', ' . $person['first_name'] . ' (' . $person['id'] . ')';

			// We don't update people, we create only new one.
			// Or else a player may become a non-player and that will cause
			// problems with double partners here. And I don't want to send emails here.
			if ($idCount > 0) {
				$return['people'][] = true;

				$this->log('Person ' . $fullName . ' already exists', 'debug');
				continue;
			}

			// Test if the assocation exists for players
			if ( $person['function'] == 'PLA' && (empty($person['association']) || empty($nations[$person['association']])) ) {
				$return['people'][] = false;

				$this->log('Association missing for player ' . $fullName, 'error');

				continue;
			}

			// Start transaction. The model argument is a dummy, hopefully ...
			$db = ConnectionManager::get($this->Person->useDbConfig);
			if (!$db->begin($this->Person)) {
				$return['people'][] = false;

				$this->log('Could not start transaction to add ' . $fullName, 'error');

				continue;
			}

			// Create new person
			$this->Person->create();
			$data = array(
				'Person' => array(
					'first_name' => $person['first_name'],
					'last_name' => $person['last_name'],
					'sex' => ($person['sex'] == 'M' ? 'M' : 'F'),
					'user_id' => $uid,
					'dob' => $person['dob'],
					'extern_id' => $person['id']
				)
			);

			if (!empty($person['association']) && !empty($nations[$person['association']]))
				$data['Person']['nation_id'] = $nations[$person['association']];

			if (!$this->Person->saveAll($data)) {
				$db->rollback($this->Person);
				$return['people'][] = false;

				$this->log('Could not save data for ' . $fullName, 'error');

				continue;
			}

			$pid = $this->Person->fieldByConditions('person_id', array('extern_id' => $person['id']));

			$data = array(
				'Registration' => array(
					'person_id' => $pid,
					'type_id' => $types[$person['function']],
					'tournament_id' => $tid
				)
			);

			if ($person['function'] == 'PLA') {
				$data['Participant'] = array(
					'single_id' => $this->_selectEvent($person, $tid, 'S'),
					'double_id' => $this->_selectEvent($person, $tid, 'D'),
					'mixed_id'  => $this->_selectEvent($person, $tid, 'X'),
					'team_id'   => $this->_selectEvent($person, $tid, 'T'),
				);
			}
				
			if (!$this->Registration->saveAll($data)) {
				$db->rollback($this->Person);
				$return['people'][] = false;

				$this->log('Could not save participant ' . $fullName, 'error');
			
				continue;
			}

			if (!$db->commit($this->Person)) {
				$return['people'][] = false;

				$this->log('Could not commit data for ' . $fullName, 'error');

				continue;
			}

			$return['people'][] = true;

			$this->log('Added person ' . $fullName, 'debug');
		}

		return $return;
	}


	function _sendWelcomeMail($id = false) {
		$this->WelcomeMail->sendWelcomeMail($id);
		return true;
	}


	function _selectEvent($person, $tid, $type) {
		$map = array('S' => 'single', 'D' => 'double', 'X' => 'mixed', 'T' => 'team');
		
		if (empty($person[$map[$type]]))
			return null;
		
		$this->loadModel('Competition');

		$year = date('Y', strtotime($person['dob']));
		$born = ($year < date('Y') - 30 ? 'born >=' : 'born <=');

		$c = $this->Competition->find('all', array(
			'fields' => array('id'),
			'conditions' => array(
				'tournament_id' => $tid,
				'sex' => $person['sex'],
				'type_of' => $type,
				$born => $year
			),
			'order' => 'born ASC'
		))->first();

		if (!empty($c))
			return $c['Competition']['id'];
		else
			return null;
	}
}
