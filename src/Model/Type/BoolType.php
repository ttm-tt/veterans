<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php

namespace App\Model\Type;

use Cake\Database\Type\BoolType as CakeBoolType;

// Overwrite BoolType::marshal until CakePHP 4.x solves a regression
class BoolType extends CakeBoolType {
    public function marshal($value): ?bool
    {
		// Empty string should be false, otherwise a hidden value false in a form
		// would be converted to null, which may not be stored in a columnd 
		// defined as "DEFAULT 0 NOT NULL"
		if ($value === '')
			return false;

		return parent::marshal($value);
    }
}
