<?php
/* Copyright (c) 2020 Christoph Theis */

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;


/**
 * Test of PagesController
 * @covers App\Controller\PagesController
 * @covers App\Controller\AppController
 * @covers App\Application
 */
class PagesControllerTest extends AppTestCase {
	public $fixtures = [
		'app.Groups',
		'app.Languages',
		'app.Organisations',
		'app.Nations',
		'app.Tournaments',
		'app.Users',
		'app.People',
		'app.Types',
		'app.Competitions',
		'app.Registrations',
		'app.Participants'
	];
	
	public function setUp() : void {
		parent::setUp();
	}
	
	public function tearDown() : void {
		parent::tearDown();
	}
	
	// Test redirect of '/'
	public function testRoot() : void {
		$this->get('/');
		$this->assertRedirectNotContains('/test/');
	}
	
	// Test redirect of '/test'
	public function testTestRoot() : void {
		$this->get('/test/');
		$this->assertRedirectContains('/test/');
	}
	
	// Test /participants
	public function testParticipants() : void {
		$this->get('/participants');
		$this->assertResponseOk();
	}
	
	// Test /onParticipantData
	public function testOnParticipantData() : void {
        $this->configRequest([
            'headers' => ['Accept' => 'application/json']
        ]);
		$this->post(['controller' => 'Pages', 'action' => 'onParticipantData'], []);
		$this->assertResponseOk();
	}
}
