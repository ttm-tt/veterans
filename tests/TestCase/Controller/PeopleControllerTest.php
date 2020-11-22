<?php
/* Copyright (c) 2020 Christoph Theis */

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;

use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Laminas\Diactoros\UploadedFile;

use App\Model\Table\GroupsTable;

/**
 * Test of PeopleController
 * @covers App\Controller\PeopleController
 * @covers App\Model\Table\PeopleTable
 * @covers App\Model\Entity\Person
 */
class PeopleControllerTest extends AppTestCase {
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
		
		// Player
		$this->get(['controller' => 'People', 'action' => 'index', '?' => ['is_player' => 1]]);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
		
		// Check Session
		$this->assertEquals(1, $this->getSession()->read('People.is_player'));
		
		// Not player
		$this->get(['controller' => 'People', 'action' => 'index', '?' => ['is_player' => -1]]);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
		
		// Check Session
		$this->assertEquals(-1, $this->getSession()->read('People.is_player'));
		
		// Reset filter
		$this->get(['controller' => 'People', 'action' => 'index', '?' => ['is_player' => 'all']]);
		$this->assertNull($this->getSession()->read('People.is_player'));
		$this->assertResponseOk();
		$this->assertBodyIsValid();
		
		// Umpire
		$this->get(['controller' => 'People', 'action' => 'index', '?' => ['is_umpire' => 1]]);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
		
		// Check Session
		$this->assertEquals(1, $this->getSession()->read('People.is_umpire'));
		
		// Reset filter
		$this->get(['controller' => 'People', 'action' => 'index', '?' => ['is_umpire' => 'all']]);
		$this->assertNull($this->getSession()->read('People.is_umpire'));
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
		$this->get(['controller' => 'People', 'action' => 'view', 1]);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
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
			'player' => [
				'extern_id' => -1,
				'rank_pts' => 100,
				'ranking_points' => [
					0 => [
						'age' => 15,
						'rank_pts' => 1000						
					]
				],
				'banned_until' => null,				
			],
			'passport' => 'Z123456',
			'passport_expires' => date('Y-m-d', strtotime('+5 years')),
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
			// Duplicate name
			[[
				'first_name' => 'Max',
				'last_name' => 'MUSTERMANN',
				'sex' => 'M',
				'nation_id' => 2,				
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
		);
	}
	
	
	/**
	 * Add with valid data
	 * @dataProvider providerAddOK
	 */
	public function testAddOK($patch) : void {
		$base = $this->getBaseAdd();
		$data = Hash::merge($base, $patch);
		
		$this->post(['controller' => 'People', 'action' => 'add'], $data);
		$this->assertNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['controller' => 'People', 'action' => 'index']);	
		
