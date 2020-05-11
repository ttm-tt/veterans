<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Controller;

use App\Controller\AppController;

use Cake\Event\EventInterface;

class CompetitionsController extends AppController {

	public $name = 'Competitions';

	function index() {
		$this->paginate = array(
			'conditions' => array('tournament_id' => $this->request->getSession()->read('Tournaments.id')),
			'order' => array('name' => 'ASC')
		);
		$this->set('competitions', $this->paginate());
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
		
		$competition = $this->Competitions->get($id);
		
		if ($this->request->is(['post', 'put'])) {
			$competition = $this->Competitions->patchEntity($competition, $this->request->getData());
			
			if ($this->Competitions->save($competition)) {
				$this->MultipleFlash->setFlash(__('The competition has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			}
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
