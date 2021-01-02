<?php
/* Copyright (c) 2020 Christoph Theis */

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;

use Cake\TestSuite\IntegrationTestTrait;

use Cake\ORM\TableRegistry;


/**
 * Test of NationsController
 * @covers App\Controller\NationsController
 * @covers App\Model\Table\NationsTable
 */
class NationsControllerTest extends AppTestCase {
	use IntegrationTestTrait;
	
	public $fixtures = [
		'app.Types',
		'app.Groups',
		'app.Languages',
		'app.Organisations',
		'app.Users',
		'app.Nations',
		'app.People',
		'app.Tournaments'
	];
	
	public function setUp() : void {
		parent::setUp();

		// We will do all tests with authenticated and authorized user
		$this->setupSession();		
		
		// Enable retain flash messages
		$this->enableRetainFlashMessages();
	}
	
	
	public function tearDown() : void {
		parent::tearDown();
	}
	
	
	/**
	 * Test without user
	 */
	public function testIndexUnauthenticated() : void {
		// destroy session
		$this->session(['Auth' => []]);
		$this->get(['controller' => 'Nations', 'action' => 'index']);
		$this->assertRedirectForLogin();
	}
	
	
	/**
	 * Test of index with authorized user
	 */
	public function testIndex() : void {
		$this->get(['controller' => 'Nations', 'action' => 'index']);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	
	/**
	 * Test GET of add with authorized user
	 */
	public function testAddGet() : void {
		$this->get(['controller' => 'Nations', 'action' => 'add']);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	
	/**
	 * Test Cancel of add with authorized user
	 */
	public function testAddCancel() : void {
		$this->post(['controller' => 'Nations', 'action' => 'add'], ['cancel' => 'Cancel']);
		$this->assertRedirect(['controller' => 'Nations', 'action' => 'index']);
	}
	
	
	/**
	 * Test NOK of add with authorized user
	 */
	public function testAddNOK() : void {
		$data = [
			'name' => null,
			'description' => 'Test Description',
			'update_people' => true
		];
		
		$this->post(['controller' => 'Nations', 'action' => 'add'], $data);
		$this->assertResponseSuccess();
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertNoRedirect();
	}
	
	/**
	 * Test OK of add with authorized user
	 */
	public function testAddOK() : void {
		$data = [
			'name' => 'TEST',
			'description' => 'Test Description',
			'update_people' => true
		];
		
		$this->post(['controller' => 'Nations', 'action' => 'add'], $data);
		$this->assertResponseSuccess();
		$this->assertEquals(1, 
				TableRegistry::getTableLocator()->get('Nations')
				->find()->where(['name' => $data['name']])->count());		
		$this->assertRedirect(['action' => 'index']);
	}
	
	/**
	 * Test view with invalid id for authorized user
	 */
	public function testViewInvalid() {
		$this->get(['controller' => 'Nations', 'action' => 'view']);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['controller' => 'Nations', 'action' => 'index']);
	}
	
	
	/**
	 * Test view with valid id for authorized user
	 */
	public function testView() {
		$this->get(['controller' => 'Nations', 'action' => 'view', 1]);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	
	/**
	 * Test edit with invalid id
	 */
	public function testEditInvalid() : void {
		$this->get(['controller' => 'Nations', 'action' => 'edit']);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['action' => 'index']);
	}
	
	/**
	 * Test edit with valid id
	 */
	public function testEditValid() : void {
		$this->get(['controller' => 'Nations', 'action' => 'edit', 1]);
		$this->assertNull($this->getSession()->read('Flash.error'));
		$this->assertResponseOk();
		$this->assertBodyIsValid();
	}
	
	/**
	 * Test Cancel of edit with authorized user
	 */
	public function testEditCancel() : void {
		$this->post(['controller' => 'Nations', 'action' => 'edit'], ['cancel' => 'Cancel']);
		$this->assertRedirect(['action' => 'index']);
	}
	
	/**
	 * Test edit with invalid data
	 */
	public function testEditNOK() : void {
		$data = [
			'id' => 1,
			'name' => null,
			'description' => 'Test Description',
			'update_people' => true
		];
		
		$this->post(['controller' => 'Nations', 'action' => 'edit', 1], $data);
		$this->assertResponseSuccess();
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertNoRedirect();		
	}
	
	
	/**
	 * Test edit with valid data
	 */
	public function testEditOK() : void {
		$data = [
			'id' => 1,
			'name' => 'TEST',
			'description' => 'Test Description',
			'update_people' => true
		];
		
		$this->post(['controller' => 'Nations', 'action' => 'edit', 1], $data);
		$this->assertResponseSuccess();
		$this->assertEquals(1, 
				TableRegistry::getTableLocator()->get('Nations')
				->find()->where(['name' => $data['name']])->count());		
		$this->assertRedirect(['action' => 'index']);		
	}
	
	
	/**
	 * Test delete with invalid id
	 */
	public function testDeleteInvalid() : void {
		$this->post(['controller' => 'Nations', 'action' => 'delete'], []);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['action' => 'index']);
	}
	
	
	/**
	 * Test with valid id which should fail
	 */
	public function testDeleteNOK() : void {
		// id 1 is referenced by user 'association'
		$this->post(['controller' => 'Nations', 'action' => 'delete', 2], []);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['action' => 'index']);		
	}
	
	
	/**
	 * Test delete with valid id
	 */
	public function testDeleteOK() : void {
		// Add our own Nation
		$data = [
			'name' => 'TEST',
			'description' => 'Test Description',
			'update_people' => true
		];
		
		$this->post(['controller' => 'Nations', 'action' => 'add'], $data);
		$nation = TableRegistry::get('Nations')->find()->where(['name' => 'TEST'])->first();
		
		$this->post(['controller' => 'Nations', 'action' => 'delete', $nation->id], []);
		$this->assertNull($this->getSession()->read('Flash.error'));
		$this->assertNotNull($this->getSession()->read('Flash.success'));
		$this->assertRedirect(['action' => 'index']);
	}
}
