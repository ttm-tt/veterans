<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;

use App\Routing\Route\DatasourceRoute;


/**
 * @see // https://github.com/cakephp/cakephp/blob/master/tests/TestCase/Routing/Route/InflectedRouteTest.php
 * Test of DatasourceRouter
 * @covers App\Routing\Route\DatasourceRoute
 */
class DatasourceRouteTest extends AppTestCase {
	public function setUp() : void {
		parent::setUp();
	}
	
	public function tearDown() : void {
		parent::tearDown();
	}
	
	public function testConstructor() : void {
		$route = new DatasourceRoute('{controller}');
		$this->assertNotNull($route);
	}
	
	public function testMatch() {
		$route = new DatasourceRoute('/{controller}/{action}');

		$result = $route->match(['ds' => null, 'controller' => 'Users', 'action' => 'index']);
		$this->assertSame('/users/index', $result);

		$result = $route->match(['ds' => 'test', 'controller' => 'Users', 'action' => 'index']);
		$this->assertSame('/test/users/index', $result);
	}
	
	public function testParse() {
		$route = new DatasourceRoute('/{controller}/{action}');

		$result = $route->parse('/users/index', 'GET');
		$this->assertEmpty($result['ds']);
		$this->assertSame('Users', $result['controller']);

		$result = $route->parse('/test/users/index', 'GET');
		$this->assertSame('test', $result['ds']);
		$this->assertSame('Users', $result['controller']);				
	}
}
