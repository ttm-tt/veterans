<?php
/* Copyright (c) 2020 Christoph Theis */

namespace App\Test\TestCase;

use Cake\TestSuite\TestCase;
use Cake\Core\Configure;
use Cake\Utility\Hash;

use Facebook\WebDriver\Exception\InvalidSessionIdException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;

use Symfony\Component\Process\Process;



class ChromeTestCase extends TestCase {
	// Start and stop chromedriver
	protected static $process = null;
	
	public static function setUpBeforeClass() : void {
		self::$process = new Process([ROOT . DS . 'bin/chromedriver']);
		self::$process->start();
    }
	
	public static function tearDownAfterClass() : void {
		self::$process->stop();
	}
	
	
	protected $webDriver;
	
	public function setUp() : void {
		parent::setUp();

		// Start headless
		$options = new ChromeOptions();
		$options->addArguments([
			'--headless',
			'--window-size=1280x800'.
			'--disable-gpu',
			'--no-sandbox'
		]);
		
		$host = 'http://localhost:9515';
		$desiredCapabilities = DesiredCapabilities::chrome();
		$desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $options);
		// We are using self-signed certificates which would cause an exception
		$desiredCapabilities->setCapability('acceptInsecureCerts', true);
		// Enable accessing the console
		$desiredCapabilities->setCapability('loggingPrefs', ['browser' => 'ALL']);
		
		$this->webDriver = RemoteWebDriver::create($host, $desiredCapabilities);
	}

	public function tearDown() : void {
		if ($this->hasFailed()) {
			$this->webDriver->takeScreenshot(TMP . date('Ymd\THis') . '-' . $this->getName() . '.png');
		}
		$this->webDriver->close();
		
		parent::tearDown();
	}
	
	/** Perform a GET call
	 * Performs a get with the given url. 
	 * @param string $url
	 * @return void
	 */
	
	protected function get(string $url) : void {
		// We assume that the server url is the diectory name of the project
		$base = basename(dirname(dirname(__DIR__)));
		
		$this->webDriver->get(Configure::read('App.fullBaseUrl') . '/test' . $url);
	}
	
	
	// Access to elements, by default we use css
	protected function findElement(string $css) : ?RemoteWebElement {
		// If not found an exception is thrown, but we want to return null
		try {
			return $this->webDriver->findElement(WebDriverBy::cssSelector($css));
		} catch (NoSuchElementException | InvalidSessionIdException $_ex) {
			return null;
		}
	}
	
	protected function findElements(string $css) : array {
		try {
			return $this->webDriver->findElements(WebDriverBy::cssSelector($css));		
		} catch (NoSuchElementException | InvalidSessionIdException $_ex) {
			return [];
		}
	}
	
	
	protected function getLog() : array {
		return $this->webDriver->manage()->getLog('browser');
	}

	// Do a login so we have an authenticated user
	protected function login(string $user = null, string $password = null) : void {
		// We are already logged in
		if ($this->findElement('a#logout') !== null)
			return;
		
		$this->get('/users/login');
		$this->findElement('input#username')->sendKeys($user ?: getenv('testuser', true));
		$this->findElement('input#password')->sendKeys($password ?: getenv('testuser', true));
		$this->findElement('div.user.form form')->submit();		
	}
	
	// logout again, assuming we are logged in
	protected function logout() : void {
		$link = $this->findElement('a#logout');
		if ($link !== null)
			$link->click();
	}
	
	// Verify the log is empty
	protected function assertLogIsEmpty() : void {
		$logs = $this->getLog();
		$this->assertCount(0, $logs, implode("\n", Hash::extract($logs, '{n}.message')));
	}
}
