<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add order status "Refund Pending"
 */

class RefundPending extends AbstractMigration {
	// Up: apply migration
	public function up() {
		$this->table('shop_orders')
			->addColumn('refund', 'decimal', [
				'after' => 'cancellation_fee',
                'default' => '0.00',
                'null' => false,
                'precision' => 15,
                'scale' => 2
			])
			->update()
		;
		
		$this->execute(
			'UPDATE shop_orders ' .
			'   SET refund = paid - total - cancellation_fee - discount + cancellation_discount ' .
			' WHERE invoice_paid IS NOT NULL AND ' .
			'       (paid - total - cancellation_fee - discount + cancellation_discount) > 0'
		);
	}
	
	
	// Down: revert migration
	public function down() {
		$this->table('shop_orders')
			->removeColumn('refund')
		;
	}
}
