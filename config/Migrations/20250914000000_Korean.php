<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Korean extends AbstractMigration {
	public function change() {
		$this->table('languages')
				->insert([
					'name' => 'kr',
					'description' => '한국어 (Korean)'
				])
				->update();
		;
	}
}
