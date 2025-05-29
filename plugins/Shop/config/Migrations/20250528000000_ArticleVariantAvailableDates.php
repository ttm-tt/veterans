<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add order status "Refund Pending"
 */

class ArticleVariantAvailableDates extends AbstractMigration {
	public function change() {
		$this->table('shop_article_variants')
				->addColumn('available_from', 'date', [
					'after' => 'available',
					'default' => null,
					'null' => true
				])
				->addColumn('available_until', 'date', [
					'after' => 'available_from',
					'default' => null,
					'null' => true
				])
				->update()
		;
	}
}