		$table = TableRegistry::get('People');
		$person = $table->find()->where(['first_name' => $data['first_name']]);
		$this->assertEquals(1, $person->count());
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
			'player' => [
				'extern_id' => -1,
				'rank_pts' => 100,
				'ranking_points' => [
					0 => [
						'age' => 15,
						'rank_pts' => 1000						
					]
				],
				'banned_until' => null,				
			],
			'passport' => 'Z123456',
			'passport_expires' => date('Y-m-d', strtotime('+5 years')),
			'country_id' => null
		];
	}
	
	/**
	 * Test edit with different association
	 */
	public function testEditForbidden() : void {
		// Change nation_id of 'association' user to sthg different than the person
		// We use bulk update so Aros is not triggered
		TableRegistry::get('Users')->updateAll(
				['nation_id' => 1], ['id' => 2]
		);
		
		$this->session([
			'Auth' => [
				'User' => [
					'id' => 2,
					'username' => 'association',
					'group_id' => GroupsTable::getAssociationId(),
					'nation_id' => 1,
					'enabled' => true
				]
			]
		]);		

		$data = $this->getBaseEdit();
		$this->post(['controller' => 'People', 'action' => 'edit', 3], $data);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['controller' => 'People', 'action' => 'index']);
	}
	
	
	/**
	 * Test edit with different association
	 */
	public function testEditAllowed() : void {
		$data = $this->getBaseEdit();
		$this->post(['controller' => 'People', 'action' => 'edit', 3], $data);
		$this->assertNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['controller' => 'People', 'action' => 'index']);
		
		$table = TableRegistry::get('People');
		$count = $table->find()->where(['first_name' => 'Kevin'])->count();
		$this->assertEquals(1, $count);
	}
	
	/**
	 * Test change nationality, which adds to the History
	 */
	public function testEditNationality() : void {
		$data = $this->getBaseEdit();
		$data['nation_id'] = 1;
		$this->post(['controller' => 'People', 'action' => 'edit', 3], $data);
		$this->assertNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect(['controller' => 'People', 'action' => 'index']);
		
		$table = TableRegistry::get('People');
		$person = $table->find()->where(['id' => '3'])->first();
		$this->assertEquals(1, $person->nation_id);	
		
		$table = TableRegistry::get('PersonHistories');
		$count = $table->find()->where(['person_id' => 3])->count();
		$this->assertGreaterThan(0, $count);
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
	 * Test delete if a person which is still referenced
	 */
	public function testDeleteNOK() : void {
		$this->markTestIncomplete();
	}
	
	
	/**
	 * Test delete valid id
	 */
	public function testDeleteOK() : void {
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
	}
	
	
	/**
	 * Test upload_photo
	 */
	public function testUploadPhotoOK() : void {
		$fname = TESTS . 'Fixture' . DS . 'Files' . DS . 'person.png';
		$fsize = filesize($fname);
		
		$temp = tmpfile();
		fwrite($temp, file_get_contents($fname));
		
		$data = [
			'UploadPhoto' => new UploadedFile(
					$temp, $fsize, UPLOAD_ERR_OK, 'person.png', 'image/png'
			)
		];
		
		$this->post(['controller' => 'People', 'action' => 'upload_photo', 1], $data);
		$this->assertRedirect();
		
		$table = TableRegistry::get('Photos');
		$count = $table->find()->where(['person_id' => 1])->count();
		$this->assertEquals(1, $count);

		// TODO: Verify no tmp file remains
		
		fclose($temp);
	}
	
	
	/**
	 * Test upload_photo
	 */
	public function testUploadPhotoNOK() : void {
		$fname = TESTS . 'Fixture' . DS . 'Files' . DS . 'person.png';
		$fsize = filesize($fname);
		
		$temp = tmpfile();
		fwrite($temp, file_get_contents($fname));
		
		$data = [
			'UploadPhoto' => new UploadedFile(
					$temp, $fsize, UPLOAD_ERR_NO_FILE, 'person.png', 'image/png'
			)
		];
		
		$this->post(['controller' => 'People', 'action' => 'upload_photo', 1], $data);
		$this->assertRedirect();
		$this->assertNotNull($this->getSession()->read('Flash.warning'));
		
		$table = TableRegistry::get('Photos');
		$count = $table->find()->where(['person_id' => 1])->count();
		$this->assertEquals(0, $count);

		// TODO: Verify no tmp file remains
		
		fclose($temp);
	}
	
	
	/**
	 * Test update people
	 */
	public function testUpdatePeople() : void {
		$fname = TESTS . 'Fixture' . DS . 'Files' . DS . 'update_people.csv';
		$fsize = filesize($fname);
		
		$temp = tmpfile();
		fwrite($temp, file_get_contents($fname));
		
		$data = [
			'File' => new UploadedFile(
					$temp, $fsize, UPLOAD_ERR_OK, 'update_people.csv', 'text/csv'
			)
		];
		
		$this->get(['controller' => 'People', 'action' => 'update_people']);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
		
		$this->post(['controller' => 'People', 'action' => 'update_people'], $data);
		$this->assertRedirect();
		$this->assertNull($this->getSession()->read('Flash.error'));
		
		// Kevin was added
		$table = TableRegistry::get('People');
		$count = $table->find()->where(['first_name' => 'Kevin'])->count();
		$this->assertEquals(1, $count);

		// Chantal, though inactive, was added, too
		$table = TableRegistry::get('Players');
		$count = $table->find()->where(['extern_id >=' => 200000])->count();
		$this->assertEquals(2, $count);

		// TODO: Verify no tmp file remains
		
		fclose($temp);		
	}
	
	
	/**
	 * Test update ranking points U15
	 */
	public function testUpdateRanking() : void {
		$fname = TESTS . 'Fixture' . DS . 'Files' . DS . 'update_u15.csv';
		$fsize = filesize($fname);
		
		$temp = tmpfile();
		fwrite($temp, file_get_contents($fname));
		
		$data = [
			'age' => 15,
			'File' => new UploadedFile(
					$temp, $fsize, UPLOAD_ERR_OK, 'update_u15.csv', 'text/csv'
			)
		];
		
		$this->get(['controller' => 'People', 'action' => 'update_ranking']);
		$this->assertResponseOk();
		$this->assertBodyIsValid();
		
		$this->post(['controller' => 'People', 'action' => 'update_ranking'], $data);
		$this->assertRedirect();
		$this->assertNull($this->getSession()->read('Flash.error'));

		// Check U15 ranking points of Leon
		$people = TableRegistry::get('People');
		
		$leon = $people->get(3, [
				'contain' => [
					'Players' => 'RankingPoints'
				]
			]
		);

		$this->assertNotNull($leon);
		$this->assertNotNull($leon->player);
		$this->assertNotNull($leon->player->ranking_points);
		
		$pts = null;
		
		foreach ($leon->player->ranking_points as $val) {
			if ($val->age === 15)
				$pts = $val->rank_pts;
		}
		
		$this->assertEquals(1000, $pts);
		
		fclose($temp);		
	}
	
	
	/**
	 * Test list_registrations by invalid id
	 */
	public function testListRegistrationsInvalid() : void {
		$this->get(['controller' => 'People', 'action' => 'list_registrations']);
		$this->assertNotNull($this->getSession()->read('Flash.error'));
		$this->assertRedirect();
	}
	
	
	/**
	 * Test list_registrations without permissions
	 */
	public function testListRegistrationsForbidden() : void {
		// TODO Run test with non-admin
		$this->markTestIncomplete();
	}
	
	
	/**
	 * Test list_registrations by valid id
	 */
	public function testListRegistrationsOK() : void {
		$this->get(['controller' => 'People', 'action' => 'list_registrations', 3]);
		$this->assertNull($this->getSession()->read('Flash.error'));
		$this->assertResponseOk();
		$this->assertBodyIsValid();
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
