<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Model\Table\GroupsTable;
use App\Model\Table\UsersTable;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Utility\Hash;

class PeopleController extends AppController {

	public $name = 'People';
	
	public $Orders = null;
	public $OrderArticles = null;
	public $PersonHistories = null;
	

	function initialize() : void {
		parent::initialize();
		
		$this->loadModel('People');
	}

	function index() {
		if ($this->request->getQuery('nation_id') !== null) {
			if ($this->request->getQuery('nation_id') == 'all')
				$this->request->getSession()->delete('Nations.id');
			else
				$this->request->getSession()->write('Nations.id', $this->request->getQuery('nation_id'));
		}

		if ($this->request->getQuery('last_name') !== null) {
			$last_name = urldecode($this->request->getQuery('last_name'));

			if ($last_name == '*')
				$this->request->getSession()->delete('People.last_name');
			else
				$this->request->getSession()->write('People.last_name', str_replace('_', ' ', $last_name));
		}

		if ($this->request->getQuery('sex') !== null) {
			if ($this->request->getQuery('sex') === 'all')
				$this->request->getSession()->delete('People.sex');
			else
				$this->request->getSession()->write('People.sex', $this->request->getQuery('sex'));
		}

		if ($this->request->getQuery('para') !== null) {
			if ($this->request->getQuery('para') == 'all')
				$this->request->getSession()->delete('People.para');
			else
				$this->request->getSession()->write('People.para', $this->request->getQuery('para'));
		}

		if ($this->request->getQuery('user_id') !== null) {
			if ($this->request->getQuery('user_id') == 'all')
				$this->request->getSession()->delete('Users.id');
			else
				$this->request->getSession()->write('Users.id', $this->request->getQuery('user_id'));
		}

		$conditions = array();

		// Filter for association
		if ($this->request->getSession()->check('Nations.id'))
			$conditions['People.nation_id'] = $this->request->getSession()->read('Nations.id');

		// Filter for Name
		if ($this->request->getSession()->check('People.last_name'))
			$conditions[] = 'People.last_name COLLATE utf8_bin LIKE \'' . $this->request->getSession()->read('People.last_name') . '%\'';

		// Filter for Sex
		if ($this->request->getSession()->check('People.sex'))
			$conditions['People.sex'] = $this->request->getSession()->read('People.sex');

		// Filter for para
		if ($this->request->getSession()->check('People.para')) {
			if ($this->request->getSession()->read('People.para') == 'no')
				$conditions[] = 'People.ptt_class = 0';
			else if ($this->request->getSession()->read('People.para') == 'yes')
				$conditions[] = 'People.ptt_class <> 0';
		}
			
		// Filter for User
		if ($this->request->getSession()->check('Users.id'))
			$conditions['People.user_id'] = $this->request->getSession()->read('Users.id');

		// Referee sees only the umpire
		if ($this->Auth->user('group_id') == GroupsTable::getRefereeId())
			$conditions[] = 'Umpires.id IS NOT NULL';

		$this->paginate = array(
			'conditions' => $conditions,
			'contain' => ['Nations', 'Users', 'Registrations'],
			'order' => ['People.display_name' => 'ASC'],
			'sortableFields' => [
				'People.display_name',
				'People.sex',
				'People.dob',
				'People.extern_id',
				'Nations.name', 
				'Users.username',
				'People.modified'
			]
		);
		
		$this->set('people', $this->paginate());

		$this->loadModel('Nations');
		
		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'name'), 
			'conditions' => ['id IN (SELECT nation_id FROM people)'],
			'order' => 'name'
		))->toArray());
		$this->set('nation_id', $this->request->getSession()->read('Nations.id'));
		$this->set('last_name', $this->request->getSession()->read('People.last_name'));
		$this->set('user_id', $this->request->getSession()->read('Users.id'));
		$this->set('username', $this->Users->fieldByConditions('username', array('id' => $this->request->getSession()->read('Users.id') ?: 0)));

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
				$conditions[] = 'People.last_name COLLATE utf8_bin LIKE \''. mb_substr($last_name, 0, $count) . '%\'';

			$tmp = $this->People->find('all', array(
				'fields' => ['firstchar' => 'DISTINCT LEFT(last_name COLLATE utf8_bin, ' . ($count + 1) . ')'],
				'conditions' => $conditions,
				'order' => ['firstchar COLLATE utf8_unicode_ci' => 'ASC']
			));

			$tmp = Hash::extract($tmp->toArray(), '{n}.firstchar');
			
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

	}

	function view($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid person'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		$this->loadModel('Groups');

		$person = $this->People->get($id, ['contain' => ['Nations', 'Users', 'Registrations']]);
		
		$this->loadModel('Shop.OrderArticles');
		$this->loadModel('Shop.Orders');

		$orderId = $this->OrderArticles->fieldByConditions('order_id', array(
			'person_id' => $id
		));

		$order = $this->Orders->find('all', array(
			'conditions' => array('Orders.id' => $orderId ?? 0)
		))->first();
			
		if (!empty($order)) {
			$person = array_merge(['order' => $order->toArray()], $person->toArray());
		}
		
		if (!empty($this->_user['nation_id'])) {
			// Restricted to an association
			if ($this->_user['nation_id'] != $person['nation_id']) {
				$this->MultipleFlash->setFlash(__('You are not allowed to view this person'), 'error');
				return $this->redirect(array('action' => 'index'));
			}
		}

		// Security check: May the current user view this person?
		if (!empty($this->_user['tournament_id'])) {
			// Restricted to a tournament
			$this->loadModel('Registrations');
			if ($this->Registrations->find('all', array('conditions' => array(
				'person_id' => $id,
				'tournament_id' => $this->_user['tournament_id']
			)))->count() == 0) {
				$this->MultipleFlash->setFlash(__('You are not allowed to view this person'), 'error');
				return $this->redirect(array('action' => 'index'));
			}
		}

/*
		if (!empty($current_user['Group']['type_ids'])) {
			// Restricted to some types
			$this->loadModel('Registration');
			if ($this->Registration->find('all', array('conditions' => array(
				'person_id' => $id,
				'type_id' => $current_user['Group']['type_ids']
			)))->count() == 0) {
				$this->MultipleFlash->setFlash(__('You are not allowed to view this person'), 'error');
				return $this->redirect(array('action' => 'index'));
			}
		}
*/
		if (GroupsTable::getRefereeId() == $this->_user['group_id']) {
			if (empty($person['umpire']['person_id'])) {
				$this->MultipleFlash->setFlash(__('You are not allowed to view this person'), 'error');
				return $this->redirect(array('action' => 'index'));
			}
		}

		$this->set('person', $person);
	}
	
	
	function history($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid person'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		$id = $id;

		$this->loadModel('Groups');

		$current_user = $this->_user;

		$this->loadModel('PersonHistories');

		$person = $this->People->find('all', array(
			'recursive' => -1,
			'conditions' => array('People.id' => $id)
		))->first();

		// Security check: May this person be viewed by the current user?
		if ( !empty($current_user['nation_id']) && 
		     $person['nation_id'] != $current_user['nation_id'] ) {

			$this->MultipleFlash->setFlash(__('You are not allowed to view this person'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		$this->set('person', $person);

		$this->loadModel('Nations');
		$nations = $this->Nations->find('list', array(
			'fields' => array('id', 'description')
		))->toArray();
		
		$histories = $this->PersonHistories->find('all', array(
			'contain' => array('Users'),
			'conditions' => array('person_id' => $person['id']),
			'order' => ['PersonHistories.created' => 'DESC']
		))->toArray();

		foreach ($histories as &$history) {
			$field_name = $history['field_name'];
			$old_value = $history['old_value'];
			$new_value = $history['new_value'];

			$history['old_name'] = $old_value;
			$history['new_name'] = $new_value;

			if ($field_name == 'nation_id') {
				if (!empty($old_value))
					$history['old_name'] = $nations[$old_value];

				if (!empty($new_value))
					$history['new_name'] = $nations[$new_value];
			} 

			if ($field_name == 'user_id') {
				if (!empty($old_value))
					$history['old_name'] = $this->Users->fieldByConditions('username', array('id' => $old_value));	

				if (!empty($new_value))
					$history['new_name'] = $this->Users->fieldByConditions('username', array('id' => $new_value));	

			}
			
			if ($field_name == 'country_id') {
				if (!empty($old_value))
					$history['old_name'] = $countries[$old_value];

				if (!empty($new_value))
					$history['new_name'] = $countries[$new_value];
			} 

		}

		$this->set('histories', $histories);
	}


	function revision($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid person'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		if (empty($this->request->getQuery('date'))) {
			$this->MultipleFlash->setFlash(__('Invalid date'), 'error');
			return $this->redirect(array('action' => 'history', $id));
		}

		$when = $this->request->getQuery('date');

		$id = $id;

		$this->loadModel('Groups');

		$current_user = $this->_user;

		$person = $this->People->find('all', array(
			'conditions' => array('People.id' => $id),
		))->first();

		// Security check: May this person be viewed by the current user?
		if ( !empty($current_user['nation_id']) && 
		     $person['nation_id'] != $current_user['nation_id'] ) {

			$this->MultipleFlash->setFlash(__('You are not allowed to view this person'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		$this->loadModel('PersonHistories');
		$histories = $this->PersonHistories->find('all', array(
			'contain' => array('Users'),
			'conditions' => array(
				'PersonHistories.person_id' => $person['id'],
				'PersonHistories.created <=' => $when
			),
			'order' => ['PersonHistories.created' => 'ASC']
		));

		foreach ($histories as $history) {
			$field_name = $history['field_name'];
			$old_value  = $history['old_value'];
			$new_value  = $history['new_value'];

			if ($field_name == 'created') {
				$person = unserialize($history['new_value']);
			} else if ($field_name == 'cancelled') {
				$person = unserialize($history['new_value']);
			} else {
				$person[$field_name] = $new_value;
			}
		}

		$this->loadModel('Nations');

		if (!empty($person['nation_id'])) {
			$person['nation']['description'] = 
				$this->Nations->fieldByConditions('description', array(
					'id' => $person['nation_id']
				));
		}
		
		if (!empty($person['user_id'])) {
			$person['user']['username'] = 
				$this->Users->fieldByConditions('username', array(
					'id' => $person['user_id']
				));
		}

		$this->set('person', $person);

		$this->set('revision', $when);

		$this->render('view');
	}
	

	function add() {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		$this->loadModel('Groups');
		$this->loadModel('Users');
		$this->loadModel('Nations');
		
		$person = $this->People->newEmptyEntity();
		
		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->getData();
			
			// Security check: May the current user edit this person?
			$current_user = $this->_user;

			// Security check: May the current user view this person?
			if (!empty($current_user['nation_id'])) {
				if ($data['nation_id'] != $current_user['nation_id']) {
					$this->MultipleFlash->setFlash(__('You are not allowed to add this person'), 'error');
					return $this->redirect(array('action' => 'index'));
				}
			}

			// Unset User
			unset($data['user']);
			unset($data['username']);

			// Clear empty fields
			if (empty($data['dob']))
				$data['dob'] = null;

			$person = $this->People->patchEntity($person, $data);

			if ($this->People->save($person)) {
				$this->MultipleFlash->setFlash(__('The person has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The person could not be saved. Please, try again.'), 'error');
			}
		}
		
		$this->set('person', $person);
		$this->set('username', $this->_user->username);
		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'description'), 
			'order' => 'description'
		))->toArray());
		
		$this->loadModel('Competitions');
		$havePara = $this->Competitions->find()
				->where([
					// 'tournament_id' => $tid,
					'ptt_class > 0'
				]) 
				->count()
			> 0;
		
		$this->set('havePara', $havePara);
	}

	function edit($id = null) {
		if ($this->Acl->check($this->_user, 'People/index'))
			$referer = array('controller' => 'people', 'action' => 'index');
		else 
			$referer = array('controller' => 'registrations', 'action' => 'index');

		if ($this->request->getData('cancel')!== null)
			return $this->redirect($referer);
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid person'), 'error');
			// return  $this->redirect(array('action' => 'index'));
			return $this->redirect($referer);
		} 
		
		$this->loadModel('Groups');
		$this->loadModel('Users');
		$this->loadModel('Nations');

		$person = $this->People->get($id);

		// If the display name is the default one, reset it.
		// It will be set again when the data is saved.
		if ($person['display_name'] == $person['last_name'] . ', ' . $person['first_name'])
			unset($person['display_name']);
		
		// Init is_para
		$person['is_para'] = ($person['ptt_class'] ?: 0) > 0;
		
		// $this->request->data = $person->toArray();
		
		if (empty($this->request->getData())) {
			$this->request->getSession()->write('referer', $this->referer());
		} 
		
		$current_user = $this->_user;

		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->getData();
			
			// Security check: May the current user edit this person?
			if (!empty($current_user['nation_id'])) {
				// Restricted to an association
				if ($current_user['nation_id'] != $data['nation_id']) {
					$this->MultipleFlash->setFlash(__('You are not allowed to edit this person'), 'error');
					// return $this->redirect(array('action' => 'index'));
					return $this->redirect($referer);
				}
			}

			// Security check: May the current user view this person?
			if (GroupsTable::getRefereeId() == $current_user['group_id']) {
				if (empty($data['umpire']['id'])) {
					$this->MultipleFlash->setFlash(__('You are not allowed to edit this person'), 'error');
					// return $this->redirect(array('action' => 'index'));
					return $this->redirect($referer);
				}
			} else if (!empty($current_user['tournament_id'])) {
				// Restricted to a tournament
				$this->loadModel('Registrations');
				if ($this->Registrations->find('all', array('conditions' => array(
					'person_id' => $id,
					'tournament_id' => $current_user['tournament_id']
				)))->count() == 0) {
					$this->MultipleFlash->setFlash(__('You are not allowed to edit this person'), 'error');
					// return $this->redirect(array('action' => 'index'));
					return $this->redirect($referer);
				}
			}

