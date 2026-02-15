<?php
/* Copyright (c) 2020 Christoph Theis */

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\GroupsTable;
use App\Test\TestCase\AppTestCase;
use Cake\ORM\TableRegistry;

/**
 * Test of RegistrationsController
 * @covers App\Controller\RegistrationsController
 * @covers App\Model\Table\RegistrationsTable
 */
class RegistrationsControllerTest extends AppTestCase {
	public $fixtures = [
		'app.Groups', 
		'app.Types',
		'app.Nations',
		'app.Languages',
		'app.Users',
		'app.People',
		'app.PersonHistories',
		'app.Organisations',
		'app.Tournaments',
		'app.Competitions',
		'app.Registrations',
		'app.Participants',
		'app.ParticipantHistories',
		'plugin.Shop.Articles',
		'plugin.Shop.OrderStatus',
		'plugin.Shop.OrderSettings',
		'plugin.Shop.Orders',
		'plugin.Shop.OrderHistories',
		'plugin.Shop.OrderArticles',
		'plugin.Shop.OrderArticleHistories'
	];

	
	public function setUp() : void {
		parent::setUp();

		// We will do all tests with authenticated and authorized user
		$this->setupSession();	
		
		// And select a tournament
		$this->mergeSession(['Tournaments' => ['id' => 1]]);
		
		// Enable retain flash messages
		$this->enableRetainFlashMessages();
	}
	
	
	public function tearDown() : void {
		parent::tearDown();
	}
	
	
	/**
	 * Get index template
	 */
	public function testIndex() : void {
		$this->get(['controller' => 'Registrations', 'action' => 'index', 1]);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	
	/**
	 * Get add template
	 */
	public function testAddGet() : void {
		$this->get(['controller' => 'Registrations', 'action' => 'add']);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	
	/**
	 * Cancel add 
	 */
	public function testAddCancel() : void {
		$this->post(['controller' => 'Registrations', 'action' => 'add'], ['cancel' => 'Cancel']);
		$this->assertRedirect(['action' => 'index']);
	}
	
	
	/**
	 * Test add valid
	 */
	public function testAddValid() : void {
		$data = [
			'tournament_id' => 1,
			'person_id' => 3,
			'type_id' => 1,
			'participant' => ['single_id' => 1]
		];
		
		$this->post(['controller' => 'Registrations', 'action' => 'add'], $data);
		$this->assertRedirect();
		$this->assertNotNull($this->getSession()->read('Flash.success'));		
		
		// Read back what is in DB
		$table = TableRegistry::get('Registrations');
		$registration = $table->find()->where(['person_id' => 3])->first();
		$this->assertNotNull($registration);
	}
	

	/**
	 * Get add template
	 */
	public function testAddParticipantGet() : void {
		$this->get(['controller' => 'Registrations', 'action' => 'add_participant']);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	
	/**
	 * Cancel add 
	 */
	public function testAddParticipantCancel() : void {
		$this->post(['controller' => 'Registrations', 'action' => 'add_participant'], ['cancel' => 'Cancel']);
		$this->assertRedirect(['action' => 'index']);
	}
	
	
	/**
	 * Test add valid
	 */
	public function testAddParticipantValid() : void {
		$data = [
			'tournament_id' => 1,
			'person' => [
				'first_name' => 'Player',
				'last_name' => 'Aaa',
				'sex' => 'M',
				'nation_id' => 2, // GER
				'dob' => date('Y-m-d', strtotime('-41 years')),
				'username' => 'touroperator'
			],
			'type_id' => 1,
			'participant' => ['single_id' => 1]
		];
		
		$this->post(['controller' => 'Registrations', 'action' => 'add_participant'], $data);
		$this->assertRedirect();
		$this->assertNotNull($this->getSession()->read('Flash.success'));		
		
		// Read back what is in DB
		$table = TableRegistry::get('People');
		$person = $table->find()->where(['first_name' => 'Player', 'last_name' => 'Aaa'])->first();
		$this->assertNotNull($person);
		$table = TableRegistry::get('Registrations');
		$registration = $table->find()->where(['person_id' => $person->id])->first();
		$this->assertNotNull($registration);
	}
	

	/**
	 * Test onChangePerson
	 */
	public function testOnChangePerson() : void {
		// Change user id and dob of person
		$this->post([
			'controller' => 'People', 'action' => 'edit', 1
		], [
			'id' => 1, 
			'user_id' => 10,
			'dob' => strtotime('41 years')
		]);
		
		// Request as organizer
		$this->mergeSession([
			'Auth' => [
				'User' => [
					'id' => 10,
					'username' => 'touroperator',
					'group_id' => GroupsTable::getTourOperatorId(),
					// 'enabled' => true
				]
			]
		]);		

		$this->configRequest([
            'headers' => [
				'X-Requested-With' => 'XMLHttpRequest',
				'Accept' => 'application/json'
			]
        ]);
		$data = [
			'tournament_id' => 1,
			'person_id' => 1
		];
		$this->post(['controller' => 'registrations', 'action' => 'onChangePerson'], $data);
		$this->assertResponseSuccess();
		
		$result = json_decode($this->_getBodyAsString(), true);
		$this->assertEquals(JSON_ERROR_NONE, json_last_error());
		$this->assertArrayHasKey('Competitions', $result);		
	}
	
	
	/**
	 * Get edit template
	 */
	public function testEditGet() : void {
		$rid = $this->_addRegistration();
		
		$this->get(['controller' => 'Registrations', 'action' => 'edit', $rid]);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	
	/**
	 * Get edit template
	 */
	public function testEditParticipantGet() : void {
		$rid = $this->_addRegistration();
		
		$this->get(['controller' => 'Registrations', 'action' => 'edit_participant', $rid]);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	
	// Helper
	private function _addRegistration() : int {
		// Change user id and dob of person
		$this->post([
			'controller' => 'People', 
			'action' => 'edit', 
			1
		], [
			'id' => 1, 
			'user_id' => 10,
			'dob' => strtotime('41 years')
		]);
		
		$data = [
			'tournament_id' => 1,
			'person_id' => 1,
			'type_id' => 1,
			'participant' => ['single_id' => 1]
		];
		
		$this->post(['controller' => 'Registrations', 'action' => 'add'], $data);
		
		// Read back what is in DB
		$table = TableRegistry::get('Registrations');
		$registration = $table->find()->where(['person_id' => 1])->first();

	    return $registration->id;
	}
}
