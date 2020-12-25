<?php
/* Copyright (c) 2020 Christoph Theis */

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;


/**
 * Test of OrderArticlesTable
 * @covers Shop\Model\Table\OrderArticlesTable
 */
class OrderArticlesTableTest extends AppTestCase {
	public $fixtures = [
		'app.Groups',
		'app.Languages',
		'app.Nations',
		'app.Organisations',
		'app.Tournaments',
		'app.Users',
		'app.People',
		'app.Competitions',
		'app.Types',
		'app.Registrations'
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
