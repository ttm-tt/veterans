<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CurrencyConversion extends AbstractMigration {
	public function change() {
		$this->table('shop_orders')
				->addColumn('payment_amount', 'decimal', [
					'after' => 'payment_method',
					'default' => null,
					'null' => true,
					'precision' => 15,
					'scale' => 3
				])
				->addColumn('payment_currency', 'string', [
					'after' => 'payment_total',
					'default' => null,
					'null' => true,
					'limit' => 3
				])
				->update()
		;
	}
}
