<?php
namespace Shop\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrderAddress Entity.
 */
class Order extends Entity
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
	protected function _getOutstanding() {
		return $this->total - $this->discount + $this->cancellation_fee - $this->cancellation_discount - $this->paid;
	}	
}
?>