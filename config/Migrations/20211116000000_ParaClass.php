<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class ParaClass extends AbstractMigration {
	public function change() {
		$this->table('people')
				->addColumn('ptt_class', 'integer', [
					'after' => 'extern_id',
					'null' => false,
					'default' => 0
				])
				->addColumn('ptt_wchc', 'integer', [
					'after' => 'ptt_class',
					'null' => false,
					'default' => 0,					
				])
				->update();
		;
		
		$this->table('competitions')
				->addColumn('ptt_class', 'integer', [
					'after' => 'born',
					'null' => false,
					'default' => 0
				])
				->update();
	}
}
