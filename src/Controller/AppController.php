<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Controller;

use Cake\Utility\Inflector;
use Cake\Event\EventInterface;
use Cake\I18n\I18n;
use Cake\I18n\FrozenTime;
use Cake\I18n\FrozenDate;
use App\I18n\FrozenDateTime;
use Cake\Core\Configure;
use Cake\Controller\Component\AuthComponent;
use Cake\Controller\Exception\SecurityException;
use Cake\Routing\Router;
use Cake\Http\Exception\NotFoundException;

use App\Model\Table\UsersTable;
use App\Datasource\Paginator;

use Shim\Controller\Controller as ShimController;



class AppController extends ShimController {
	// Menu definitions
	// The entries must be key'ed array indices, even if the definition is empty
	public $controller_menu = array(		
		'users' => array(
			'title'=> 'Users',
			'controller' => 'users',
			'controllers' => array('Users', 'Groups')
		),
		'permissions' => array(
			'title' => 'Permissions', 
			'controller' => 'Acos', 
			'plugin' => 'AclEdit',
			'action' => 'admin_index', 
			'acl_path' => 'controllers/AclEdit/Acos/admin_index'
		),
		// 'media', 
		'people' => array(),
		'nations' => array('title' => 'Associations', 'controller' => 'Nations'),
		'types' => array('title' => 'Functions', 'controller' => 'Types'),
		'tournaments' => array(),
	);

	public $tournament_controller_menu = array(
		'competitions' => array(),
		'registrations' => array(),
		'orders' => array(
			'title' => 'Shop',
			'controller' => 'Orders',
			'controllers' => array('Orders', 'Shops', 'Articles', 'Allotments'),
			'plugin' => 'Shop',
			'action' => 'index',
			'acl_path' => 'controllers/Shop/Orders/index'
		), 
	);

	protected $_user;
	protected $_tournament;
	
	public function initialize() : void {
		$this->loadComponent('RequestHandler', [
			'enableBeforeRedirect' => false
		]);
		
		parent::initialize();
		
		$this->loadComponent('Acl.Acl');

		$this->loadComponent('Auth', [
			// 'checkAuthIn' => 'Controller.initialize',
			'authorize' => [
				'Acl.Actions' => ['actionPath' => 'controllers/']
			],
			'authenticate' => [
				AuthComponent::ALL => [
					'passwordHasher' => [
						'className' => 'Fallback',
						'hashers' => [
							'Default',
							'Weak' => ['hashType' => 'sha1']
						]
					]
				],
				'Form'
			],
			'loginAction' => ['plugin' => null, 'controller' => 'users', 'action' => 'login'],
			'logoutRedirect' => ['plugin' => null, 'controller' => 'users', 'action' => 'login'],
			'loginRedirect' => ['plugin' => null, 'controller' => 'tournaments', 'action' => 'index'],
			'autoRedirect' => false
		]);		
		
		$this->loadComponent('Security');
		
		// sort-*, direction-* are secondary (previous) sort parameters:
		// A click on a header will first sort by this header, then by what was the previous sort
		$this->loadComponent('Paginator', [
	        'allowedParameters' => ['limit', 'sort', 'page', 'direction', 'sort-0', 'direction-0', 'sort-1', 'direction-1'],
			'paginator' => new Paginator()
		]);
		
		$this->loadComponent('MultipleFlash');
		
		$this->viewBuilder()->setHelpers([
			'Html', 
			'Form', 
			'MultipleFlash'			
		]);
	}
		
	// Pagination throws NotFoundException if the page is out of range
	public function paginate($object = null, array $settings = []) {
		try {
			return parent::paginate($object, $settings);
		} catch (NotFoundException $e) {
			$query = $this->request->getQueryParams();
			if (isset($query['page']) && $query['page'] > 1) {
				$query['page'] = 1;
				$this->request = $this->request->withQueryParams($query);
				return $this->paginate($object, $settings);
			} else {
				return array();
			}
		}
	}
	
