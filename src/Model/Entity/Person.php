<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Person Entity.
 */
class Person extends Entity
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
	protected function _getBorn() {
		return $this->dob === null ? null : $this->dob->format('Y');
	}
	
	protected function _getName() {
		if (empty($this->last_name))
			return null;
		
		if (empty($this->first_name))
			return $this->last_name;
		
		return $this->last_name . ', ' . $this->first_name;
	}	
}
?>