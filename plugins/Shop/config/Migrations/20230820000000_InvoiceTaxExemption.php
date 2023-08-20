<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class InvoiceTaxExemption extends AbstractMigration {
	public function change() {
		$this->table('shop_settings')
				->addColumn('invoice_tax_exemption', 'text', [
					'after' => 'invoice_add_body_top',
					'default' => null,
					'null' => true
				])
				->update()
		;
	}
}