	public function beforeFilter(EventInterface $event) {
		parent::beforeFilter($event);
		
		// Don't validate Ajax calls
		if ($this->request->is('ajax'))
			$this->Security->setConfig('validatePost', false);

		// Default format for DateTime
		FrozenDateTime::setToStringFormat('yyyy-MM-dd HH:mm:ss');
		FrozenDate::setToStringFormat('yyyy-MM-dd');
		FrozenTime::setToStringFormat('HH:mm');

		// I will always need the languages			
		$this->loadModel('Languages');
		$this->set('languages', $this->Languages->find('list', array(
			'fields' => array('id', 'description'),
			'order' => 'description'
		))->toArray());

		$lang = $this->_getLanguage();
		
		I18n::setLocale($lang);
		
		$this->set('language_id', $this->Languages->fieldByConditions('id', array('name' => $lang)));

		// Warning when used with google translate
		if (strpos($this->request->referer(), '://translate.google') !== false) {
			$this->MultipleFlash->setFlash(
					'This site may not work when translated with translate.google.com. ' .
					'Use the extension "Google Translate" from Google instead.', 
					'warning'
			);
		}
		
		$this->loadModel('Users');

		// Default format for DateTime
		FrozenTime::setToStringFormat('yyyy-MM-dd HH:mm:ss');
		FrozenDate::setToStringFormat('yyyy-MM-dd');

		// Always needed
		
		$this->Session = $this->request->getSession();

		$this->_user = $this->Users->find('all', array(
			'conditions' => array('Users.id' => $this->Auth->user('id') ?: 0),
			'contain' => array('Groups')
		))->first();
		$user = &$this->_user; 

		$this->Security->setConfig('blackHoleCallback', '_blackHoleCallback');

		// Force to https://
		if (!Configure::read('App.allowUnsecure'))
			$this->Security->requireSecure('login');

		// Various checks
		// Check for authorization of restricted users
		// Nation.id (e.g. for group Association)
		if (!empty($user['nation_id']))
			$this->request->getSession()->write('Nations.id', $user['nation_id']);

		// Tournament.id (e.g. for group Organizer)
		if (!empty($user['tournament_id']))
			$this->request->getSession()->write('Tournaments.id', $user['tournament_id']);
		else {
			$this->loadModel('Tournaments');
			if ($this->Tournaments->find('all')->count() === 1) {
				// Table::field() returns the field from the first record
				$this->request->getSession()->write('Tournaments.id', $this->Tournaments->find('all')->first()->id);
			}
		}

		// Type.id (e.g. for group Association)
		if (!$this->request->getSession()->check('Types.id') && !empty($user['group']['type_ids'])) {
			$types = explode(',', $user['group']['type_ids']);
			if (!in_array($this->request->getSession()->read('Types.id'), $types))
				$this->request->getSession()->delete('Types.id');
		}

		if (!empty($user['group']['type_ids']))
			$this->request->getSession()->write('Groups.type_ids', $user['group']['type_ids']);
		
		if ($this->request->getSession()->check('Config.language'))
			Configure::write('Config.language', $this->request->getSession()->read('Config.language'));
	}

	
	public function beforeRender(EventInterface $event) {
		parent::beforeRender($event);

		// There should be one and only one tournament
		if ($this->request->getSession()->check('Tournaments.id')) {
			$this->loadModel('Tournaments');
			$this->set('tournament', $this->Tournaments->find('all', array(
				'conditions' => array('Tournaments.id' => $this->request->getSession()->read('Tournaments.id')),
				'contain' => array(
					'Nations', 'Organizers', 'Committees', 'Hosts', 'Contractors', 'Dpas')
			))->first());
		}

		// Nation.id is the marker if an association was selected
		if ($this->request->getSession()->check('Nations.id')) {
			$this->loadModel('Nations');
			$this->set('nation', $this->Nations->findById($this->request->getSession()->read('Nations.id')));
		}

		// In some views (e.g. media) we have to check for permissions
		$this->set('Acl', $this->Acl);
		
		$user = &$this->_user; 

		if (empty($user['id'])) {
			$this->set('hasRootPrivileges', false);
			$this->set('isOrganizer', false);
			$this->set('isCompetitionManager', false);
			
			$this->set('current_user', null);
			return;
		}
		
		// Extract and set menu items which are allowed for the current user
		$menu = array();
		$cm = $this->controller_menu;
		if ($this->request->getSession()->check('Tournaments.id'))
			$cm = array_merge($cm, $this->tournament_controller_menu);

		// Permissions are for my eyes only
		if ($user['username'] !== 'theis')
			unset($cm[array_search('permissions', $cm)]);

		foreach ($cm as $key => $m) {
			$acl_path = '';

			if (!empty($m['acl_path']))
				$acl_path = $m['acl_path'];
			else if (empty($m['controller']))
				$acl_path  = ucwords($key) . '/index';
			else
				$acl_path  = ucwords($m['controller']) . '/index';

			// Check if the user is allowed to at least browse here
			if (!$this->Acl->check($user, $acl_path))
				continue;
			
			$menu[] = count($m) === 0 ? $key : $m;
		}

		$this->set('controllerMenu', $menu);

		$this->set('current_user', $user);

		// Shortcut for root privileges
		$hasRootPrivileges = UsersTable::hasRootPrivileges($user);
		$isOrganizer = false;
		$isCompetitionManager = false;
		
		// Tournaments.id is the marker if a tournament was selected
		if (!empty($this->_tournament)) {
			$this->set('tournament', $this->_tournament);
			
			$isOrganizer = 
				$user->group_id == GroupsTable::getOrganizerId() &&
				$this->_tournament->id == $user->tournament_id;

			$isCompetitionManager = 
				$user->group_id == GroupsTable::getCompetitionManagerId() &&
				$this->_tournament->competition_manager_id == $user->id;			
		}
		
		$this->set('hasRootPrivileges', $hasRootPrivileges);
		$this->set('isOrganizer', $isOrganizer);
		$this->set('isCompetitionManager', $isCompetitionManager);
		
	}
	
	
	function _getCurrentUser() {
		return $this->_user;
	}
	
