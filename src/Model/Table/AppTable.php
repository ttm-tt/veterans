<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       cake
 * @subpackage    cake.app
 */
namespace App\Model\Table;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Entity;

use Shim\Model\Table\Table as ShimTable;


class AppTable extends ShimTable {
	var $actsAs = []; // array('Containable');
	var $components = array('Auth');
	
	public function allFields($alias) {
		$fields = [];
		foreach ($this->schema()->columns() as $col) {
			$fields[$alias . '__' . $col] = $alias . '.' . $col;
		}
		
		return $fields;
	}

	
	// Retrieve record but also catch null
	public function record($id, array $options = []) {
		if ($id === null)
			return null;
		
		return parent::record($id, $options);
	}

	
	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		$ct = $options['modified'] ?? new \Cake\I18n\FrozenTime('now');
		
		// We will set created and modified here
		unset($entity->created);
		unset($entity->modified);

		// Sometimes we include the model for the id only when we save associated models.
		// In this case don't change the modified field.
		// CakePHP v3: does original always include modified?
		if ($entity->isNew()) {
			$entity->created = $ct;
			$entity->modified = $ct;
		} else if (isset($options['modified']) && !$options['modified']) {
			$entity->setDirty('created', false);
			$entity->setDirty('modified', false);
		} else {
			foreach ($entity->getDirty() as $p) {
				if (!isset($entity->{$p}))
					continue;
				
				if ($entity->{$p} instanceof Entity)
					continue;

				// Resolves schema
				if (!$this->hasField($p))
					continue;
				
				// Sometimes we have the id set
				if ($p === $this->getPrimaryKey())
					continue;
				
				$entity->modified = $ct;
				break;
			}
		}
		
		// return parent::beforeSave($event, $entity, $options);
	}

	
	public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		
	}
	
	public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options) {
	
	}
	
	
	// Return true if a direct property of this object was modified
	// The fields modified and created are exempt
	// Reason is that CakePHP isDirty return true even if an association only was modfied
	protected function _isEntityDirty(Entity $entity) {
		$original = $entity->getOriginalValues();
		
		foreach (array_keys($original) as $key) {
			if ($entity->get($key) instanceof Entity)
				continue;
			
			if ($key === 'modified' || $key === 'created')
				continue;
			
			if ($entity->dirty($key))
				return true;
		}
		
		return false;
	}
}
