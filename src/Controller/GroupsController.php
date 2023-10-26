<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Controller;

use App\Controller\AppController;

class GroupsController extends AppController {

	public $name = 'Groups';

	function initialize() : void {
		parent::initialize();
		
		$this->loadModel('Groups');
	}
	

	function index() {
		$this->loadModel('Types');

		$types = $this->Types->find('list')->toArray();
		
		$this->paginate = [
			'order' => ['name' => 'ASC']
		];

		$groups = $this->paginate()->toArray();
		foreach($groups as $k => $v) {
			$typenames = array();

			if (!empty($v['type_ids'])) {
				foreach(explode(',', $v['type_ids']) as $t)
					$typenames[] = $types[$t];
			}

			$groups[$k]['typenames'] = implode(',', $typenames);
		}

		$this->set('groups', $groups);
	}

	function view($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid group'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		$group = $this->Groups->get($id);

		$this->loadModel('Types');
		$types = $this->Types->find('list')->toArray();

		if (!empty($group['type_ids'])) {
			$typenames = array();
			foreach(explode(',', $group['type_ids']) as $t)
				$typenames[] = $types[$t];

			$group['type_names'] = implode(',', $typenames);
		}

		$this->set('group', $group);
	}

	function add() {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		$group = $this->Groups->newEmptyEntity();
		
		if ($this->request->is(['post', 'put'])) {
			$group = $this->Groups->patchEntity($group, $this->request->getData());
			$group->type_ids = implode(',', $group->types);

			if ($this->Groups->save($group)) {
				$this->MultipleFlash->setFlash(__('The group has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The group could not be saved. Please, try again.'), 'error');
			}
		}

		// Set list of functions (types) and parent groups
		$this->loadModel('Types');

		$types = $this->Types->find('list', array(
			'fields' => array('id', 'description')
		))->toArray();

		$this->set('group', $group);
		$this->set('types', $types);
	}

	function edit($id = null) {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid group'), 'error');
			return $this->redirect(array('action' => 'index'));
		} 
		
		$group = $this->Groups->get($id);

		if ($this->request->is(['post', 'put'])) {			
			$group = $this->Groups->patchEntity($group, $this->request->getData());
			$group->type_ids = implode(',', $group->types);

			if ($this->Groups->save($group)) {
				$this->MultipleFlash->setFlash(__('The group has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The group could not be saved. Please, try again.'), 'error');
			}
		}

		// Set list of functions (types) and parent groups
		$this->loadModel('Types');
		$types = $this->Types->find('list', array(
			'fields' => array('id', 'description')
		))->toArray();

		$group->types = explode(',', $group->type_ids);
		$this->set('types', $types);
		$this->set('group', $group);
	}

	function delete($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid id for group'), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		
		$group = $this->Groups->get($id);
		
		if ($this->Groups->delete($group)) {
			$this->MultipleFlash->setFlash(__('Group deleted'), 'success');
			return $this->redirect(array('action'=>'index'));
		}
		$this->MultipleFlash->setFlash(__('Group was not deleted'), 'error');
		return $this->redirect(array('action' => 'index'));
	}
}
