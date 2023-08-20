<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddBodyBottom extends AbstractMigration {
	public function change() {
		$this->table('shop_settings')
				->renameColumn('add_footer', 'invoice_add_footer')
				->addColumn('invoice_add_body_bottom', 'text', [
					'after' => 'invoice_add_body_top',
					'default' => null,
					'null' => true
				])
				->update()
		;
	}
}
