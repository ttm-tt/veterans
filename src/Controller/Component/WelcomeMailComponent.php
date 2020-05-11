<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
/* Send welcome mail to user */
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Mailer\Email;
use Cake\Log\Log;

use App\Model\Table\GroupsTable;



class WelcomeMailComponent extends Component {
	var $components = array('Auth');

	var $controller;
	public function sendWelcomeMail($id = false, $force = false) {
		if (is_array($id)) {
			foreach ($id as $i)
				$this->sendWelcomeMail($i, $force);

			return;
		}
		
		$this->getController()->loadModel('Users');

		$conditions = array();
		
		if (!empty($id))
			$conditions['Users.id'] = $id;
		else
			$conditions['Users.group_id'] = GroupsTable::getParticipantId ();

		$users = $this->getController()->Users->find('all', array(
			'conditions' => $conditions
		));

		$debug = Configure::read('debug') > 0;

		foreach ($users as $user) {
			// Restart execution timer
			set_time_limit(60);

			if (empty($user['email'])) {
				$this->log('Empty email for user ' . $user['username'], 'debug');
				continue;
			}

			if (!$debug && strpos($user['email'], '@localhost') > 0) {
				$this->log('localhost email for user ' . $user['username'], 'debug');
				continue;
			}

			if (!$force && $user['password'] != '') {
				$this->log('User ' . $user['username'] . ' has a password', 'debug');
				continue;
			}

			$password = null;
			
			$subjectPrefix = '';
			$replyTo = null;

			if (!empty($user['tournament_id'])) {
				$this->getController()->loadModel('Tournaments');			
				$tournament = $this->getController()->Tournaments->find('all', array(
					'conditions' => array('Tournaments.id' => $user['tournament_id']),
					'contain' => array('Nations', 'Hosts', 'Organizers', 'Contractors')
				))->first();
			} else if ($this->getController()->getRequest()->getSession()->check('Tournaments.id')) {
				$this->getController()->loadModel('Tournaments');			
				$tournament = $this->getController()->Tournaments->find('all', array(
					'conditions' => array('Tournaments.id' => $this->getController()->getRequest()->getSession()->read('Tournaments.id')),
					'contain' => array('Nations', 'Hosts', 'Organizers', 'Contractors')
				))->first();
			}
			
			if (!empty($tournament))
				$subjectPrefix = 
					$tournament['name'] . ' ' .
					$tournament['location'] . ' ';

			if (!empty($tournament['contractor']['email']))
				$replyTo = $tournament['contractor']['email'];
			else if (!empty($tournament['host']['email']))
				$replyTo = $tournament['host']['email'];

			if ($user['password'] == '') {
				$password = $this->getController()->Users->generatePassword($user);
				$user['password'] = $password;
				if (!$this->getController()->Users->save($user)) {
					$this->log('Could not save password for ' . $user['username'], 'error');
					continue;
				}
			}

			if (empty($replyTo))
				$replyTo = $this->getController()->Users->fieldByConditions('email', array(
					'group_id' => GroupsTable::getOrganizerId(),
					'tournament_id' => $tournament['id'],
					'email IS NOT NULL'
				));

			$email = new Email('default');
			$email
				->setEmailFormat('both')
				->setSubject($subjectPrefix . __d('user', 'Double Entries'))
				->setTo(array($user['email']))
			;
			
			if ($replyTo !== null)
				$email->setReplyTo($replyTo);
			
			if (!empty($tournament)) {
				$email
					->addHeaders(array(
						'X-Tournament' => $tournament['name'],
						'X-Type' => 'Welcome',
						'X-' . $tournament['name'] . '-Type' => 'Welcome'
					))
				;
			}
			
			$this->getController()->loadModel('Languages');
			
			$lang = '';
			if ( !empty($user['language_id']) )
				$lang = $this->getController()->Languages->fieldByConditions('name', array('id' => $user['language_id']));
			
			if (empty($lang))
				$lang = 'en' . DS;
			else
				$lang = $lang . DS;
					
			if ($user['group_id'] == GroupsTable::getTourOperatorId())
				$email->viewBuilder()->setTemplate('welcome_group');
			else
				$email->viewBuilder()->setTemplate($lang . 'welcome');
								
			if (!empty($user['add_email']))
				$email->setCc(explode("\n", $user['add_email']));
			
			$email->setBcc($this->getController()->Users->fieldByConditions('email', array('username' => 'admin')));

			$email->setViewVars(array(
				'password' => $password,
				'email' => $user['email'],
				'tournament' => $tournament
			));
			
			$email->send();

			Log::info('Send password to ' . $user['username'], 'login');
		}
	}
}
