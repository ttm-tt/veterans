<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add order status "Refund Pending"
 */

class AddPaymentPaypal extends AbstractMigration {
	public function change() {
		$this->table('shop_settings')
				->addColumn('paypal', 'boolean', [
					'length' => 1,
					'after' => 'banktransfer',
					'default' => 0,
					'null' => false
				])
				->update()
		;
	}
}
