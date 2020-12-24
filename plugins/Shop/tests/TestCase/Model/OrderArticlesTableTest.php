<?php
/* Copyright (c) 2020 Christoph Theis */

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;

use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

use App\Model\Table\GroupsTable;

/**
 * Test of PeopleController
 * @covers App\Controller\PeopleController
 * @covers App\Model\Table\PeopleTable
 * @covers App\Model\Entity\Person
 */
class OrderArticlesTableTest extends AppTestCase {
	public $fixtures = [
		'app.Groups',
		'app.Users',
		'app.Languages',
		'app.Nations',
		'app.Tournaments',
		'app.People',
		'app.Competitions',
		'app.Registrations',
		'app.Types'
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
	
	
	// Delete a person which is still linked to an article
	public function testDeletePersonNOK() : void {
		$this->markTestIncomplete();		
	}
}
