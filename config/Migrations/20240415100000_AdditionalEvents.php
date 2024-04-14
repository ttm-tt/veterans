<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AdditionalEvents extends AbstractMigration {
	public function change() {
		$this->table('participants')
				->addColumn('single_2_id', 'integer', [
					'after' => 'single_cancelled',
					'null' => true,
					'default' => null
				])
				->addColumn('single_2_cancelled', 'boolean', [
					'after' => 'single_2_id',
					'null' => false,
					'default' => 0
				])
				->addIndex(['single_2_id'])
				->save()
		;
		$this->table('participants')
				->addForeignKey('single_2_id', 'competitions', 'id', [
					'update' => 'RESTRICT',
					'delete' => 'SET_NULL'
				])
				->save()
		;
		
	}
}
