<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TournamentsFixture
 */
class TournamentsFixture extends TestFixture
{
	public $import = ['model' => 'Tournaments'];

	public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'TEST',
                'description' => 'Test Tournament',
                'start_on' => strtotime('+3 weeks'),
                'end_on' => strtotime('+4 weeks'),
                'enter_after' => strtotime('-1 week'),
                'enter_before' => strtotime('+1 week'),
                'enter_others_before' => strtotime('+2 weeks'),
                'enter_accommodations_before' => strtotime('+2 weeks'),
                'enter_travels_before' => strtotime('+2 weeks'),
                'modify_before' => strtotime('+2 weeks'),
                'nation_id' => 2,
                'location' => 'Berlin',
                'organizers_url' => '',
                'wr_relevant' => true,
                'invitation' => false,
                'competition_manager_id' => null,
                'id_required_reason' => ''
            ],
            [
                'id' => 2,
                'name' => 'PAST',
                'description' => 'Past Tournament',
                'start_on' => strtotime('-4 weeks'),
                'end_on' => strtotime('-3 weeks'),
                'enter_after' => strtotime('-8 week'),
                'enter_before' => strtotime('-6 week'),
                'enter_others_before' => strtotime('-5 weeks'),
                'enter_accommodations_before' => strtotime('-5 weeks'),
                'enter_travels_before' => strtotime('-5 weeks'),
                'modify_before' => strtotime('-5 weeks'),
                'nation_id' => 2,
                'location' => 'Berlin',
                'organizers_url' => '',
                'wr_relevant' => true,
                'invitation' => false,
                'competition_manager_id' => null,
                'id_required_reason' => ''
            ],
        ];
        parent::init();
    }
}
