<?php
namespace Shop\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrderAddress Entity.
 */
class OrderAddress extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];
	
	// -------------------------------------------------------------------
	// Virtual fields
	protected function _getDisplayName() {
		if (empty($this->last_name))
			return null;
		
		if (empty($this->first_name))
			return $this->last_name;
		
		return $this->last_name . ', ' . $this->first_name;
	}	
}
?>