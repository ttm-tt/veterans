<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class VariantsVisible extends AbstractMigration {
	public function change() {
		$this->table('shop_article_variants')
				->addColumn('visible', 'boolean', [
					'after' => 'variant_type',
					'default' => 1,
					'null' => false
				])
				->update()
		;
	}
}
