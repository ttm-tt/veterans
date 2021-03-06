<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Controller;

use App\Controller\AppController;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;


class NationsController extends AppController {

	public $name = 'Nations';

	public $paginate = array();

	function index() {
		$this->paginate = array('order' => ['Nations.name' => 'ASC']);
		$this->set('nations', $this->paginate());
	}

	function view($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid association'), 'error');
			$this->redirect(array('action' => 'index'));
		}

		try {
			$this->set('nation', $this->Nations->get($id));
		} catch (InvalidPrimaryKeyException | RecordNotFoundException $_ex) {
			$this->_UNUSED($_ex);
			$this->MultipleFlash->setFlash(__('Invalid association'), 'error');
			return $this->redirect(array('action' => 'index'));			
		}
	}

	function add() {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		$nation = $this->Nations->newEmptyEntity();
		
		if ($this->request->is(['post', 'put'])) {
			$nation = $this->Nations->patchEntity($nation, $this->request->getData());
			if ($this->Nations->save($nation)) {
				$this->MultipleFlash->setFlash(__('The association has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The association could not be saved. Please, try again.'), 'error');
			}
		}
		
		$this->set('nation', $nation);
	}

	function edit($id = null) {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid association'), 'error');
			return $this->redirect(array('action' => 'index'));
		} 
		
		try {
			$nation = $this->Nations->get($id);
		} catch (InvalidPrimaryKeyException | RecordNotFoundException $_ex) {
			$this->_UNUSED($_ex);
			$this->MultipleFlash->setFlash(__('Invalid association'), 'error');
			return $this->redirect(array('action' => 'index'));			
		}

		if ($this->request->is(['post', 'put'])) {
			$nation = $this->Nations->patchEntity($nation, $this->request->getData());
			
			if ($this->Nations->save($nation)) {
				$this->MultipleFlash->setFlash(__('The association has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The association could not be saved. Please, try again.'), 'error');
			}
		}
		
		$this->set('nation', $nation);
	}

	function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid id for association'), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		
		try {
			$nation = $this->Nations->get($id);
		} catch (InvalidPrimaryKeyException | RecordNotFoundException $_ex) {
			$this->_UNUSED($_ex);
			$this->MultipleFlash->setFlash(__('Invalid association'), 'error');
			return $this->redirect(array('action' => 'index'));			
		}
		
		if ($this->Nations->delete($nation)) {
			$this->MultipleFlash->setFlash(__('Association deleted'), 'success');
			return $this->redirect(array('action'=>'index'));
		}
		$this->MultipleFlash->setFlash(__('Association was not deleted'), 'error');
		return $this->redirect(array('action' => 'index'));
	}
}
