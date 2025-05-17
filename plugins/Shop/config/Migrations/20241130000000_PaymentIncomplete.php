<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add order status "Refund Pending"
 */

class PaymentIncomplete extends AbstractMigration {
	public function up() {
		$inco = [
			'name' => 'INCO',
			'description' => 'Payment Incomplete'
		];
		
		$table = $this->table('shop_order_status');
		$table->insert($inco)->save();
		$table->saveData();
	}
	
	public function down() {
		$this->execute('DELETE FROM shop_order_status WHERE name = "INCO"');
		// Reset aauto_increment, mysql will set it to the lowest possible value
		$this->execute('ALTER TABLE shop_order_status AUTO_INCREMENT=0');
	}
}
