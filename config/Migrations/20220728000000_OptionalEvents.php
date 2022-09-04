<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class OptionalEvents extends AbstractMigration {
	public function change() {
		$this->table('competitions')
				->addColumn('optin', 'boolean', [
					'after' => 'born',
					'default' => 0,
					'null' => false
				])
				->update()
		;
	}
}
