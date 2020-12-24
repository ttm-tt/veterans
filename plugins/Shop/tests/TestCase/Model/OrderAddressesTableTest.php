<?php
/* Copyright (c) 2020 Christoph Theis */

declare(strict_types=1);

namespace Shop\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;

use Cake\ORM\TableRegistry;

/**
 * Test of PeopleController
 * @covers Shop\Model\Table\OrderAddresses
 */
class OrderAddressesTableTest extends AppTestCase {
	public $fixtures = [
		'app.Groups',
		'app.Nations',
		'app.Languages',
		'app.Organisations',
		'app.Tournaments',
		'app.Users',
		'plugin.Shop.Countries',
		'plugin.Shop.OrderStatus',
		'plugin.Shop.Orders',
		'plugin.Shop.OrderAddresses'
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
	 * Test validation
	 */
	public function testValidation() : void {
		$dataNOK = [
			[
				'first_name' => null,
				'last_name' => null,
				'city' => null,
				'country_id' => null
			],
			[
				'first_name' => 'Kevin',
				'last_name' => 'Mustermann',
				'city' => 'Musterstadt',
				'country_id' => ''
			]
		];
		
		$dataOK = [
			[
				'first_name' => 'Kevin',
				'last_name' => 'Mustermann',
				'city' => 'Musterstadt',
				'country_id' => 1
			]			
		];
		
		$table = TableRegistry::get('Shop.OrderAddresses');
		
		foreach ($dataNOK as $data) {
			$address = $table->newEntity($data);
			$this->assertTrue($address->hasErrors());
		}
		
		foreach ($dataOK as $data) {
			$address = $table->newEntity($data);
			$this->assertFalse($address->hasErrors());
		}			
	}
}