/*
			if (!empty($current_user['Group']['type_ids'])) {
				// Restricted to some types
				$this->loadModel('Registration');
				if ($this->Registration->find('all', array('conditions' => array(
					'person_id' => $id,
					'type_id' => $current_user['Group']['type_ids']
				)))->count() == 0) {
					$this->MultipleFlash->setFlash(__('You are not allowed to edit this person'), 'error');
					return $this->redirect($this->request->getSession()->read('referer'));
				}
			}
*/

			if (UsersTable::hasRootPrivileges($current_user)) {
				// Only root may change the user of this person
				if (empty($data['username']))
					$data['user_id'] = null;
				else
					$data['user_id'] = $this->Users->fieldByConditions('id', array('username' => $data['username']));
			}

			// Unset User
			unset($data['user']);
			unset($data['username']);

			// Clear empty fields
			if (isset($data['dob']) && empty($data['dob']))
				$data['dob'] = null;
			
			// Check para
			if (($data['is_para'] ?: 0) == 0) {
				$data['ptt_class'] = 0;
				$data['wchc'] = 0;
			}

			$person = $this->People->patchEntity($person, $data);
			
			if ($this->People->save($person)) {
				$this->MultipleFlash->setFlash(__('The person has been saved'), 'success');
				// $this->redirect(array('action' => 'index'));
				return $this->redirect($referer);
			} else {
				$this->MultipleFlash->setFlash(__('The person could not be saved. Please, try again.'), 'error');
			}
		}

		$this->loadModel('Competitions');
		$havePara = $this->Competitions->find()
				->where([
					// 'tournament_id' => $tid,
					'ptt_class > 0'
				]) 
				->count()
			> 0;
		
		$this->set('havePara', $havePara);
		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'description'), 
			'order' => 'description'
		))->toArray());
		
		if (UsersTable::hasRootPrivileges($current_user)) {
			// Only root may change the user of this person
			if (empty($person['user_id']))
				$person->username = '';
			else
				$person->username = $this->Users->fieldByConditions('username', array('id' => $person['user_id']));
		}

		$this->set('person', $person);
	}

	function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid id for person'), 'error');
			$this->redirect(array('action'=>'index'));
		}

		try {
			$person = $this->People->get($id);
		} catch (InvalidPrimaryKeyException | RecordNotFoundException $_ex) {
			$this->_UNUSED($_ex);
			$this->MultipleFlash->setFlash(__('Invalid person'), 'error');
			return $this->redirect(array('action' => 'index'));			
		}

		// Security check: May the current user view this person?
		if (!UsersTable::hasRootPrivileges($this->_user)) {
			if ($person['nation_id'] != $this->Auth->user('nation_id')) {
				$this->MultipleFlash->setFlash(__('You are not allowed to delete this person'), 'error');
				$this->redirect(array('action' => 'index'));
			}
		}

		// Need them to check rules :(
		$this->loadModel('Shop.OrderArticles');
		$this->loadModel('Shop.Orders');

		if ($this->People->delete($person)) {
			$this->MultipleFlash->setFlash(__('Person deleted'), 'success');
			$this->redirect(array('action'=>'index'));
		}
		$this->MultipleFlash->setFlash(__('Person was not deleted'), 'error');
		$this->redirect(array('action' => 'index'));
	}
}

