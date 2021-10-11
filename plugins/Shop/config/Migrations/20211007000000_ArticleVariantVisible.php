<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add order status "Refund Pending"
 */

class ArticleVariantVisible extends AbstractMigration {
	public function change() {
		$this->table('shop_article_variants')
				->addColumn('visible', 'boolean', [
					'length' => 1,
					'after' => 'variant_type',
					'default' => 1
				])
				->update()
		;
	}
}
