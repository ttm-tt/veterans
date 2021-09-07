<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Table;

use App\Model\Table\AppTable;
use Cake\ORM\TableRegistry;

class TypesTable extends AppTable {
	private static $_map = null;
	
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->hasMany('Registrations');
	}

	public static function getPlayerId() {
		return TypesTable::_getId('PLA');
	}


	public static function getAccId() {
		return TypesTable::_getId('ACC');
	}
	
	
	public static function getCoachId() {
		return TypesTable::_getId('COA');
	}


	public static function getUmpireId() {
		return TypesTable::_getId('UMP');
	}

	public static function getRefereeId() {
		return TypesTable::_getId('REF');
	}

	public static function getPressId() {
		return TypesTable::_getId('PRE');
	}

	public static function getTelevisionId() {
		return TypesTable::_getId('TV');
	}

	public static function getSupplierId() {
		return TypesTable::_getId('SUP');
	}

	public static function getDelegateId() {
		return TypesTable::_getId('DEL');
	}

	// I need this at sesveral places
	public static function getOrganizerTypeIds() {
		return array(
			TypesTable::getPressId(),
			TypesTable::getTelevisionId(),
			TypesTable::getSupplierId()
		);
	}


	// Don't repeat yourself
	private static function _getId($name) {
		if (TypesTable::$_map == null) {
			$type = TableRegistry::get('Types');
			TypesTable::$_map = $type->find('list', array('fields' => array('name', 'id')))->toArray();
		}

		return TypesTable::$_map[$name] ?? null;
	}
}
?>
