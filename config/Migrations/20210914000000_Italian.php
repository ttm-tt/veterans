<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Italian extends AbstractMigration {
	public function change() {
		$this->table('languages')
				->insert([
					'name' => 'it',
					'description' => 'Italiano'
				])
				->update();
		;
	}
}
