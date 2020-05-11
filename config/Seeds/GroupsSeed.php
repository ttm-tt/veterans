<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * Groups seed.
 */
class GroupsSeed extends AbstractSeed
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
                'name' => 'Administrator',
                'parent_id' => NULL,
                'type_ids' => '',
                'modified' => '2011-05-08 18:01:48',
                'created' => '2010-11-21 17:13:55',
            ],
            [
                'id' => '5',
                'name' => 'Organizer',
                'parent_id' => NULL,
                'type_ids' => '',
                'modified' => '2011-07-31 20:27:16',
                'created' => '2011-07-31 20:27:16',
            ],
            [
                'id' => '7',
                'name' => 'Referee',
                'parent_id' => NULL,
                'type_ids' => '6,7',
                'modified' => '2012-03-22 20:23:02',
                'created' => '2012-03-22 20:23:02',
            ],
            [
                'id' => '8',
                'name' => 'Participant',
                'parent_id' => NULL,
                'type_ids' => '1,5',
                'modified' => '2012-04-22 13:54:00',
                'created' => '2012-04-22 13:54:00',
            ],
            [
                'id' => '9',
                'name' => 'Rpc',
                'parent_id' => NULL,
                'type_ids' => '',
                'modified' => '2012-04-22 17:40:13',
                'created' => '2012-04-22 17:40:13',
            ],
            [
                'id' => '10',
                'name' => 'Guest',
                'parent_id' => NULL,
                'type_ids' => '1',
                'modified' => '2012-08-20 15:51:22',
                'created' => '2012-08-20 15:38:00',
            ],
            [
                'id' => '11',
                'name' => 'Tour Operator',
                'parent_id' => NULL,
                'type_ids' => '1,5',
                'modified' => '2015-04-29 11:03:16',
                'created' => '2015-04-29 11:03:16',
            ],
            [
                'id' => '12',
                'name' => 'Competition Manager',
                'parent_id' => NULL,
                'type_ids' => '',
                'modified' => NULL,
                'created' => '2016-05-02 18:19:31',
            ],
        ];

        $table = $this->table('groups');
        $table->insert($data)->save();
    }
}
