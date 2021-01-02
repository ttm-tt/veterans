<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Model\Table\TypesTable;

use Cake\Utility\Inflector;
use Cake\Event\EventInterface;
use Cake\Utility\Hash;


class PagesController extends AppController {

/**
 * Controller name
 *
 * @var string
 * @access public
 */
	public $name = 'Pages';

/**
 * Default helper
 *
 * @var array
 * @access public
 */
	public $helpers = array('Html');

/**
 * This controller does not use a model
 *
 * @var array
 * @access public
 */
	public $uses = array();
	
	function initialize() : void {
		parent::initialize();
		
		$this->loadComponent('Datatable');
	}
	
	// Allow certain pages for all users
	function beforeFilter(EventInterface $event) {
		parent::beforeFilter($event);
		$this->Auth->allow(array(
			'display', 
			'participants', 
			'onParticipantData',
			'count_participants',
			'onParticipantCount'
		));
		$this->Security->setConfig('validatePost', false);
	}

/**
 * Displays a view
 *
 * @param mixed What page to display
 * @access public
 */
	function display() {
		if (!empty($this->Auth->loginRedirect)) {
			return $this->redirect($this->Auth->loginRedirect);
		} else {
			$path = func_get_args();

			$count = count($path);
			if (!$count) {
				return $this->redirect('/');
			}
			$page = $subpage = $title_for_layout = null;

			if (!empty($path[0])) {
				$page = $path[0];
			}
			if (!empty($path[1])) {
				$subpage = $path[1];
			}
			if (!empty($path[$count - 1])) {
				$title_for_layout = Inflector::humanize($path[$count - 1]);
			}
			$this->set(compact('page', 'subpage', 'title_for_layout'));

			if (method_exists($this, $page))
				return $this->$page();
			
			$this->render(implode('/', $path));
		}
	}

	// The "/" URL
	function home() {
		if ($this->Auth->user() === null)
			return $this->redirect(array('controller' => 'Users', 'action' => 'login'));
		else if ($this->request->getSession()->check('Tournaments.id')) 
			$this->redirect(array('controller' => 'Registrations', 'tournament_id' => $this->request->getSession()->read('Tournament.id')));
		else
			$this->redirect(array('controller' => 'Tournaments'));
	}
	
	
	// Show participants
	public function participants() {
		$this->loadModel('Competitions');
	
		$tmp = $this->Competitions->find('all', array(
			'fields' => array('type_of' => 'DISTINCT type_of')
		));
		
		$types = Hash::combine($tmp->toArray(), '{n}.type_of', '{n}.type_of');
		
		$this->set('types', $types);
	}
	
	// Count participants
	public function count_participants() {
		// $this->autoRender = false;
		
		if (!$this->request->is(['post', 'get'])) {
			// No POST, return empty body
			$this->autoRender = false;
			echo '';
			return;
		}
		
		$this->loadModel('Registrations');
		
		$conditions = array(
			'Registrations.cancelled IS NULL',
			'Registrations.type_id' => TypesTable::getPlayerId()			
		);
		
		$count = $this->Registrations->find('all', ['conditions' => $conditions])->count();
		
		$this->set('count', ['paid' => $count]);
		$this->set('_serialize', 'count');
		// echo json_encode($count);
	}
	
