<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddBodyTop extends AbstractMigration {
	public function change() {
		$this->table('shop_settings')
				->addColumn('invoice_add_body_top', 'text', [
					'default' => null,
					'limit' => null,
					'null' => true
				])
				->update()
		;
	}
}
