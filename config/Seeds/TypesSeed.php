<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * Types seed.
 */
class TypesSeed extends AbstractSeed
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
                'id' => '1',
                'name' => 'PLA',
                'description' => 'Player',
            ],
            [
                'id' => '2',
                'name' => 'COA',
                'description' => 'Coach',
            ],
            [
                'id' => '3',
                'name' => 'DEL',
                'description' => 'Delegate to congress',
            ],
            [
                'id' => '4',
                'name' => 'MED',
                'description' => 'Medical Personnel',
            ],
            [
                'id' => '5',
                'name' => 'ACC',
                'description' => 'Accompanying Person',
            ],
            [
                'id' => '6',
                'name' => 'UMP',
                'description' => 'Umpire',
            ],
            [
                'id' => '7',
                'name' => 'REF',
                'description' => 'Referee',
            ],
            [
                'id' => '8',
                'name' => 'PRE',
                'description' => 'Press',
            ],
            [
                'id' => '9',
                'name' => 'TV',
                'description' => 'Television',
            ],
            [
                'id' => '10',
                'name' => 'SUP',
                'description' => 'Supplier',
            ],
            [
                'id' => '11',
                'name' => 'OFF',
                'description' => 'Official',
            ],
            [
                'id' => '12',
                'name' => 'ORG',
                'description' => 'Organizer',
            ],
        ];

        $table = $this->table('types');
        $table->insert($data)->save();
    }
}
