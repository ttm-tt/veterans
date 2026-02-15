<?php
/* Copyright (c) 2020 Christoph Theis */

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;

use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * Test of PeopleController
 * @covers App\Controller\PeopleController
 * @covers App\Model\Table\PeopleTable
 * @covers App\Model\Entity\Person
 */
class PeopleControllerTest extends AppTestCase {
	public $fixtures = [
		'app.Groups',
		'app.Languages',
		'app.Organisations',
		'app.Nations',
		'app.Tournaments',
		'app.Users',
		'app.People',
		'app.PersonHistories',
		'app.Types',
		'app.Competitions',
		'app.Registrations',
		'app.Participants'
	];
	
	public function setUp() : void {
		parent::setUp();
		
		$this->setupSession();
			
		// Enable retain flash messages
		$this->enableRetainFlashMessages();
}
	
	public function tearDown() : void {
		parent::tearDown();
	}
	
	
	/**
	 * Test people/index
	 */
	public function testIndex() : void {
		$this->get(['controller' => 'People', 'action' => 'index']);
		$this->assertResponseOk();
		$this->assertBodyIsValid();		
		
		// Check filters: Associaton, Type
		$this->get(['controller' => 'People', 'action' => 'index', '?' => ['nation_id' => 1]]);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
		
		// Check Session
		$this->assertEquals(1, $this->getSession()->read('Nations.id'));
		
		// Reset filter
		$this->get(['controller' => 'People', 'action' => 'index', '?' => ['nation_id' => 'all']]);
		$this->assertNull($this->getSession()->read('Nations.id'));
		$this->assertResponseOk();
		$this->assertBodyIsValid();
		
		// Last name
		$this->get(['controller' => 'People', 'action' => 'index', '?' => ['last_name' => 'Mu']]);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
		
		// Check Session
		$this->assertEquals('Mu', $this->getSession()->read('People.last_name'));
		
		// Reset filter
		$this->get(['controller' => 'People', 'action' => 'index', '?' => ['last_name' => '*']]);
		$this->assertNull($this->getSession()->read('People.last_name'));
		$this->assertResponseOk();
		$this->assertBodyIsValid();
		
		// Sex
		$this->get(['controller' => 'People', 'action' => 'index', '?' => ['sex' => 'F']]);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
		
		// Check Session
		$this->assertEquals('F', $this->getSession()->read('People.sex'));
		
		// Reset filter
		$this->get(['controller' => 'People', 'action' => 'index', '?' => ['sex' => 'all']]);
		$this->assertNull($this->getSession()->read('People.sex'));
		$this->assertResponseOk();
		$this->assertBodyIsValid();		
	}
	
	
	/**
	 * View with invalid id
	 */
	public function testViewInvalid() : void {
		$this->get(['controller' => 'People', 'action' => 'view']);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['controller' => 'People', 'action' => 'index']);
	}
	
	
	/**
	 * Test view with valid id
	 */
	public function testView() {
		$this->markTestIncomplete('Requires Shop.Orders, ...');
/*		
		$this->get(['controller' => 'People', 'action' => 'view', 1]);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
 */
	}
	
	
	/**
	 * Get add template
	 */
	public function testAddGet() : void {
		$this->get(['controller' => 'Users', 'action' => 'add']);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	
	/**
	 * Cancel add 
	 */
	public function testAddCancel() : void {
		$this->post(['controller' => 'Users', 'action' => 'add'], ['cancel' => 'Cancel']);
		$this->assertRedirect(['action' => 'index']);
	}
	
	
	/**
	 * Base for add data
	 */
	protected function getBaseAdd() : array {
		return [
			'first_name' => null,
			'last_name' => null,
			'display_name' => null,
			'sex' => null,
			'nation_id' => null,
			'is_umpire' => true,
			'is_player' => true,
			'dob' => date('Y-m-d', strtotime('-15 years')),
			'extern_id' => -1,
			'country_id' => null
		];
	}
	
	/*
	 * Patches for NOK data
	 */
	public function providerAddNOK() : array {
		return array(
			// Empty values
			[[				
			]],			
		);
	}
	
	
	/**
	 * Add with invalid / incomplete data
	 * @dataProvider providerAddNOK
	 */
	public function testAddNOK($patch) : void {
		$base = $this->getBaseAdd();
		$data = Hash::merge($base, $patch);
		
		$this->post(['controller' => 'People', 'action' => 'add'], $data);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertResponseOk();
	}
	
	
	/*
	 * Patches for OK data
	 */
	public function providerAddOK() : array {
		return array(
			[[
				'first_name' => 'Kevin',
				'last_name' => 'MUSTERMANN',
				'sex' => 'M',
				'nation_id' => 2,				
			]],
			// Duplicate name is OK, display_name is calculated in this case
			[[
				'first_name' => 'Max',
				'last_name' => 'MUSTERMANN',
				'sex' => 'M',
				'nation_id' => 2,				
			]],
		);
	}
	
	
	/**
	 * Add with valid data
	 * @dataProvider providerAddOK
	 */
	public function testAddOK($patch) : void {
		$base = $this->getBaseAdd();
		$data = Hash::merge($base, $patch);
		
		$table = TableRegistry::get('People');
		$oldCount = $table->find()->where(['first_name' => $data['first_name']])->count();

		$this->post(['controller' => 'People', 'action' => 'add'], $data);
		$this->assertNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['controller' => 'People', 'action' => 'index']);	
		
		$person = $table->find()->where(['first_name' => $data['first_name']]);
		$this->assertEquals($oldCount + 1, $person->count());
	}
	
	
	/**
	 * Test get edit with invalid id
	 */
	public function testEditInvalid() : void {
		$this->get(['controller' => 'People', 'action' => 'edit']);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['controller' => 'People', 'action' => 'index']);
	}
	
	
	/**
	 * Test get edit with valid id
	 */
	public function testEditValid() : void {
		$this->get(['controller' => 'People', 'action' => 'edit', 1]);
		$this->assertNull($this->getSession()->read('Flash.error'));
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	
	/**
	 * Test cancel edit
	 */
	public function testEditCancel() : void {
		$this->post(['controller' => 'People', 'action' => 'edit'], ['cancel' => 'Cancel']);
		$this->assertRedirect(['controller' => 'People', 'action' => 'index']);
	}
	
	
	/**
	 * Base for add data
	 */
	protected function getBaseEdit() : array {
		return [
			'id' => 3,
			'first_name' => 'Kevin',
			'last_name' => 'MUSTERMANN',
			'display_name' => null,
			'sex' => 'M',
			'nation_id' => 2,
			'is_umpire' => false,
			'is_player' => true,
			'dob' => date('Y-m-d', strtotime('-15 years')),
			'extern_id' => -1,
			'country_id' => null
		];
	}
	
	/**
	 * Test delete with invalid method or id
	 */
	public function testDeleteInvalid() : void {
		// Get is not allowed
		$this->get(['controller' => 'People', 'action' => 'delete', 2]);
		$this->assertResponseCode(405);
		
		// No id
		$this->post(['controller' => 'People', 'action' => 'delete']);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['controller' => 'People', 'action' => 'index']);
	}
	
	
	/**
	 * Test delete when some checks rail
	 */
	public function testDeleteNOK() : void {
		$this->markTestIncomplete();
	}
	
	
	/**
	 * Test delete with event triggered by delete rule 
	 */
	public function testDeleteDeleteRule() : void {
		// Add a person to the list, so we can be sure we can delete him
		$patches = $this->providerAddOK();
		$base = $this->getBaseAdd();
		
		$data = Hash::merge($base, $patches[0][0]);		
		$this->post(['controller' => 'People', 'action' => 'add'], $data);
		
		// Find id of new person
		$people = TableRegistry::get('People');
		$person = $people->find()->where(['first_name' => $data['first_name']])->first();
		$this->assertNotNull($person);
		$id = $person->id;
		
		$ret = false;
		
		// Add an event for Person.deleteRule, which returns false
		$people
				->getEventManager()
				->on('Person.deleteRule', function(\Cake\Event\EventInterface $event) use(&$ret) {
						if (!$ret) {
							$event->stopPropagation();
							$event->getSubject()->setError('person_id', __('Person cannot be deleted'));
							
							// No result, it is in the entity
							return null;
						}
					}
				);
				
		// Should fail because event returns fail
		$this->assertFalse($people->delete($people->get($id)));
		
		// Now the same but should succeed
		$ret = true;		
		$this->assertTrue($people->delete($people->get($id)));		
	}
	
	
	/**
	 * Test delete valid id
	 */
	public function testDeleteOK() : void {
		$this->markTestIncomplete('Requires Shop.Orders, ...');		
/*		
		// Add a person to the list, so we can be sure we can delete him
		$patches = $this->providerAddOK();
		$base = $this->getBaseAdd();
		
		$data = Hash::merge($base, $patches[0][0]);		
		$this->post(['controller' => 'People', 'action' => 'add'], $data);
		
		// Find id of new person
		$table = TableRegistry::get('People');
		$person = $table->find()->where(['first_name' => $data['first_name']])->first();
		$id = $person->id;
		
		$this->post(['controller' => 'People', 'action' => 'delete', $id]);
		$this->assertNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['controller' => 'People', 'action' => 'index']);	
		
		// And person doesn't exist anymore
		$this->assertNull($table->find()->where(['id' => $id])->first());
 */
	}
	
	
	/**
	 * Test delete if the person is still referenced by Registrations
	 */
	public function testDeleteOKWithRegistration() : void {
		$this->markTestIncomplete('Requires Shop.Orders, ...');		
/*		
		// We need a tournament here
		$this->mergeSession(['Tournaments' => ['id' => 1]]);
		
		// Add a person to the list which is referenced
		$patches = $this->providerAddOK();
		$base = $this->getBaseAdd();
		
		$data = Hash::merge($base, $patches[0][0]);		
		$this->post(['controller' => 'People', 'action' => 'add'], $data);
		
		// Find id of new person
		$people = TableRegistry::get('People');

		$person = $people->find()->where(['first_name' => $data['first_name']])->first();
		$id = $person->id;
		
		// Add a registration
		$registration = [
			'person_id' => $id,
			'type_id' => 5, // ACC
			'tournament_id' => 1
		];
		
		$registrations = TableRegistry::get('Registrations');
		$this->assertNotFalse($registrations->save($registrations->newEntity($registration)));
		
		// And now delete it
		$this->post(['controller' => 'People', 'action' => 'delete', $id]);
		$this->assertRedirect(['controller' => 'People', 'action' => 'index']);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertNull($people->record($id));
 */
	}
	
	
	// Stubs
	public function testHistory() : void {
		$this->markTestIncomplete();
	}
	
	
	public function testRevision() : void {
		$this->markTestIncomplete();
	}
	
	
	public function testPhoto() : void {
		$this->markTestIncomplete();
	}
	
	
	public function testRemovePhoto() : void {
		$this->markTestIncomplete();
	}
}
