<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class PersonHistories extends AbstractMigration {
	public function change() {
		$this->table('person_histories')
            ->addColumn('person_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('field_name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('old_value', 'string', [
                'default' => null,
                'limit' => 4096,
                'null' => true,
            ])
            ->addColumn('new_value', 'string', [
                'default' => null,
                'limit' => 4096,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'person_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->create();
		
        $this->table('person_histories')
            ->addForeignKey(
                'person_id',
                'people',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->update();
	}
}
