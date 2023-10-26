<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Controller;

use App\Controller\AppController;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Event\EventInterface;

class CompetitionsController extends AppController {

	public $name = 'Competitions';

	function initialize() : void {
		parent::initialize();
		
		$this->loadModel('Competitions');
	}

	function index() {
		if ($this->request->getQuery('cp_sex') !== null) {
			if ($this->request->getQuery('cp_sex') === 'all')
				$this->request->getSession()->delete('Competitions.sex');
			else
				$this->request->getSession()->write('Competitions.sex', $this->request->getQuery('cp_sex'));
		}

		if ($this->request->getQuery('type_of') !== null) {
			if ($this->request->getQuery('type_of') === 'all')
				$this->request->getSession()->delete('Competitions.type_of');
			else
				$this->request->getSession()->write('Competitions.type_of', $this->request->getQuery('type_of'));
		}
		
		if ($this->request->getQuery('para') !== null) {
			if ($this->request->getQuery('para') == 'all')
				$this->request->getSession()->delete('Competitions.para');
			else
				$this->request->getSession()->write('Competitions.para', $this->request->getQuery('para'));
		}

		$conditions = [];

		// Filter for Sex
		if ($this->request->getSession()->check('Competitions.sex'))
			$conditions['Competitions.sex'] = $this->request->getSession()->read('Competitions.sex');

		// Filter for type_of
		if ($this->request->getSession()->check('Competitions.type_of'))
			$conditions['Competitions.type_of'] = $this->request->getSession()->read('Competitions.type_of');

		if ($this->request->getSession()->check('Competitions.para')) {
			if ($this->request->getSession()->read('Competitions.para') == 'no')
				$conditions[] = 'Competitions.ptt_class = 0';
			else if ($this->request->getSession()->read('Competitions.para') == 'yes')
				$conditions[] = 'Competitions.ptt_class <> 0';
		}
			
		$this->paginate = array(
			'conditions' => [
				'tournament_id' => $this->request->getSession()->read('Tournaments.id')
			] + $conditions,
			'order' => array('name' => 'ASC')
		);
		
		$this->set('competitions', $this->paginate());
		
		$this->set('cp_sex', $this->request->getSession()->read('Competitions.sex'));
		$this->set('type_of', $this->request->getSession()->read('Competitions.type_of'));
		$this->set('para', $this->request->getSession()->read('Competitions.para'));
	}

	function view($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid competition'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		$this->set('competition', $this->Competitions->get($id));
	}

	function add() {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		
		$competition = $this->Competitions->newEmptyEntity();
		
		if ($this->request->is(['post', 'put'])) {
			$competition = $this->Competitions->patchEntity($competition, $this->request->getData());

			if ($this->Competitions->save($competition)) {
				$this->MultipleFlash->setFlash(__('The competition has been saved'), 'success');
				if ( !array_key_exists('continue', $this->request->getData()) )
					return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The competition could not be saved. Please, try again.'), 'error');
			}
		}
		
		$this->set('competition', $competition);
	}

	function edit($id = null) {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid competition'), 'error');
			return $this->redirect(array('action' => 'index'));
		} 
		
		if (!$this->request->getSession()->check('Tournaments.id')) {
			$this->MultipleFlash->setFlash(__('You must select a tournament first'), 'error');
			return $this->redirect(array('controller' => 'tournaments', 'action' => 'index'));
		}
		
		try {
			$competition = $this->Competitions->get($id);
		} catch (InvalidPrimaryKeyException | RecordNotFoundException $_ex) {
			$this->_UNUSED($_ex);
			$this->MultipleFlash->setFlash(__('Invalid competition'), 'error');
			return $this->redirect(array('action' => 'index'));			
		}
		
		if ($this->request->is(['post', 'put'])) {
			$competition = $this->Competitions->patchEntity($competition, $this->request->getData());
			
			if ($this->Competitions->save($competition)) {
				$this->MultipleFlash->setFlash(__('The competition has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			}
				
			$this->MultipleFlash->setFlash(__('The competition could not be saved. Please, try again.'), 'error');
		}
		
		$this->set('competition', $competition);
	}

	function delete($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid id for competition'), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		
		$competition = $this->Competitions->get($id);
		
		if ($this->Competitions->delete($competition)) {
			$this->MultipleFlash->setFlash(__('Competition deleted'), 'success');
			return $this->redirect(array('action'=>'index'));
		}
		
		$this->MultipleFlash->setFlash(__('Competition was not deleted'), 'error');
		return $this->redirect(array('action' => 'index'));
	}

	// ----------------------------------------------------------------------
	function beforeRender(EventInterface $event) {
		parent::beforeRender($event);
		$this->set('types', array(
			'S' => __('Singles'), 
			'D' => __('Doubles'), 
			'X' => __('Mixed'), 
			'T' => __('Teams')
		));
		$this->set('sex', array(
			'F' => __('Women'), 
			'M' => __('Men'), 
			'X' => __('Mixed')
		));
	}
}
