<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Newsletter extends AbstractMigration {
	public function change() {
		$this->table('people')
				->addColumn('newsletter', 'boolean', [
					'after' => 'dob',
					'null' => false,
					'default' => false
				])
				->update()
		;
		$this->table('users')
				->addColumn('newsletter', 'boolean', [
					'after' => 'add_email',
					'null' => false,
					'default' => false
				])
				->update()
		;
	}
}