	function onParticipantData() {
		// $this->autoRender = false;
		
		if (!$this->request->is(['post'])) {
			// No POST, return empty body
			$this->autoRender = false;
			echo '';
			return;
		}
		
		$request = $this->request->getData();
		
		$this->loadModel('Registrations');
		$this->loadModel('Types');
		$this->loadModel('Competitions');
		// $this->loadModel('Tournament');
		
		$conditions = array(
			'Registrations.cancelled IS NULL',
			'Registrations.type_id' => TypesTable::getPlayerId()			
		);
		
		$fields = array(
			'Person.display_name',
			'Nation.name'
			
		);
		
		$tmp = $this->Competitions->find('all', array(
			'fields' => array('type_of' => 'DISTINCT type_of')
		));
		
		$types = Hash::combine($tmp->toArray(), '{n}.type_of', '{n}.type_of');
		
		$joins = array(
				array('table' => 'people', 'alias' => 'Person', 'type' => 'INNER', 'conditions' => 'Registrations.person_id = Person.id'),
				array('table' => 'nations', 'alias' => 'Nation', 'type' => 'INNER', 'conditions' => 'Person.nation_id = Nation.id'),
				array('table' => 'participants', 'alias' => 'Participant', 'type' => 'INNER', 'conditions' => 'Registrations.id = Participant.registration_id'),
		);
		
		if (isset($types['S'])) {
			$joins[] = 
				array('table' => 'competitions', 'alias' => 'Singles', 'type' => 'LEFT OUTER', 'conditions' => 'Participant.single_id = Singles.id')
			;
			
			$fields[] = 'Singles.name';
		}
		
		if (isset($types['D'])) {
			$joins[] = 
				array('table' => 'competitions', 'alias' => 'Doubles', 'type' => 'LEFT OUTER', 'conditions' => 'Participant.double_id = Doubles.id')
			;
			$fields[] = 'Doubles.name';
			
			$joins[] =
				array(
					'table' => 'participants', 
					'alias' => 'DoublePartnerParticipant', 
					'type' => 'LEFT OUTER', 
					'conditions' => 
						'Participant.double_partner_id = DoublePartnerParticipant.registration_id AND DoublePartnerParticipant.double_partner_id = Participant.registration_id'
				)
			;
			$joins[] =
				array('table' => 'registrations', 'alias' => 'DoublePartnerRegistration', 'type' => 'LEFT OUTER', 'conditions' => 'DoublePartnerRegistration.id = DoublePartnerParticipant.registration_id')
			;
			$joins[] =
				array('table' => 'people', 'alias' => 'DoublePartner', 'type' => 'LEFT OUTER', 'conditions' => 'DoublePartnerRegistration.person_id = DoublePartner.id')
			;
			
			$fields[] = 'DoublePartner.display_name';
		}
		
		if (isset($types['X'])) {
			$joins[] = 
				array('table' => 'competitions', 'alias' => 'Mixed', 'type' => 'LEFT OUTER', 'conditions' => 'Participant.mixed_id = Mixed.id')
			;
			$fields[] = 'Mixed.name';
			
			$joins[] =
				array(
					'table' => 'participants', 
					'alias' => 'MixedPartnerParticipant', 
					'type' => 'LEFT OUTER', 
					'conditions' => 
						'Participant.mixed_partner_id = MixedPartnerParticipant.registration_id AND MixedPartnerParticipant.mixed_partner_id = Participant.registration_id'
				)
			;
			$joins[] =
				array('table' => 'registrations', 'alias' => 'MixedPartnerRegistration', 'type' => 'LEFT OUTER', 'conditions' => 'MixedPartnerRegistration.id = MixedPartnerParticipant.registration_id')
			;
			$joins[] =
				array('table' => 'people', 'alias' => 'MixedPartner', 'type' => 'LEFT OUTER', 'conditions' => 'MixedPartnerRegistration.person_id = MixedPartner.id')
			;
			
			$fields[] = 'MixedPartner.display_name';
		}
		
		if (isset($types['T'])) {
			$joins[] = 
				array('table' => 'competitions', 'alias' => 'Teams', 'type' => 'LEFT OUTER', 'conditions' => 'Participant.team_id = Teams.id')
			;
			$fields[] = 'Teams.name';
		}
		
		$this->paginate = array(
			'conditions' => $conditions,
			'join' => $joins,
			'fields' => $fields,
		);
		
		$response = $this->Datatable->getResponse($request, $this->Registrations);
		
		$this->set('response', $response);
		$this->viewBuilder()->setOption('serialize', ['response']);
		// $this->set('_serialize', 'response');
		// echo json_encode($response);
	}
	
	public function onParticipantCount() {
		// $this->autoRender = false;
		
		if (!$this->request->is(['post', 'get'])) {
			// No POST, return empty body
			$this->autoRender = false;
			echo '';
			return;
		}
		
		$this->loadModel('Registrations');
		
		$conditions = array(
			'Registrations.cancelled IS NULL',
			'Registrations.type_id' => TypesTable::getPlayerId()			
		);
		
		$count = $this->Registrations->find('all', ['conditions' => $conditions])->count();
		
		$this->set('count', $count);
		$this->set('_serialize', 'count');
		// echo json_encode($count);
	}
}
