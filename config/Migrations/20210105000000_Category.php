<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Category extends AbstractMigration {
	public function change() {
		$this->table('competitions')
				->addColumn('category', 'string', [
					'after' => 'description',
					'default' => 'null',
					'limit' => 64
				])
				->update()
		;
	}
}
