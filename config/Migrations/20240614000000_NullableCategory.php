<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class NullableCategory extends AbstractMigration {
	public function change() {
		$this->table('competitions')
				->changeColumn('category', 'string', [
					'null' => true,
					'limit' => 64
				])
				->save()
		;
		
		$this->execute(
				'UPDATE competitions SET category = NULL WHERE category = "null"'
		);
	}
}

