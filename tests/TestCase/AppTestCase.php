<?php
/* Copyright (c) 2020 Christoph Theis */

namespace App\Test\TestCase;

use Cake\TestSuite\TestCase;
use Cake\TestSuite\IntegrationTestTrait;

use App\Model\Table\GroupsTable;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utility\Hash;


class AppTestCase extends TestCase {
	use IntegrationTestTrait;
	
	public $fixtures = [
		'app.Groups'
	];
	
	
	public function setUp() : void {
		parent::setUp();
		
        Configure::write('Acl.classname', 'App\Test\Mock\TestAcl');
		
		// Force https
		$this->configRequest([
			'environment' => ['HTTPS' => 'on']
		]);
		
		// Enable debug, so we can check tokens
		Configure::write('debug', true);
		
		// To test for PHP errors we need the phpunit error handler
		// But that won't install by default because the one from CakePHP
		// is already installed. So we do it manually. 
		// And forget about the one from CakePHP.
		$result = $this->getTestResultObject();
        if ($result->getConvertDeprecationsToExceptions() || 
			$result->getConvertErrorsToExceptions() || 
			$result->getConvertNoticesToExceptions() || 
			$result->getConvertWarningsToExceptions()	
		) {
            $errorHandler = new \PHPUnit\Util\ErrorHandler(
                $result->getConvertDeprecationsToExceptions(),
                $result->getConvertErrorsToExceptions(),
                $result->getConvertNoticesToExceptions(),
                $result->getConvertWarningsToExceptions()
            );

			set_error_handler($errorHandler);
		}
				
		// Automatic generation of security tokens
		$this->enableSecurityToken();
		// $this->enableCsrfToken();		
	}
	
	public function tearDown() : void {
		parent::tearDown();
	}
	
	
	// Setup session
	protected function setupSession() {
		$this->session([
			'Auth' => [
				'User' => [
					'id' => 1,
					'username' => 'admin',
					'group_id' => GroupsTable::getAdminId(),
					// 'enabled' => true
				]
			]
		]);		
	}
	
	
	// Merge session data
	protected function mergeSession($data) {
		$this->_session = Hash::merge($this->_session, $data);
	}
	

	// Test that the response to the action is a redirect to login
	// The response contains a query string with the referer, which we dont't want to test
	protected function assertRedirectForLogin() {
		$this->assertResponseCode(302);
		$this->assertRedirectContains(Router::url(['controller' => 'users', 'action' => 'login']));
	}
	
	
	// Assert that the body is valid HTML
	protected function assertBodyIsValid() : void {
		$body = $this->_getBodyAsString();
		$tidy = new \tidy();
		$tidy->parseString($body, ['drop-empty-elements' => false]);
		if ($tidy->getStatus() !== 0)
			file_put_contents (TMP . date('Ymd\THis') . '-' . $this->getName() . '.html', $body);
		$this->assertEquals(0, $tidy->getStatus(), $tidy->errorBuffer ?? '');
	}	
}
