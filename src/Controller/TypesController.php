<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Controller;

use App\Controller\AppController;

class TypesController extends AppController {

	public $name = 'Types';

	function initialize() : void {
		parent::initialize();
		
		$this->loadModel('Types');
	}		

	function index() {
		$this->paginate = array('order' => ['Types.name' => 'ASC']);
		$this->set('types', $this->paginate());
	}

	function view($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid function'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		$this->set('type', $this->Types->get($id));
	}

	function add() {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		$type = $this->Types->newEmptyEntity();
		
		if ($this->request->is(['post', 'put'])) {
			$type = $this->Types->patchEntity($type, $this->request->getData());
			
			if ($this->Types->save($type)) {
				$this->MultipleFlash->setFlash(__('The function has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The function could not be saved. Please, try again.'), 'error');
			}
		}
		
		$this->set('type', $type);
	}

	function edit($id = null) {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid function'), 'error');
			return $this->redirect(array('action' => 'index'));
		} 
		
		$type = $this->Types->get($id);
		
		if ($this->request->is(['post', 'put'])) {
			$type = $this->Types->patchEntity($type, $this->request->getData());
			
			if ($this->Types->save($type)) {
				$this->MultipleFlash->setFlash(__('The function has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The function could not be saved. Please, try again.'), 'error');
			}
		}
		
		$this->set('type', $type);
	}

	function delete($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid id for function'), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		
		$type = $this->Types->get($id);
		
		if ($this->Types->delete($type)) {
			$this->MultipleFlash->setFlash(__('Function deleted'), 'success');
			return $this->redirect(array('action'=>'index'));
		}
		$this->MultipleFlash->setFlash(__('Function was not deleted'), 'error');
		return $this->redirect(array('action' => 'index'));
	}
}
