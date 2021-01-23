<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * GroupsFixture
 */
class GroupsFixture extends TestFixture
{
	public $import = ['model' => 'Groups'];

	public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Administrator',
                'parent_id' => null,
                'type_ids' => ''
            ],
            [
                'id' => 5,
                'name' => 'Organizer',
                'parent_id' => null,
                'type_ids' => ''
            ],
            [
                'id' => 7,
                'name' => 'Referee',
                'parent_id' => null,
                'type_ids' => '6,7'
            ],
            [
                'id' => 8,
                'name' => 'Participant',
                'parent_id' => null,
                'type_ids' => '1,5'
            ],
            [
                'id' => 6,
                'name' => 'Rpc',
                'parent_id' => null,
                'type_ids' => ''
            ],
            [
                'id' => 10,
                'name' => 'Guest',
                'parent_id' => null,
                'type_ids' => '1'
            ],
            [
                'id' => 11,
                'name' => 'Tour Operator',
                'parent_id' => null,
                'type_ids' => ''
            ],
            [
                'id' => 12,
                'name' => 'Competition Manager',
                'parent_id' => null,
                'type_ids' => ''
            ],
        ];
        parent::init();
    }
}
