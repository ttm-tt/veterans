<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add order status "Refund Pending"
 */

class RemoveArticlesCancellationfee extends AbstractMigration {
	public function change() {
		$this->table('shop_articles')
				->removeColumn('cancellation_fee')
				->update()
		;
	}
}
