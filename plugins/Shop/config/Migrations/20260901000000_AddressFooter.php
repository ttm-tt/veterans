<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add order status "Refund Pending"
 */

class AddressFooter extends AbstractMigration {
	public function change() {
		$this->table('shop_order_addresses')
				->addColumn('footer', 'string', [
					'length' => 64,
					'after' => 'phone',
					'default' => null,
					'null' => true
				])
				->update()
		;
	}
}
