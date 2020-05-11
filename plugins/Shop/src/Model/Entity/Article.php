<?php
namespace Shop\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\Behavior\Translate\TranslateTrait;

/**
 * Article Entity.
 */
class Article extends Entity
{
	use TranslateTrait;
	
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
		'_translations' => true,
        'id' => false,
    ];	
}

