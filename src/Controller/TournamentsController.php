<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Model\Table\UsersTable;
use App\Model\Table\GroupsTable;

class TournamentsController extends AppController {

	function index() {
		$this->paginate = array('order' => ['start_on DESC']);
	
		if (empty($this->request->getQuery('all')) && !UsersTable::hasRootPrivileges($this->_user))
			$this->paginate['conditions'] = array('end_on >=' => date('Y-m-d'));

		if (!UsersTable::hasRootPrivileges($this->_user) && GroupsTable::getOrganizerId() != $this->_user['User']['group_id'])
			$this->paginate['conditions']['enter_after <='] = date('Y-m-d');

		$this->set('all', !empty($this->request->getQuery('all')));
		$this->set('tournaments', $this->paginate());
	}

	function view($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid tournament'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		$this->set('tournaments', $this->Tournaments->find('all', array(
			'conditions' => array('Tournaments.id' => $id),
			'contain' => array('Nations')
		))->first());
	}

	function add() {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		$this->loadModel('Nations');
		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'description'), 
			'order' => 'description'
		))->toArray());
		
		$tournament = $this->Tournaments->newEmptyEntity();
		
		if ($this->request->is(['post', 'put'])) {
			$tournament = $this->Tournaments->patchEntity($tournament, $this->request->getData());
			
			if ($this->Tournaments->save($tournament)) {
				$this->MultipleFlash->setFlash(__('The tournament has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The tournament could not be saved. Please, try again.'), 'error');
			}						
		}
		
		// Views have a variable named 'tournament'
		$this->set('t', $tournament);
	}

	function edit($id = null) {
		if (array_key_exists('cancel', $this->request->getData())) {
			return $this->redirect(array('action' => 'index'));
		} 
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid tournament'), 'error');
			return $this->redirect(array('action' => 'index'));
		} 
		
		$tournament = $this->Tournaments->find('all', array(
			'conditions' => array('Tournaments.id' => $id),
			'contain' => array(
				'Nations', 'Organizers', 'Committees', 'Hosts', 'Contractors', 'Dpas'
			)
		))->first();

		$this->loadModel('Nations');
		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'description'), 
			'order' => 'description'
		))->toArray());
		
		
		if ($this->request->is(['post', 'put'])) {
			$tournament = $this->Tournaments->patchEntity($tournament, $this->request->getData());
			
			if ($this->Tournaments->save($tournament)) {
				$this->MultipleFlash->setFlash(__('The tournament has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The tournament could not be saved. Please, try again.'), 'error');
			}
		}
		
		// View have a variable named 'tournament'
		$this->set('t', $tournament);
	}

	function delete($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid id for tournament'), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		
		set_time_limit(0);
		
		$tournament = $this->Tournaments->get($id);
		
		if ($this->Tournaments->delete($tournament)) {
			$this->MultipleFlash->setFlash(__('Tournament deleted'), 'success');
			return $this->redirect(array('action'=>'index'));
		} 
		
		$this->MultipleFlash->setFlash(__('Tournament was not deleted'), 'error');
		return $this->redirect(array('action' => 'index'));
	}
}
