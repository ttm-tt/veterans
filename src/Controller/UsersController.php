<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Controller;

use App\Controller\AppController;

use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Event\EventInterface;
use Cake\Routing\Router;
use Cake\Log\Log;
use Cake\Utility\Hash;
use Cake\Mailer\Mailer;
use Cake\I18n\I18n;
use App\Model\Table\GroupsTable;
use App\Model\Table\UsersTable;


class UsersController extends AppController {

	public function initialize() : void {
		parent::initialize();
		
		$this->loadComponent('WelcomeMail');
		
		$this->Auth->allow([
			'login', 
			'logout', 
			'forgot_password', 
			'request_password', 
			'onChangeLanguage'
		]);
	}
	
	
	public function beforeFilter(EventInterface $event) {
		// XXX: Before or after call to parent?
		parent::beforeFilter($event);
		
		$this->Security->setConfig('unlockedActions', [
			'login', 
			'logout', 
			'forgot_password', 
			'request_password', 
			'onChangeLanguage'
		]);
	}


	public function forgot_password() {
		if ($this->request->getData('cancel') !== null) {
			return $this->redirect(array('action' => 'login'));
		}

		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->getData();
			
			if (empty($data['username'])) {
				$this->MultipleFlash->setFlash(__d('user', 'You must fill in your email address'), 'error');

				return;
			}
			
			$users = $this->Users->find('all', array(
				'conditions' => array(
					'username' => $data['username']
				)
			));

			if (empty($users)) {
				// Log error
				Log::info('Forgot Password: unknown email address ' . $data['username'] . ' from ' . $_SERVER['REMOTE_ADDR'], 'login');
				$this->MultipleFlash->setFlash(__d('user', 'Email address not found'), 'error');

				return;
			} 

			$this->loadModel('Tournaments');
			$enterAfter = $this->Tournaments->find('list', array(
				'fields' => array('id', 'enter_after')
			))->toArray();

			foreach($users as $user) {
				if ( !empty($user['tournament_id']) ) {
					if (date('Y-m-d') < $enterAfter[$user['tournament_id']])
						continue;
				}

				if (empty($user['password'])) {
					$this->_sendPasswordMail($user);
					return $this->redirect(array('action' => 'login'));
				} else {
					$this->_sendRequestMail($user);
					return $this->redirect(array('action' => 'login'));
				}
			}
		}
	}

	public function request_password($ticket) {
		$user = $this->Users->find('all', array('conditions' => array(
			'ticket' => $ticket
		)))->first();

		if (empty($user)) {
			$this->MultipleFlash->setFlash(__d('user', 'Invalid request to reset the password'), 'error');
			return $this->redirect(array('action' => 'login'));
		}

		if ($user['ticket_expires'] < date('Y-m-d H:i:s')) {
			$this->MultipleFlash->setFlash(__d('user', 'Your ticket has expired'), 'error');
			return $this->redirect(array('action' => 'forgot_password'));
		}

		$this->_sendPasswordMail($user);
		
		// TODO: And login participants
		if (Configure::read('App.magicLinks') && $user->group_id === GroupsTable::getParticipantId()) {
			return $this->_afterLogin($user);
		} else {
			return $this->redirect(array('action' => 'login'));	
		}
	}


	private function _sendRequestMail($user) {
		$subjectPrefix = '';
		$this->loadModel('Tournaments');			
		$tournament = $this->Tournaments->find('all', array(
			'contain' => array('Nations', 'Hosts', 'Organizers', 'Contractors')
		))->first();
			
		$subjectPrefix = 
			$tournament['name'] . ' ' .
			$tournament['location'] . ' ';
		
		$hash = md5($user['id'] . date('r'));

		$user->ticket = $hash;
		$user->ticket_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
		$user->count_requests = $user->count_requests + 1;

		if ( !$this->Users->save($user) ) {
			$this->MultipleFlash->setFlash(__d('user', 'The ticket to request a new password could not be created'), 'error');

			return;
		}

		$url = Router::url(array('action' => 'request_password', $hash), true);
		
		if (!empty($tournament['contractor']['email']))
			$replyTo = $tournament['contractor']['email'];
		else if (!empty($tournament['host']['email']))
			$replyTo = $tournament['host']['email'];
		else
			$replyTo = $this->Users->fieldByConditions('email', array(
					'group_id' => GroupsTable::getOrganizerId(),
					'tournament_id' => $tournament['id'],
					'email IS NOT NULL'
			));

		$email = new Mailer('default');
		$email
			->setEmailFormat('text')
			->setSubject($subjectPrefix . __d('user', 'Double Entries'))
			->setTo(array($user['email']))
			->setBcc($this->Users->fieldByConditions('email', array('username' => 'admin')))
			->addHeaders(array(
				'X-Tournament' => $tournament['name'],
				'X-Type' => 'Request',
				'X-' . $tournament['name'] . '-Type' => 'Request'
			))				
		;
		
		if ($replyTo !== null)
			$email->setReplyTo($replyTo);

		// $this->Email->lineLength = strlen($url) + 2;

		$textMessage = array(
			__d('user', 'A new password has been requested with your email address.'),
			'',
			__d('user', 'If you did not initiate this request, please ignore this mail. You password will remain unchanged.'),
			'',
			__d('user', 'To get a new password click the link below. A new password will then be sent to your email address.'),
			$url
		);
		
		if (Configure::read('App.magicLinks') && 
				$user['group_id'] === GroupsTable::getParticipantId()) {
			$textMessage[] = '';
			$textMessage[] = 
				__d('user', 'The link above will also log you in one time.') .
				' ' .
				__d('user', 'To login another time you have to use the password or request a new link with "Forgot Password".');
		}
		
		if (!$email->send($textMessage)) {
			$this->MultipleFlash->setFlash(__d('user', 'The email confirming your password request could not be sent'), 'error');
		} else {
			$this->MultipleFlash->setFlash(__d('user', 'An email has been sent. Follow the instructions in the mail to receive a new password'), 'success');
		}
	}

	private function _sendPasswordMail($user) {
		$subjectPrefix = '';
		$this->loadModel('Tournaments');			
		$tournament = $this->Tournaments->find('all', array(
			'contain' => array('Nations', 'Hosts', 'Organizers', 'Contractors')
		))->first();
			
		$subjectPrefix = 
			$tournament['name'] . ' ' .
			$tournament['location'] . ' ';
		
		if (!empty($tournament['contractor']['email']))
			$replyTo = $tournament['contractor']['email'];
		else if (!empty($tournament['host']['email']))
			$replyTo = $tournament['host']['email'];
		else
			$replyTo = $this->Users->fieldByConditions('email', array(
					'group_id' => GroupsTable::getOrganizerId(),
					'tournament_id' => $tournament['id'],
					'email IS NOT NULL'
			));

		// Send email
		$email = new Mailer('default');
		$email
			->setEmailFormat('text')
			->setSubject($subjectPrefix . __d('user', 'Double Entries'))
			->setTo(array($user['email']))
			->setBcc($this->Users->fieldByConditions('email', array('username' => 'admin')))
			->addHeaders(array(
				'X-Tournament' => $tournament['name'],
				'X-Type' => 'Account',
				'X-' . $tournament['name'] . '-Type' => 'Account'
			))				
		;
		
		if ($replyTo !== null)
			$email->setReplyTo($replyTo);

		$pwd = $this->Users->generatePassword($user);

		$textMessage = array(
			__d('user', 'Your new password is: ') . $pwd
		);

		$user->password = $pwd;
		$user->ticket = null;
		$user->ticket_expires = null;

		if (!$email->send($textMessage)) {
			$this->MultipleFlash->setFlash(__d('user', 'The email could not be sent, password remains unchanged'), 'error');
		} else if (!$this->Users->save($user)) {
			$this->MultipleFlash->setFlash(__d('user', 'The new password could not be saved'), 'error');
		} else {
			$this->MultipleFlash->setFlash(__d('user', 'A new password has been sent to your email address'), 'success');
		}
	}

	public function login() {
		if (!$this->request->is(['post', 'put']))
			return;

		$data = $this->request->getData();
				
		// Check for username or email address
		// XXX Why?
		$username = $data['username'];
		
		if ( $this->Users->find('all', array('conditions' => array('username' => $username)))->count() == 0 &&
			 $this->Users->find('all', array('conditions' => array('email' => $username)))->count() == 1 
			) {
			$username = $this->Users->fieldByConditions('username', array('email' => $username));
		}
		
		// Modfying the request, bad idea.
		// Better to have my own finder which would look in username and then email,
		// but return null if email is not unique
		$data['username'] = $username;
				
		$user = $this->Auth->identify();
		
		if ($user) {
			// Log it
			Log::info($this->Auth->user('username') . ' login from ' . $_SERVER['REMOTE_ADDR'], 'login');

			// Update password if still the old one. $user may still be an array
			if ($this->Auth->authenticationProvider()->needsPasswordRehash()) 
				$user['password'] = $this->request->getData('password');

			return $this->_afterLogin($user);
		} else if ($this->request->is(['post', 'put'])) {
			// Log error
			Log::info($this->request->getData('username') . ' failed login from ' . $_SERVER['REMOTE_ADDR'] . ' with password "' . $this->request->getData('password') . '"', 'login');
			$this->Users->updateAll(
					[
						new QueryExpression('count_failed = count_failed + 1'),
						new QueryExpression('count_failed_since = count_failed_since + 1')
					],
					[
						'username' => $username
					]
			);
		}
	}
	
	private function _afterLogin($user) {
		$this->Auth->setUser($user);

		$this->MultipleFlash->setFlash(__d('user', 'You are logged in'), 'success');

		// Expire any ticket
		$user['ticket'] = null;
		$user['ticket_expires'] = null;

		// Count logins
		$user['count_successful'] = $user['count_successful'] + 1;
		$user['count_failed_since'] = 0;

		// Cache it for later use
		// Update last_login and expire any ticket
		$user = $this->Users->get($this->Auth->user('id'), [
			'contain' => 'Groups'
		]);

		$this->_user = $user;

		// Set filters
		$this->loadModel('Groups');
		$this->loadModel('Tournaments');
		$this->loadModel('Languages');

		$lang_id = false;

		if ($this->request->getSession()->check('Config.language')) {
			$lang = $this->request->getSession()->read('Config.language');

			$lang_id = $this->Languages->fieldByConditions('id', array('name' => $lang));
			if ($lang_id) {
				$user->language_id = $lang_id;
				$this->Users->save($user);
			}
		} else {			
			if (!empty($lang_id)) {
				$lang = $this->Languages->fieldByConditions('name', array('id' => $lang_id));
				$this->request->getSession()->write('Config.language', $lang);
			}
		}

		// Save association that user is in charge of (if any)
		if (!UsersTable::hasRootPrivileges($this->_user)) {
			$this->request->getSession()->write('Nations.id', $this->Auth->user('nation_id'));
			$this->request->getSession()->write('Tournaments.id', $this->Auth->user('tournament_id'));
		} else {
			$this->request->getSession()->delete('Nations.id');
			$this->request->getSession()->delete('Tournaments.id');
		}

		if ($this->Tournaments->find('all')->count() === 1) {
			// Returns the id of the first record
			$this->request->getSession()->write('Tournaments.id', $this->Tournaments->fieldByConditions('id'));
		}

		$type_ids = $this->Groups->fieldByConditions('type_ids', array('id' => $this->Auth->user('group_id')));
		if ($type_ids)
			$this->request->getSession()->write('Groups.type_ids', $type_ids);
		else
			$this->request->getSession()->delete('Groups.type_ids');

		$where = null;
		// Redirect to called page
		if ($this->request->getSession()->check('Auth.redirect')) {
			$where = Router::parse($this->request->getSession()->read('Auth.redirect'));

			if (!$this->Acl->check($user, $this->_makeAclPath($where))) {
				$where = null;
				$this->request->getSession()->delete('Auth.redirect');
			}
		}

		if ($where != null)
			;
		else if (!$this->request->getSession()->check('Tournaments.id'))
			$where = array('controller' => 'tournaments', 'action' => 'index');
		else if ($this->Acl->check($user, 'Registrations/index'))
			$where = array('controller' => 'registrations', 'action' => 'index', 'tournament_id' => $this->request->getSession()->read('Tournaments.id'));
		else if ($this->Acl->check($user, 'Registrations/list_partner_wanted'))
			$where = array('controller' => 'registrations', 'action' => 'list_partner_wanted', 'tournament_id' => $this->request->getSession()->read('Tournaments.id'));
		else
			$where = array('controller' => 'users', 'action' => 'logout');

		if ($this->Acl->check($user, $this->_makeAclPath($where)))
			return $this->redirect($where);
		else {
			return $this->redirect('/'); 
		}	
	}

	public function logout() {
		// Log it
		Log::info($this->Auth->user('username') . ' logout', 'login');

		// Delete auth token: is done in CookieAuthenticate
		
		// Reset login_token
		if ($this->Auth->user('id')) {
			$user = $this->Users->get($this->Auth->user('id'));
			if ($user !== null) {
				$user->login_token = null;
				$this->Users->save($user);
			}
		}
		
		// Clear cached user
		$this->_user = null;
		
		// Destroy session and all filters therein
		$this->request->getSession()->destroy();

		$this->MultipleFlash->setFlash(__d('user', 'Good-Bye'), 'info');
			
		return $this->redirect($this->Auth->logout());
	}

	public function index() {
		if ($this->request->getQuery('group_id') !== null) {
			if ($this->request->getQuery('group_id') === 'all')
				$this->request->getSession()->delete('Groups.id');
			else
				$this->request->getSession()->write('Groups.id', $this->request->getQuery('group_id'));
		}

		if ($this->request->getQuery('username') !== null) {
			if ($this->request->getQuery('username') == 'all')
				$this->request->getSession()->delete('Users.username');
			else
				$this->request->getSession()->write('Users.username', $this->request->getQuery('username'));
		}

		$conditions = array();
		if ($this->request->getSession()->check('Groups.id')) 
			$conditions['Users.group_id'] = $this->request->getSession()->read('Groups.id');

		if ($this->request->getSession()->check('Users.username'))
			$conditions['Users.username LIKE'] = $this->request->getSession()->read('Users.username') . '%';

		$this->paginate = array(
			'contain' => array('Groups'),
			'order' => ['username' => 'ASC'],
			'conditions' => $conditions
		);
		
		$this->set('users', $this->paginate());

		$this->set('group_id', $this->request->getSession()->read('Groups.id'));
		$this->set('username', $this->request->getSession()->read('Users.username'));

		$this->loadModel('Groups');
		$this->set('groups', $this->Groups->find('list', array(
			'fields' => array('id', 'name'),
			'order' => 'name'
		))->toArray());

		$allchars = $this->Users->find('all', [
			'fields' => ['firstchar' => 'DISTINCT LEFT(UPPER(username), 1)']
		]);
		
		$allchars = Hash::extract($allchars->toArray(), '{n}.0.firstchar');
		$allchars = array_unique(array_merge(range('A', 'Z'), $allchars));
		sort($allchars);
		$this->set('allchars', $allchars);
	}

	public function view($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid user'), 'error');
			return $this->redirect(array('action' => 'index'));
		}
		$this->set('user', $this->Users->get($id, [
			'contain' => ['Nations', 'Groups', 'Tournaments']
		]));
	}

	public function add() {
		if ($this->request->getData('cancel')!== null)
			return $this->redirect(['action' => 'index']);
		
		$user = $this->Users->newEmptyEntity();
		
		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->getData();
			
			// Reset nation and tournament for root user
			if ($data['group_id'] == GroupsTable::getAdminId()) {
				$data['nation_id'] = null;
				$data['tournament_id'] = null;
			}

			// Remove confirm password from data as it is not needed anymore
			unset($data['confirm_password']);

			// Dont't set password if empty
			if ($data['password'] == '') {
				unset($data['password']);
			}

			// Unste nation_id
			$data['nation_id'] = null;

			// Only Referee and Organizer and such have tournament_id set
			if ( $data['group_id'] != GroupsTable::getRefereeId() &&
 			     $data['group_id'] != GroupsTable::getOrganizerId() &&
			     $data['group_id'] != GroupsTable::getParticipantId() &&
				 $data['group_id'] != GroupsTable::getGuestId() &&
				 $data['group_id'] != GroupsTable::getTourOperatorId() &&
				 $data['group_id'] != GroupsTable::getCompetitionManagerId() )
				$data['tournament_id'] = null;
			
			// Set prefix
			if ($data['group_id'] == GroupsTable::getParticipantId()) {
				if (empty($data['prefix_people'])) {
					$prefix = $this->Users->find()
							->select(['prefix_people' => 'MAX(prefix_people) + 1'])
							->first()
							->prefix_people;
			
					if (empty($prefix) || $prefix < 10100)
						$prefix = 10100;
					
					 $data['prefix_people'] = $prefix;
				}
			}
			
			if ($data['group_id'] == GroupsTable::getTourOperatorId()) {
				if (empty($data['prefix_people'])) {
					$prefix = $this->Users->find()
							->select(['prefix_people' => 'MAX(prefix_people) + 1'])
							->where(['group_id' => GroupsTable::getTourOperatorId()])
							->first()
							->prefix_people;

					if (empty($prefix) || $prefix < 10010)
						$prefix = 10010;
					
					$data['prefix_people'] = $prefix;
				}
			}

			$user = $this->Users->patchEntity($user, $data);
			
			if (isset($data['password']))
				$user->password = $data['password'];
			
			if ($this->Users->save($user)) {
				$this->MultipleFlash->setFlash(__('The user has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The user could not be saved. Please, try again.'), 'error');
				unset($user->password);
			}
		}

		$this->loadModel('Groups');
		$this->loadModel('Nations');
		$this->loadModel('Tournaments');

		$groups = $this->Groups->find('list', array('order' => 'name'))->toArray();

		$this->set('groups', $groups);

		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'description'),
			'order' => 'description'
		))->toArray());
		
		$this->set('tournaments', $this->Tournaments->find('list', array(
			'fields' => array('id', 'description'),
			'order' => 'start_on DESC'
		))->toArray());
		
		$this->set('user', $user);
	}

	public function edit($id = null) {
		if ($this->request->getData('cancel') !== null) {
			return $this->redirect(array('action' => 'index'));
		}

		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid user'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		$user = $this->Users->get($id);
		
		if (!$this->request->is(['post', 'put']))
			unset($user->password);
		
		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->getData();
			
			// If password is empty, leave untouched
			if (empty($data['password'])) {
				unset($data['password']);
			}

			// Unset nation_id
			$data['nation_id'] = null;

			// Only Referee and Organizer have tournament_id set
			if ( $data['group_id'] != GroupsTable::getRefereeId() &&
 			     $data['group_id'] != GroupsTable::getOrganizerId() &&
			     $data['group_id'] != GroupsTable::getParticipantId() &&
				 $data['group_id'] != GroupsTable::getGuestId() &&
				 $data['group_id'] != GroupsTable::getTourOperatorId() &&
				 $data['group_id'] != GroupsTable::getCompetitionManagerId() )
				$data['tournament_id'] = null;

			$user = $this->Users->patchEntity($user, $data);
			
			if ($this->Users->save($user)) {
				$this->MultipleFlash->setFlash(__('The user has been saved'), 'success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->MultipleFlash->setFlash(__('The user could not be saved. Please, try again.'), 'error');
				unset($user->password);
			}
		}

		$this->loadModel('Groups');
		$this->loadModel('Nations');
		$this->loadModel('Tournaments');
		$this->loadModel('Languages');

		$this->set('groups', $this->Groups->find('list', array('order' => 'name'))->toArray());
		$this->set('nations', $this->Nations->find('list', array(
			'fields' => array('id', 'description'),
			'order' => 'description'
		))->toArray());
		
		$this->set('tournaments', $this->Tournaments->find('list', array(
			'fields' => array('id', 'description'),
			'order' => 'start_on DESC'
		)));
		
		$this->set('languages', $this->Languages->find('list', array(
			'fields' => array('id', 'description'),
			'order' => array('description')
		))->toArray());
		
		$this->set('user', $user);
	}

	public function delete($id = null) {
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid id for user'), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		
		$user = $this->Users->get($id);
		
		if ($this->Users->delete($user)) {
			$this->MultipleFlash->setFlash(__('User deleted'), 'success');
			return $this->redirect(array('action'=>'index'));
		}
		$this->MultipleFlash->setFlash(__('User was not deleted'), 'error');
		return $this->redirect(array('action' => 'index'));
	}

	public function profile($id = null) {
		$redirect = $this->request->getSession()->check('Tournaments.id') ? 
			array('controller' => 'registrations', 'action' => 'index') :
			array('controller' => 'tournaments', 'action' => 'index');

		if ($this->request->getData('cancel') !== null) {
			return $this->redirect($redirect);
		}

		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid user'), 'error');
			return $this->redirect($redirect);
		}

		if ($id != $this->Auth->user('id')) {
			$this->MultipleFlash->setFlash(__('Invalid user'), 'error');
			return $this->redirect($redirect);
		}

		$user = $this->Users->get($id);

		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->getData();
			
			// Replace DOS <CR><LF> with Unix <LF> and remove empty lines
			$add_email = $data['add_email'] ?? null;
			if (!empty($add_email)) {
				$count = 1;
				while ($count)
					$add_email = str_replace(array("\r\n", "\r", "\n\n"), "\n", $add_email, $count);
			}

			// If result is empty, replace with null.
			if (!empty($add_email))
				$data['add_email'] = $add_email;
			else
				$data['add_email'] = null;

			$user = $this->Users->patchEntity($user, $data);
			
			if ($this->Users->save($user)) {
				$this->loadModel('Languages');
				$lang = $this->Languages->fieldByConditions('name', array('id' => $data['language_id']));
				$this->request->getSession()->write('Config.language', $lang);
				$this->MultipleFlash->setFlash(__d('user', 'Your profile has been saved'), 'success');
				return $this->redirect($redirect);
			} else {
				$this->MultipleFlash->setFlash(__d('user', 'Your profile could not be saved'), 'error');
			}
		}
		
		$this->set('user', $user);
	}
	
	public function onChangeLanguage() {
		$this->autoRender = false;
		
		if (!$this->request->is('ajax'))
			return;

		$id = $this->request->getData('lang');
		
		// Sometimes id is sent as array(0 => id), no idea why ...
		if (is_array($id))
			$id = $id[0];
		
		$this->loadModel('Languages');
		$lang = $this->Languages->fieldByConditions('name', array('id' => $id));
		if (empty($lang))
			return;
		
		I18n::setLocale($lang);
			
		$this->request->getSession()->write('Config.language', $lang);
		
		if ( !empty($this->Auth->user('id')) ) {
			$user = $this->Users->get($this->Auth->user('id'));
			$user->language_id = $id;
			$this->Users->save($user);
		}
	}


	public function notifications($id = null) {
		if ($this->request->getData('cancel') !== null) {
			return $this->redirect(array('action' => 'index'));
		}
		
		if (!$id) {
			$this->MultipleFlash->setFlash(__('Invalid user'), 'error');
			return $this->redirect(array('action' => 'index'));
		}

		$user = $this->Users->get($id);
		
		$this->loadModel('Notifications');

		$notification = $this->Notifications->find('all', array(
			'conditions' => array('user_id' => $user['id'])
		))->first();
		
		if ($notification === null)
			$notification = $this->Notifications->newEmptyEntity();

		if ($this->request->is(['post', 'put']))  {
			$data = $this->request->getData();
			
			if (empty($data['user_id']))
				$data['user_id'] = $user['id'];
			
			$notification = $this->Notifications->patchEntity($notification, $data);
			
			$this->Notifications->save($notification);

			return $this->redirect(array('controller' => 'users', 'action' => 'index'));
		}
		
		$this->set('notification', $notification);
		$this->set('user', $user);
		$this->set('columns', $this->Notifications->getSchema()->columns());
		
	}

	public function send_welcome_mail($id = null, $force = false) {
		if (!$id)
			return $this->redirect(array('action' => 'index'));

		$user = $this->Users->get($id);

		$user->password = '';
		
		$this->Users->save($user);	

		if (in_array($user->group_id, [
					GroupsTable::getParticipantId(), 
					GroupsTable::getTourOperatorId()
				] )) {
			$this->WelcomeMail->sendWelcomeMail($id, $force);
		} else {
			$this->_sendPasswordMail($user);
			return $this->redirect($this->referer());
		} 

		$this->MultipleFlash->setFlash(__d('user', 'A password has been sent'), 'success');

		return $this->redirect(array('action' => 'index'));
	}
}
