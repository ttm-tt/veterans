<?php
/* Copyright (c) 2020 Christoph Theis */

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;
use App\Model\Table\GroupsTable;
use App\Model\Table\UsersTable;

use Cake\ORM\TableRegistry;


/**
 * Test of UsersTable
 * @covers App\Model\Table\UsersTable
 */
class UsersTableTest extends AppTestCase {
	public function setUp() : void {
		parent::setUp();
	}
	
	
	public function tearDown() : void {
		parent::tearDown();
	}
	
	
	public function testHasRootPrivileges() {
		$this->assertFalse(UsersTable::hasRootPrivileges(null));
		
		$user = TableRegistry::get('users')->newEmptyEntity();
		$this->assertFalse(UsersTable::hasRootPrivileges($user));
		$user->group_id = GroupsTable::getCompetitionManagerId();
		$this->assertFalse(UsersTable::hasRootPrivileges($user));
		$user->group_id = GroupsTable::getAdminId();
		$this->assertTrue(UsersTable::hasRootPrivileges($user));
	}	
}
