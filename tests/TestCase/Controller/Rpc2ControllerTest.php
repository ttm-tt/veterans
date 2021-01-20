<?php
/* Copyright (c) 2020 Christoph Theis */

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;



// XXX: Why do I have to do this manually?
require_once ROOT . '/vendor/greenfieldtech-nirs/ixr-xmlrpc/ixr_xmlrpc.php';


/**
 * Test of Rpc2Controller
 * @covers App\Controller\Rpc2Controller
 */
class Rpc2ControllerTest extends AppTestCase {
	public $fixtures = [
		'app.Groups',
		'app.Languages',
		'app.Users',
		'app.Nations',
		'app.Tournaments',
		'app.Competitions'
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
	
	
	public function testListTournaments() : void {
		$request = new \IXR_Request('onlineentries.listTournaments', []);
		$xml = $request->getXml();
		$this->post(['controller' => 'Rpc2', 'action' => 'index'], $xml);
		$this->assertResponseOk();
		$response = new \IXR_Message($this->_getBodyAsString());
		$this->assertTrue($response->parse());
		$this->assertEquals('methodResponse', $response->messageType);
		$this->assertIsArray($response->params);
		$this->assertIsArray($response->params[0]);
		$this->assertArrayHasKey('name', $response->params[0][0]); // Tournament name
		$this->assertEquals('TEST', $response->params[0][0]['name']);
	}
}