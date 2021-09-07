<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add order status "Refund Pending"
 */

class AddRoletype extends AbstractMigration {
	public function change() {
		$this->table('shop_articles')
				->addColumn('roletype', 'boolean', [
					'length' => 1,
					'after' => 'visible',
					'default' => 0
				])
				->update()
		;
	}
}
