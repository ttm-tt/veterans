<?php /* Copyright (c) 2024 Christoph Theis */ ?>
<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Competition Entity.
 */
class Competition extends Entity
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
	// born is nullable but we can't use null as key or group field in findList.
	// So add a virtual field which returns 0 instead, but keep it nullable
	protected function _getYob() {
		return $this->born ?? 0;
	}
}
