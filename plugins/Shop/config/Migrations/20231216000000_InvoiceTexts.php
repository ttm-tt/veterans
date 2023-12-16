<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class InvoiceTexts extends AbstractMigration {
	public function change() {
		$this->table('shop_settings')
				->addColumn('invoice_title', 'string', [
					'after' => 'banktransfer',
					'default' => null,
					'null' => true
				])
				->addColumn('invoice_date', 'string', [
					'after' => 'invoice_title',
					'default' => null,
					'null' => true
				])
				->addColumn('invoice_no', 'string', [
					'after' => 'invoice_date',
					'default' => null,
					'null' => true
				])
				->update()
		;
	}
}
