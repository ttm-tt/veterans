<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ArosFixture
 */
class ArosFixture extends TestFixture
{
    /**
     * Import
     *
     * @var array
     */
    public $import = ['table' => 'aros'];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 974,
                'parent_id' => null,
                'model' => 'Groups',
                'foreign_key' => 1,
                'alias' => 'Administrator',
                'lft' => 1,
                'rght' => 12,
            ],
            [
                'id' => 978,
                'parent_id' => null,
                'model' => 'Groups',
                'foreign_key' => 5,
                'alias' => 'Organizer',
                'lft' => 13,
                'rght' => 14,
            ],
            [
                'id' => 979,
                'parent_id' => null,
                'model' => 'Groups',
                'foreign_key' => 7,
                'alias' => 'Referee',
                'lft' => 15,
                'rght' => 16,
            ],
            [
                'id' => 980,
                'parent_id' => null,
                'model' => 'Groups',
                'foreign_key' => 8,
                'alias' => 'Participant',
                'lft' => 17,
                'rght' => 20,
            ],
            [
                'id' => 981,
                'parent_id' => null,
                'model' => 'Groups',
                'foreign_key' => 9,
                'alias' => 'Rpc',
                'lft' => 21,
                'rght' => 24,
            ],
            [
                'id' => 982,
                'parent_id' => null,
                'model' => 'Groups',
                'foreign_key' => 10,
                'alias' => 'Guest',
                'lft' => 25,
                'rght' => 26,
            ],
            [
                'id' => 983,
                'parent_id' => 974,
                'model' => 'Users',
                'foreign_key' => 81,
                'alias' => 'admin',
                'lft' => 2,
                'rght' => 3,
            ],
            [
                'id' => 985,
                'parent_id' => 974,
                'model' => 'Users',
                'foreign_key' => 84,
                'alias' => 'ettu',
                'lft' => 4,
                'rght' => 5,
            ],
            [
                'id' => 986,
                'parent_id' => 974,
                'model' => 'Users',
                'foreign_key' => 89,
                'alias' => 'theis',
                'lft' => 6,
                'rght' => 7,
            ],
            [
                'id' => 988,
                'parent_id' => 981,
                'model' => 'Users',
                'foreign_key' => 713,
                'alias' => 'rpc2',
                'lft' => 22,
                'rght' => 23,
            ],
            [
                'id' => 1028,
                'parent_id' => null,
                'model' => 'Groups',
                'foreign_key' => 11,
                'alias' => null,
                'lft' => 27,
                'rght' => 28,
            ],
            [
                'id' => 1029,
                'parent_id' => null,
                'model' => 'Groups',
                'foreign_key' => 12,
                'alias' => null,
                'lft' => 29,
                'rght' => 30,
            ],
            [
                'id' => 1030,
                'parent_id' => 974,
                'model' => 'Users',
                'foreign_key' => 714,
                'alias' => null,
                'lft' => 8,
                'rght' => 9,
            ],
            [
                'id' => 1031,
                'parent_id' => 974,
                'model' => 'Users',
                'foreign_key' => 715,
                'alias' => null,
                'lft' => 10,
                'rght' => 11,
            ],
            [
                'id' => 1033,
                'parent_id' => 980,
                'model' => 'Users',
                'foreign_key' => 717,
                'alias' => null,
                'lft' => 18,
                'rght' => 19,
            ],
        ];
        parent::init();
    }
}
