<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Table;

use App\Model\Table\AppTable;
use App\Model\Table\GroupsTable;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Entity;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Cake\Auth\DefaultPasswordHasher;

use Cake\I18n\Time;

class UsersTable extends AppTable {
	var $actsAs = array('Acl.Acl' => array('type' => 'requester'));

	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->addAssociations([
			'belongsTo' => [
				'Groups',
				'Nations',
				'Tournaments',
				'Languages'
			],
			'hasMany' => [
				'People'
			],
			'hasOne' => [
				// Should be hasMany but then some stuff will not work
				// Anyway, per tournament there can be only one
				'Notifications' 
			]
		]);
	}

	public function validationDefault(Validator $validator) : Validator {		
		// Username may not be empty
		$validator->notEmptyString('username');
		
		return $validator;
	}
	
	// Application rules
	public function buildRules(RulesChecker $rules) : RulesChecker {
		// Username must be unique
		$rules->add($rules->isUnique(['username'], __('A user with this name already exists')));
		
		// Prefix must be unique
		$rules->add($rules->isUnique(['prefix_people'], __('The prefix is already in use')));
		
		return $rules;
	}
	
	
	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		parent::beforeSave($event, $entity, $options);
		
		// Hash some fields
		// If a the password field is empty don't change it
		foreach (['password', 'login_token'] as $field) {
			if ($entity->isDirty($field)) {
				if (!empty($entity->{$field}))
					$entity->{$field} = (new DefaultPasswordHasher())->hash($entity->{$field});
				else if ($field === 'password')
					$entity->setDirty($field, false);
			}
		}
	}

	public static function hasRootPrivileges($user) {
		return $user['group_id'] == GroupsTable::getAdminId();
	}


	public function parentNode() {
    	if (!$this->id && empty($this->data)) {
        	return null;
	    }
    	if (isset($this->data['group_id'])) {
			$groupId = $this->data['group_id'];
	    } else {
    		$groupId = $this->field('group_id');
	    }
    	if (!$groupId) {
			return null;
	    } else {
    	    return array('group' => array('id' => $groupId));
	    }
	}

/*
	public function bindNode($user) {
		// ACL is taken from Group
		return array('Group' => array('id' => $user['User']['group_id']));
	}
*/

	// ===================================================================
	// Generate new password
	public function generatePassword($user) {
		$chars = array(
			'23456789',                   // no '0' and '1', they are similar to '0' and '1'
			'abcdefghkmnpqrstuvwxyz',     // no 'o' and 'l', they are similar to '0' and '1'. No 'i' and 'j' either
			'ABCDEFGHJKLMNPQRSTUVWXYZ'    // no 'O and 'I' either
		);

		if ($user['group_id'] != GroupsTable::getParticipantId())
			$chars[] = '!%=+#/()';       // some special characters

		$pwd = '';

		while (strlen($pwd) < 10) {
			$tmp = $chars[mt_rand(0, count($chars) - 1)];
			$c = substr($tmp, mt_rand(0, strlen($tmp) - 1), 1);
			if (!strstr($pwd, $c))
				$pwd .= $c;
		}

		return $pwd;
	}
	
	
	// Update user entity upon login
	public function loginUser($user) {
		if (empty($user['id']))
			return false;
		
		if (!($user instanceof Entity))
			$user = $this->patchEntity($this->get($user['id']), $user);
		
		$user->last_login = Time::now();
		
		$this->save($user, ['modified' => false]);
		
		return $user;
	}
	
	// Update user entity upon logout
	public function logoutUser($user) {
		if (empty($user['id']))
			return false;
		
		if (!($user instanceof Entity))
			$user = $this->patchEntity($this->get($user['id']));
		
		$this->save($user, ['modified' => false]);
	}
}
?>