	function _getLanguage() {
		$this->loadModel('Languages');
		
		$langs = $this->Languages->find('list', array(
			'fields' => ['id', 'name']
		))->toArray();
		
		if (!empty($this->_user['language_id']))
			$lang = $langs[$this->_user->language_id] ?: null;
		
		if (empty($lang))
			$lang = $this->request->getSession()->read('Config.language');
		
		if (empty($lang))
			$lang = I18n::getLocale();
		
		if (strlen($lang) > 2) 
			$lang = substr($lang, 0, 2);
		
		if (!in_array($lang, array_values($langs)))
			$lang = 'en';
		
		return $lang;
	}

	// Create Acl path form action array
	function _makeAclPath($where) {
		$path = '';

		if (!empty($where['plugin']))
			$path .= (Inflector::camelize($where['plugin']) . '/');

		$path .= (ucwords($where['controller']) . '/');

		if (!empty($where['action']))
			$path .= $where['action'];
		else if (!empty($where['admin']))
			$path .= 'admin_index';
		else
			$path .= 'index';

		return $path;
	}

	function _blackHoleCallback($error = '', SecurityException $ex = null) {
		switch ($error) {
			case 'secure' :
				return $this->redirect('https://' . env('SERVER_NAME') . Router::url($this->request->getRequestTarget()));
		}
		
		throw $ex;
	}

	// Open a file with implicite conversion to UTF-8
	// $encoding is the default encoding if nothing else can be detected
	function _openFile($name, $mode, $encoding) {
		$file = fopen($name, $mode);

		$bom = fread($file, 2);
		rewind($file);
		if ($bom === chr(0xFF) . chr(0xFE))
			$encoding = 'UTF-16';
		else if ($bom === chr(0xFE) . chr(0xFF))
			$encoding = 'UTF-16';
		else if ($bom[0] === chr(0x00))
			$encoding = 'UTF-16';
		else if ($bom[1] === chr(0x00))
			$encoding = 'UTF-16';
		
		stream_filter_append($file, 'convert.iconv.' . $encoding . '/UTF-8');

		return $file;
	}

	// Parse a date string
	function _parseDate($str) {
		if (empty($str))
			return null;

		if (strpos($str, '.') > 0) {
			$fields = explode('.', $str);
			$str = ($fields[2] < 100 ? 2000 + $fields[2] : $fields[2]) . '-' . $fields[1] . '-' . $fields[0];
		}

		if (empty($str))
			return null;
		else if (strtotime($str) === false)
			return null;
		else if (date('Y', strtotime($str)) < 2000)
			return null;
		else
			return date('Y-m-d', strtotime($str));
	}

	// Parse a time string
	function _parseTime($str) {
		if (empty($str))
			return null;

		if (strtotime($str) === false)
			return null;

		return $str;
	}
}
