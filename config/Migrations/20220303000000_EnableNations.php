<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class EnableNations extends AbstractMigration {
	public function change() {
		$this->table('nations')
				->addColumn('enabled', 'boolean', [
					'after' => 'continent',
					'default' => 1,
					'null' => false
				])
				->update()
		;
	}
}
