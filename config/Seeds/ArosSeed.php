<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * Aros seed.
 */
class ArosSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => '974',
                'parent_id' => NULL,
                'model' => 'Groups',
                'foreign_key' => '1',
                'alias' => 'Administrator',
                'lft' => '1',
                'rght' => '12',
            ],
            [
                'id' => '978',
                'parent_id' => NULL,
                'model' => 'Groups',
                'foreign_key' => '5',
                'alias' => 'Organizer',
                'lft' => '13',
                'rght' => '14',
            ],
            [
                'id' => '979',
                'parent_id' => NULL,
                'model' => 'Groups',
                'foreign_key' => '7',
                'alias' => 'Referee',
                'lft' => '15',
                'rght' => '16',
            ],
            [
                'id' => '980',
                'parent_id' => NULL,
                'model' => 'Groups',
                'foreign_key' => '8',
                'alias' => 'Participant',
                'lft' => '17',
                'rght' => '18',
            ],
            [
                'id' => '981',
                'parent_id' => NULL,
                'model' => 'Groups',
                'foreign_key' => '9',
                'alias' => 'Rpc',
                'lft' => '19',
                'rght' => '22',
            ],
            [
                'id' => '982',
                'parent_id' => NULL,
                'model' => 'Groups',
                'foreign_key' => '10',
                'alias' => 'Guest',
                'lft' => '23',
                'rght' => '24',
            ],
            [
                'id' => '983',
                'parent_id' => '974',
                'model' => 'Users',
                'foreign_key' => '81',
                'alias' => 'admin',
                'lft' => '2',
                'rght' => '3',
            ],
            [
                'id' => '985',
                'parent_id' => '974',
                'model' => 'Users',
                'foreign_key' => '84',
                'alias' => 'ettu',
                'lft' => '4',
                'rght' => '5',
            ],
            [
                'id' => '986',
                'parent_id' => '974',
                'model' => 'Users',
                'foreign_key' => '89',
                'alias' => 'theis',
                'lft' => '6',
                'rght' => '7',
            ],
            [
                'id' => '988',
                'parent_id' => '981',
                'model' => 'Users',
                'foreign_key' => '713',
                'alias' => 'rpc2',
                'lft' => '20',
                'rght' => '21',
            ],
            [
                'id' => '1028',
                'parent_id' => NULL,
                'model' => 'Groups',
                'foreign_key' => '11',
                'alias' => NULL,
                'lft' => '25',
                'rght' => '26',
            ],
            [
                'id' => '1029',
                'parent_id' => NULL,
                'model' => 'Groups',
                'foreign_key' => '12',
                'alias' => NULL,
                'lft' => '27',
                'rght' => '28',
            ],
            [
                'id' => '1030',
                'parent_id' => '974',
                'model' => 'Users',
                'foreign_key' => '714',
                'alias' => NULL,
                'lft' => '8',
                'rght' => '9',
            ],
            [
                'id' => '1031',
                'parent_id' => '974',
                'model' => 'Users',
                'foreign_key' => '715',
                'alias' => NULL,
                'lft' => '10',
                'rght' => '11',
            ],
        ];

        $table = $this->table('aros');
        $table->insert($data)->save();
    }
}
