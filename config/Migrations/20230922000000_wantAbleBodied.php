<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class WantAbleBodied extends AbstractMigration {
	public function change() {
		$this->table('people')
				->addColumn('ptt_nonpara', 'boolean', [
					'after' => 'ptt_wchc',
					'default' => 0,
					'null' => false
				])
				->update()
		;
	}
}
