<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
	public $import = ['model' => 'Users'];

	public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'username' => 'admin',
                'password' => (new \Cake\Auth\DefaultPasswordHasher())->hash('admin'),
				'login_token' => null,
                'enabled' => true,
                'email' => 'nobody@middle.nowhere',
                'add_email' => '',
                'group_id' => 1,
                'nation_id' => null,
                'tournament_id' => null,
				'language_id' => null,
				'prefix_people' => null,
                'last_login' => null,
				'count_successful' => 0,
				'count_failed' => 0,
				'count_failed_since' => 0,
				'count_requests' => 0,
                'ticket' => null,
                'ticket_expires' => null
            ],
            [
                'id' => 5,
                'username' => 'organizer',
                'password' => (new \Cake\Auth\DefaultPasswordHasher())->hash('organizer'),
				'login_token' => null,
                'enabled' => true,
                'email' => 'nobody@middle.nowhere',
                'add_email' => '',
                'group_id' => 5,
                'nation_id' => null,
                'tournament_id' => null,
				'language_id' => null,
				'prefix_people' => null,
                'last_login' => null,
				'count_successful' => 0,
				'count_failed' => 0,
				'count_failed_since' => 0,
				'count_requests' => 0,
                'ticket' => null,
                'ticket_expires' => null
            ],
            [
                'id' => 8,
                'username' => 'participant',
                'password' => (new \Cake\Auth\DefaultPasswordHasher())->hash('participant'),
				'login_token' => null,
                'enabled' => true,
                'email' => 'nobody@middle.nowhere',
                'add_email' => '',
                'group_id' => 8,
                'nation_id' => 2,
                'tournament_id' => null,
				'language_id' => null,
				'prefix_people' => null,
                'last_login' => null,
				'count_successful' => 0,
				'count_failed' => 0,
				'count_failed_since' => 0,
				'count_requests' => 0,
                'ticket' => null,
                'ticket_expires' => null
            ],
            [
                'id' => 10,
                'username' => 'guest',
                'password' => (new \Cake\Auth\DefaultPasswordHasher())->hash('guest'),
				'login_token' => null,
                'enabled' => false,
                'email' => 'nobody@middle.nowhere',
                'add_email' => '',
                'group_id' => 10,
                'nation_id' => null,
                'tournament_id' => null,
				'language_id' => null,
				'prefix_people' => null,
                'last_login' => null,
				'count_successful' => 0,
				'count_failed' => 0,
				'count_failed_since' => 0,
				'count_requests' => 0,
                'ticket' => null,
                'ticket_expires' => null
            ],
            [
                'id' => 11,
                'username' => 'touroperator',
                'password' => (new \Cake\Auth\DefaultPasswordHasher())->hash('touroperator'),
				'login_token' => null,
                'enabled' => false,
                'email' => 'nobody@middle.nowhere',
                'add_email' => '',
                'group_id' => 11,
                'nation_id' => null,
                'tournament_id' => null,
				'language_id' => null,
				'prefix_people' => null,
                'last_login' => null,
				'count_successful' => 0,
				'count_failed' => 0,
				'count_failed_since' => 0,
				'count_requests' => 0,
                'ticket' => null,
                'ticket_expires' => null
            ]
        ];
        parent::init();
    }
}
