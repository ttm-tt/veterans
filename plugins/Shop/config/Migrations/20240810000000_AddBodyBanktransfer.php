<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddBodyBanktransfer extends AbstractMigration {
	public function change() {
		$this->table('shop_settings')
				->addColumn('invoice_add_body_banktransfer', 'text', [
					'after' => 'invoice_no',
					'default' => null,
					'null' => true
				])
				->update()
		;
	}
}
