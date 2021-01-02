<?php
/* Copyright (c) 2020 Christoph Theis */

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;

use Cake\ORM\TableRegistry;


/**
 * Test of CompetitionsController
 * @covers App\Controller\CompetitionsController
 * @covers App\Model\Table\CompetitionsTable
 */
class CompetitionsControllerTest extends AppTestCase {
	public $fixtures = [
		'app.Groups',
		'app.Languages',
		'app.Organisations',
		'app.Nations',
		'app.Tournaments',
		'app.Users',
		'app.Competitions',
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
	 * Test of index with authorized user
	 */
	public function testIndex() : void {
		$this->get(['controller' => 'Competitions', 'action' => 'index']);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	
	/**
	 * Test GET of add with authorized user
	 */
	public function testAddGet() : void {
		$this->get(['controller' => 'Competitions', 'action' => 'add']);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	
	/**
	 * Test Cancel of add with authorized user
	 */
	public function testAddCancel() : void {
		$this->post(['controller' => 'Competitions', 'action' => 'add'], ['cancel' => 'Cancel']);
		$this->assertRedirect(['controller' => 'Competitions', 'action' => 'index']);
	}
	
	
	/**
	 * Data provider for various tests
	 */
	public function provider() : array {
		return [
			[[
				// Empty
			]],
			[[
				// Null
				'name' => null,
				'description' => null,
				'type_of' => null,
				'sex' => null,
				'tournament_id' => null
			]],
			[[
				// Illegal
				'name' => 'MS40',
				'description' => 'Men\'s Singles over 40',
				'type_of' => 'R',
				'sex' => 'S',
				'tournament_id' => 2
			]],
			[[
				// Illegal type_of / sex
				'name' => 'MS40',
				'description' => 'Men\'s Singles over 40',
				'type_of' => 'S',
				'sex' => 'X',
				'tournament_id' => 1
			]]
		];
	}
	
	
	/**
	 * Test NOK of add with authorized user
	 * @dataProvider provider
	 */
	public function testAddNOK($data) : void {
		$this->post(['controller' => 'Competitions', 'action' => 'add'], $data);
		$this->assertResponseSuccess();
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertNoRedirect();
	}
	
	/**
	 * Test OK of add with authorized user
	 */
	public function testAddOK() : void {
		$data = [
			'name' => 'XD',
			'description' => 'Mixed Doubles',
			'type_of' => 'X',
			'sex' => 'X',
			'tournament_id' => 1,
			'entries' => null,
			'entries_host' => null
		];
		
		$this->post(['controller' => 'Competitions', 'action' => 'add'], $data);
		$this->assertResponseSuccess();
		$this->assertEquals(1, 
				TableRegistry::getTableLocator()->get('Competitions')
				->find()->where(['name' => $data['name']])->count());		
		$this->assertRedirect(['action' => 'index']);
	}

	
	/**
	 * Test view with invalid id for authorized user
	 */
	public function testViewInvalid() {
		$this->get(['controller' => 'Competitions', 'action' => 'view']);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['controller' => 'Competitions', 'action' => 'index']);
	}
	
	
	/**
	 * Test view with valid id for authorized user
	 */
	public function testView() {
		$this->get(['controller' => 'Competitions', 'action' => 'view', 1]);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	
	/**
	 * Test edit with invalid id
	 */
	public function testEditInvalid() : void {
		$this->get(['controller' => 'Competitions', 'action' => 'edit']);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['action' => 'index']);
	}
	
	/**
	 * Test edit with valid id
	 */
	public function testEditValid() : void {
		$this->get(['controller' => 'Competitions', 'action' => 'edit', 1]);
		$this->assertNull($this->getSession()->read('Flash.error'));
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	/**
	 * Test Cancel of edit with authorized user
	 */
	public function testEditCancel() : void {
		$this->post(['controller' => 'Competitions', 'action' => 'edit'], ['cancel' => 'Cancel']);
		$this->assertRedirect(['action' => 'index']);
	}
	
	/**
	 * Test edit with invalid data
	 * @dataProvider provider
	 */
	public function testEditNOK($data) : void {
		$data['id'] = 1;
		
		$this->post(['controller' => 'Competitions', 'action' => 'edit', 1], $data);
		$this->assertResponseSuccess();
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertNoRedirect();		
	}
	
	
	/**
	 * Test delete with invalid id
	 */
	public function testDeleteInvalid() : void {
		$this->post(['controller' => 'Competitions', 'action' => 'delete'], []);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['action' => 'index']);
	}
	
	
	/**
	 * Test with valid id which should fail
	 */
	public function testDeleteNOK() : void {
		$this->markTestIncomplete();
	}
	
	
	/**
	 * Test delete with valid id
	 */
	public function testDeleteOK() : void {
		$this->post(['controller' => 'Competitions', 'action' => 'delete', 1], []);
		$this->assertNull($this->getSession()->read('Flash.error'));
		$this->assertNotNull($this->getSession()->read('Flash.success'));
		$this->assertRedirect(['action' => 'index']);
	}	
}
