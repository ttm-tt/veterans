<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add order status "Refund Pending"
 */

class ArticleVariantAvailable extends AbstractMigration {
	public function change() {
		$this->table('shop_article_variants')
				->addColumn('available', 'integer', [
					'after' => 'visible',
					'default' => null,
					'null' => true
				])
				->update()
		;
	}
}
