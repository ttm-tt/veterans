<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class NotificationsDelete extends AbstractMigration {
	public function change() {
		$this->table('notifications')
				->addColumn('delete_registration_player', 'boolean', [
					'after' => 'new_player',
					'null' => false,
					'default' => 1
				])
				->save()
		;
	}
}

