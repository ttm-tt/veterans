<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Table;

use App\Model\Table\AppTable;

use Acl\Model\Behavior\AclBehavior;
use Cake\ORM\TableRegistry;

class GroupsTable extends AppTable {
	var $actsAs = array('Acl.Acl' => array('type' => 'requester'));
	
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->belongsTo('ParentGroups', [
			'className' => 'Groups',
			'foreignKey' => 'parent_id'
		]);
		
		$this->hasMany('ChildGroups', [
			'className' => 'Groups',
			'foreignKey' => 'parent_id'
		]);
		
		$this->hasMany('Users');
	}

	private static $_map = null;

	public static function getAdminId() {
		return GroupsTable::_getId('Administrator');
	}

	public static function getOrganizerId() {
		return GroupsTable::_getId('Organizer');
	}


	public static function getRefereeId() {
		return GroupsTable::_getId('Referee');
	}


	public static function getParticipantId() {
		return GroupsTable::_getId('Participant');
	}


	public static function getGuestId() {
		return GroupsTable::_getId('Guest');
	}
	
	
	public static function getTourOperatorId() {
		return GroupsTable::_getId('Tour Operator');
	}


	public static function getCompetitionDirectorId() {
		return GroupsTable::_getId('Competition Director');
	}


	public static function getCompetitionManagerId() {
		return GroupsTable::_getId('Competition Manager');
	}


	// Don't repeat yourself
	private static function _getId($name) {
		if (self::$_map == null) {
			$group = TableRegistry::get('Groups');
			self::$_map = $group->find('list', array('fields' => array('name', 'id')))->toArray();
		}

		return self::$_map[$name];
	}


	function parentNode() {
		return null;
	}
}
?>
