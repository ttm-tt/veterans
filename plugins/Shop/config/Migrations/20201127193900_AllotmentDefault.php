<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add order status "Refund Pending"
 */

class AllotmentDefault extends AbstractMigration {
	// Up: apply migration
	public function up() {
		$this->table('shop_allotments')
			->changeColumn('allotment', 'integer', [
                'default' => '0',
			])
			->update()
		;
	}
		
	// Down: revert migration
	public function down() {

	}
}
