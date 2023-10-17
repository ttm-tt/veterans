<?php
/**
 *
 * @author   Nicolas Rod <nico@alaxos.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.alaxos.ch
 * 
 * @property AclReflectorComponent $AclReflector 
 */

namespace AclEdit\Controller;

use AclEdit\Controller\AclAppController;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Utility\Text;


class ArosController extends AclEditAppController
{
	// Models loaded on the fly
	public $Groups = null;
	public $Permissions = null;
	
	function initialize() : void {
		parent::initialize();
		
		$this->loadComponent('Acl.Acl');
		
	}
	function beforeFilter(EventInterface $event)
	{
	    $this->loadModel(Configure :: read('acl.aro.role.model'));
	    $this->loadModel(Configure :: read('acl.aro.user.model'));
		
		$this->loadModel('Acl.Aros');
		$this->loadModel('Acl.Permissions');
		
	    parent :: beforeFilter($event);
	}
    
	function admin_index()
	{
	    
	}
	
	function admin_check($run = null)
	{
		$user_model_name = Configure :: read('acl.aro.user.model');
	    $role_model_name = Configure :: read('acl.aro.role.model');
	    
	    $user_display_field = $this->AclManager->set_display_name($user_model_name, Configure :: read('acl.user.display_name'));
	    $role_display_field = $this->AclManager->set_display_name($role_model_name, Configure :: read('acl.aro.role.display_field'));
	    
	    $this->set('user_display_field', $user_display_field);
	    $this->set('role_display_field', $role_display_field);
	    
		$roles = $this->{$role_model_name}->find('all', array('order' => $role_display_field, 'contain' => false, 'recursive' => -1));
	 	
		$missing_aros = array('roles' => array(), 'users' => array());
	    
		foreach($roles as $role)
		{
			/*
			 * Check if ARO for role exist
			 */
			$aro = $this->Aros->find('all', array(
				'conditions' => array(
					'model' => $role_model_name, 
					'foreign_key' => $role[$role_model_name][$this->_get_role_primary_key_name()]
				)
			))->first();
			
			if($aro === false)
			{
				$missing_aros['roles'][] = $role;
			}
		}
		
		$users = $this->{$user_model_name}->find('all', array('order' => $user_display_field, 'contain' => false, 'recursive' => -1));
		foreach($users as $user)
		{
			/*
			 * Check if ARO for user exist
			 */
			$aro = $this->Aros->find('all', array(
				'conditions' => array(
					'model' => $user_model_name, 
					'foreign_key' => $user[$user_model_name][$this->_get_user_primary_key_name()]
				)
			))->first();
			
			if($aro === false)
			{
				$missing_aros['users'][] = $user;
			}
		}
		
		
		if(isset($run))
		{
			$this->set('run', true);
			
			/*
			 * Complete roles AROs
			 */
			if(count($missing_aros['roles']) > 0)
			{
				foreach($missing_aros['roles'] as $k => $role)
				{
					$this->Aros->create(array('parent_id' 		=> null,
												'model' 		=> $role_model_name,
												'foreign_key' 	=> $role[$this->_get_role_primary_key_name()],
												'alias'			=> $role[$role_display_field]));
					
					if($this->Aros->save())
					{
						unset($missing_aros['roles'][$k]);
					}
				}
			}
			
			/*
			 * Complete users AROs
			 */
			if(count($missing_aros['users']) > 0)
			{
				foreach($missing_aros['users'] as $k => $user)
				{
					/*
					 * Find ARO parent for user ARO
					 */
					$parent_id = $this->Aros->fieldByConditions('id', array('model' => $role_model_name, 'foreign_key' => $user[$user_model_name][$this->_get_role_foreign_key_name()]));
					
					if($parent_id !== false)
					{
						$this->Aros->create(array('parent_id' 		=> $parent_id,
													'model' 		=> $user_model_name,
													'foreign_key' 	=> $user[$user_model_name][$this->_get_user_primary_key_name()],
													'alias'			=> $user[$user_model_name][$user_display_field]));
						
						if($this->Aros->save())
						{
							unset($missing_aros['users'][$k]);
						}
					}
				}
			}
		}
		else
		{
			$this->set('run', false);
		}
		
		$this->set('missing_aros', $missing_aros);
		
	}
	
	function admin_users()
	{
	    $user_model_name = Configure :: read('acl.aro.user.model');
	    $role_model_name = Configure :: read('acl.aro.role.model');
	    
	    $user_display_field = $this->AclManager->set_display_name($user_model_name, Configure :: read('acl.user.display_name'));
	    $role_display_field = $this->AclManager->set_display_name($role_model_name, Configure :: read('acl.aro.role.display_field'));
	    
	    $this->paginate['order'] = array($user_display_field => 'asc');
	    
	    $this->set('user_display_field', $user_display_field);
	    $this->set('role_display_field', $role_display_field);
	    
	    $roles = $this->{$role_model_name}->find('all', array('order' => $role_display_field, 'contain' => false, 'recursive' => -1));
	 
	    if(isset($this->request->data['User'][$user_display_field]) || $this->request->getSession()->check('acl.aros.users.filter'))
	    {
	        if(!isset($this->request->data['User'][$user_display_field]))
	        {
	            $this->request->data['User'][$user_display_field] = $this->request->getSession()->read('acl.aros.users.filter');
	        }
	        else
	        {
	            $this->request->getSession()->write('acl.aros.users.filter', $this->request->data['User'][$user_display_field]);
	        }
	        
	        $filter = array($user_model_name . '.' . $user_display_field . ' LIKE' => '%' . $this->request->data['User'][$user_display_field] . '%');
	    }
	    else
	    {
	        $filter = array();
	    }
	    
	    $users = $this->paginate($user_model_name, $filter);
	    
	    $missing_aro = false;
	    
	    foreach($users as $user)
	    {
	    	$aro = $this->Aros->find('all', array(
				'conditions' => array(
					'model' => $user_model_name, 
					'foreign_key' => $user[$this->_get_user_primary_key_name()]
				)
			))->first();

	        if($aro !== false)
	        {
	            $user['Aro'] = $aro;
	        }
	        else
	        {
	            $missing_aro = true;
	        }
	    }

	    $this->set('roles', $roles);
	    $this->set('users', $users);
	    $this->set('missing_aro', $missing_aro);
	}

	function admin_update_user_role()
	{
	    $user_model_name = Configure :: read('acl.aro.user.model');

        $data = array($user_model_name => array($this->_get_user_primary_key_name() => $this->request->getQuery('user'), $this->_get_role_foreign_key_name() => $this->request->getQuery('role')));

	    if($this->{$user_model_name}->save($data))
	    {
	        $this->request->getSession()->setFlash(__d('acl', 'The user role has been updated'), 'flash_message', null, 'plugin_acl');
	    }
	    else
	    {
	        $errors = array_merge(array(__d('acl', 'The user role could not be updated')), $this->{$user_model_name}->validationErrors);
	        $this->request->getSession()->setFlash($errors, 'flash_error', null, 'plugin_acl');
	    }

	    $this->_return_to_referer();
	}

	function admin_ajax_role_permissions()
	{
		$role_model_name = Configure :: read('acl.aro.role.model');

		$role_display_field = $this->AclManager->set_display_name($role_model_name, Configure :: read('acl.aro.role.display_field'));

	    $this->set('role_display_field', $role_display_field);

	    $roles = $this->{$role_model_name}->find('all', array(
			'order' => $role_display_field
		));

	    $actions = $this->AclReflector->get_all_actions();
		
	    $methods = array();
		foreach($actions as $k => $full_action)
    	{
	    	$arr = Text::tokenize($full_action, '/');

			if (count($arr) == 2)
			{
				$plugin_name     = null;
				$controller_name = $arr[0];
				$action          = $arr[1];
			}
			elseif(count($arr) == 3)
			{
				$plugin_name     = $arr[0];
				$controller_name = $arr[1];
				$action          = $arr[2];
			}

			if($controller_name == 'App')
			{
			    unset($actions[$k]);
			}
			else
			{
        		if(isset($plugin_name))
                {
                	$methods['plugin'][$plugin_name][$controller_name][] = array('name' => $action);
                }
                else
                {
            	    $methods['app'][$controller_name][] = array('name' => $action);
                }
			}
    	}

	    $this->set('roles', $roles);
	    $this->set('actions', $methods);
	}

	function admin_role_permissions()
	{
	    $role_model_name = Configure :: read('acl.aro.role.model');

	    $role_display_field = $this->AclManager->set_display_name($role_model_name, Configure :: read('acl.aro.role.display_field'));

	    $this->set('role_display_field', $role_display_field);

	    $roles = $this->{$role_model_name}->find('all', array('order' => $role_display_field, 'contain' => false, 'recursive' => -1));

	    $actions = $this->AclReflector->get_all_actions();

	    $permissions = array();
	    $methods     = array();

	    foreach($actions as $full_action)
    	{
	    	$arr = Text::tokenize($full_action, '/');

			if (count($arr) == 2)
			{
				$plugin_name     = null;
				$controller_name = $arr[0];
				$action          = $arr[1];
			}
			elseif(count($arr) == 3)
			{
				$plugin_name     = $arr[0];
				$controller_name = $arr[1];
				$action          = $arr[2];
			}

			if($controller_name != 'App')
			{
    		    foreach($roles as $role)
    	    	{
    	    	    $aro_node = $this->Aros->node($role);
    	            if(!empty($aro_node))
    	            {
    	                $aco_node = $this->Acl->Aco->node('controllers/' . $full_action);
    	        	    if(!empty($aco_node))
    	        	    {
    	        	    	$authorized = $this->Acl->check($role, 'controllers/' . $full_action);

    	        	    	$permissions[$role[$this->_get_role_primary_key_name()]] = $authorized ? 1 : 0 ;
    					}
    	            }
    	    		else
            	    {
            	        /*
            	         * No check could be done as the ARO is missing
            	         */
            	        $permissions[$role[$this->_get_role_primary_key_name()]] = -1;
            	    }
        		}

        		if(isset($plugin_name))
                {
                	$methods['plugin'][$plugin_name][$controller_name][] = array('name' => $action, 'permissions' => $permissions);
                }
                else
                {
            	    $methods['app'][$controller_name][] = array('name' => $action, 'permissions' => $permissions);
                }
			}
    	}

	    $this->set('roles', $roles);
	    $this->set('actions', $methods);
	}

	function admin_user_permissions($user_id = null)
	{
	    $user_model_name = Configure :: read('acl.aro.user.model');
	    $role_model_name = Configure :: read('acl.aro.role.model');

	    $user_display_field = $this->AclManager->set_display_name($user_model_name, Configure :: read('acl.user.display_name'));

	    $this->paginate['order'] = array($user_display_field => 'asc');
	    $this->set('user_display_field', $user_display_field);

	    if(empty($user_id))
	    {
    	    if(isset($this->request->data['User'][$user_display_field]) || $this->request->getSession()->check('acl.aros.user_permissions.filter'))
    	    {
    	        if(!isset($this->request->data['User'][$user_display_field]))
    	        {
    	            $this->request->data['User'][$user_display_field] = $this->request->getSession()->read('acl.aros.user_permissions.filter');
    	        }
    	        else
    	        {
    	            $this->request->getSession()->write('acl.aros.user_permissions.filter', $this->request->data['User'][$user_display_field]);
    	        }

    	        $filter = array($user_model_name . '.' . $user_display_field . ' LIKE' => '%' . $this->request->data['User'][$user_display_field] . '%');
    	    }
    	    else
    	    {
    	        $filter = array();
    	    }

	        $users = $this->paginate($user_model_name, $filter);

	        $this->set('users', $users);
	    }
	    else
	    {
	    	$role_display_field = $this->AclManager->set_display_name($role_model_name, Configure :: read('acl.aro.role.display_field'));

	    	$this->set('role_display_field', $role_display_field);

	        $roles = $this->{$role_model_name}->find('all', array('order' => $role_display_field, 'contain' => false, 'recursive' => -1));

	        $user = $this->{$user_model_name}->find('first', array($this->_get_user_primary_key_name() => $user_id));

	        $permissions = array();
	    	$methods     = array();

	        /*
             * Check if the user exists in the ARO table
             */
            $user_aro = $this->Aros->node($user)->toArray();
            if(empty($user_aro))
            {
                $display_user = $this->{$user_model_name}->find('first', array('conditions' => array($user_model_name . '.id' => $user_id, 'contain' => false, 'recursive' => -1)));
                $this->request->getSession()->setFlash(sprintf(__d('acl', "The user '%s' does not exist in the ARO table"), $display_user[$user_display_field]), 'flash_error', null, 'plugin_acl');
            }
            else
            {
            	$actions = $this->AclReflector->get_all_actions();

	            foreach($actions as $full_action)
		    	{
			    	$arr = Text::tokenize($full_action, '/');

					if (count($arr) == 2)
					{
						$plugin_name     = null;
						$controller_name = $arr[0];
						$action          = $arr[1];
					}
					elseif(count($arr) == 3)
					{
						$plugin_name     = $arr[0];
						$controller_name = $arr[1];
						$action          = $arr[2];
					}

					if($controller_name != 'App')
					{
    					if(!($this->request->getQuery('ajax')))
    					{
        		    		$aco_node = $this->Acl->Aco->node('controllers/' . $full_action);
        	        	    if(!empty($aco_node))
        	        	    {
        	        	    	$authorized = $this->Acl->check($user, 'controllers/' . $full_action);

        	        	    	$permissions[$user[$this->_get_user_primary_key_name()]] = $authorized ? 1 : 0 ;
        					}
    					}

    			    	if(isset($plugin_name))
    		            {
    		            	$methods['plugin'][$plugin_name][$controller_name][] = array('name' => $action, 'permissions' => $permissions);
    		            }
    		            else
    		            {
    		        	    $methods['app'][$controller_name][] = array('name' => $action, 'permissions' => $permissions);
    		            }
					}
		    	}

		    	/*
		    	 * Check if the user has specific permissions
		    	 */
		    	$count = $this->Permissions->find('all', array('conditions' => array('aro_id' => $user_aro[0]['id'])))->count();
		    	if($count != 0)
		    	{
		    	    $this->set('user_has_specific_permissions', true);
		    	}
		    	else
		    	{
		    	    $this->set('user_has_specific_permissions', false);
		    	}
            }

            $this->set('user', $user);
            $this->set('roles', $roles);
            $this->set('actions', $methods);

            if($this->request->getQuery('ajax'))
            {
                $this->render('admin_ajax_user_permissions');
            }
	    }
	}

	function admin_empty_permissions()
	{
	    if($this->Aros->Permission->deleteAll(array('Permission.id > ' => 0)))
	    {
	        $this->request->getSession()->setFlash(__d('acl', 'The permissions have been cleared'), 'flash_message', null, 'plugin_acl');
	    }
	    else
	    {
	        $this->request->getSession()->setFlash(__d('acl', 'The permissions could not be cleared'), 'flash_error', null, 'plugin_acl');
	    }

	    $this->_return_to_referer();
	}

	function admin_clear_user_specific_permissions($user_id)
	{
	    $user = $this->{Configure :: read('acl.aro.user.model')}->newEmptyEntity();
	    $user->id = $user_id;

	    /*
         * Check if the user exists in the ARO table
         */
        $node = $this->Aros->node($user);
        if(empty($node))
        {
            $asked_user = $user->read(null, $user_id);
            $this->request->getSession()->setFlash(sprintf(__d('acl', "The user '%s' does not exist in the ARO table"), $asked_user['User'][Configure :: read('acl.user.display_name')]), 'flash_error', null, 'plugin_acl');
        }
        else
        {
            if($this->Aros->Permission->deleteAll(array('Aro.id' => $node[0]['Aro']['id'])))
    	    {
    	        $this->request->getSession()->setFlash(__d('acl', 'The specific permissions have been cleared'), 'flash_message', null, 'plugin_acl');
    	    }
    	    else
    	    {
    	        $this->request->getSession()->setFlash(__d('acl', 'The specific permissions could not be cleared'), 'flash_error', null, 'plugin_acl');
    	    }
        }

	    $this->_return_to_referer();
	}

	function admin_grant_all_controllers($role_id)
	{
	    $role = $this->{Configure :: read('acl.aro.role.model')}->newEmptyEntity();
        $role->id = $role_id;

		/*
         * Check if the Role exists in the ARO table
         */
        $node = $this->Aros->node($role);
        if(empty($node))
        {
            $asked_role = $role->read(null, $role_id);
            $this->request->getSession()->setFlash(sprintf(__d('acl', "The role '%s' does not exist in the ARO table"), $asked_role['Role'][Configure :: read('acl.aro.role.display_field')]), 'flash_error', null, 'plugin_acl');
        }
        else
        {
            //Allow to everything
            $this->Acl->allow($role, 'controllers');
        }

	    $this->_return_to_referer();
	}
	function admin_deny_all_controllers($role_id)
	{
	    $role = $this->{Configure :: read('acl.aro.role.model')}->newEmptyEntity();
        $role->id = $role_id;

        /*
         * Check if the Role exists in the ARO table
         */
        $node = $this->Aros->node($role);
        if(empty($node))
        {
            $asked_role = $role->read(null, $role_id);
            $this->request->getSession()->setFlash(sprintf(__d('acl', "The role '%s' does not exist in the ARO table"), $asked_role['Role'][Configure :: read('acl.aro.role.display_field')]), 'flash_error', null, 'plugin_acl');
        }
        else
        {
            //Deny everything
            $this->Acl->deny($role, 'controllers');
        }

	    $this->_return_to_referer();
	}

	function admin_get_role_controller_permission($role_id)
	{
		$role =& $this->{Configure :: read('acl.aro.role.model')};

        $role_data = $role->get($role_id);

        $aro_node = $this->Aros->node($role_data);
        if(!empty($aro_node))
        {
	        $plugin_name        = $this->request->getQuery('plugin') ?: '';
	        $controller_name    = $this->request->getQuery('controller');
			$controller_class_name = (empty($plugin_name) ? 'App' : $plugin_name) . '\\Controller\\' . $controller_name . 'Controller';
	        $controller_actions = $this->AclReflector->get_controller_actions($controller_class_name);

	        $role_controller_permissions = array();

	        foreach($controller_actions as $action_name)
	        {
	        	$aco_path  = $plugin_name;
		        $aco_path .= empty($aco_path) ? $controller_name : '/' . $controller_name;
		        $aco_path .= '/' . $action_name;

		        $aco_node = $this->Acl->Aco->node('controllers/' . $aco_path);
        	    if(!empty($aco_node))
        	    {
        	        $authorized = $this->Acl->check($role_data, 'controllers/' . $aco_path);
        	        $role_controller_permissions[$action_name] = $authorized;
        	    }
        	    else
        	    {
        	        $role_controller_permissions[$action_name] = -1;
        	    }
	        }
	    }
		else
        {
        	//$this->set('acl_error', true);
            //$this->set('acl_error_aro', true);
        }

		if($this->request->is('ajax'))
        {
        	Configure::write('debug', 0); //-> to disable printing of generation time preventing correct JSON parsing
        	echo json_encode($role_controller_permissions);
        	$this->autoRender = false;
        }
        else
        {
            $this->_return_to_referer();
        }
	}
	function admin_grant_role_permission($role_id)
	{
	    $role = $this->{Configure :: read('acl.aro.role.model')}->newEmptyEntity();

        $role->id = $role_id;

        $aco_path = $this->_get_passed_aco_path();

        /*
         * Check if the role exists in the ARO table
         */
        $aro_node = $this->Aros->node($role);
        if(!empty($aro_node))
        {
            if(!$this->AclManager->save_permission($aro_node->toArray(), $aco_path, 'grant'))
            {
                $this->set('acl_error', true);
            }
        }
        else
        {
            $this->set('acl_error', true);
            $this->set('acl_error_aro', true);
        }

        $this->set('role_id', $role_id);
        $this->_set_aco_variables();

        if($this->request->is('ajax'))
        {
            $this->render('ajax_role_granted');
        }
        else
        {
            $this->_return_to_referer();
        }
	}
	function admin_deny_role_permission($role_id)
	{
	    $role = $this->{Configure :: read('acl.aro.role.model')}->newEmptyEntity();

        $role->id = $role_id;

        $aco_path = $this->_get_passed_aco_path();

        $aro_node = $this->Aros->node($role);
        if(!empty($aro_node))
        {
            if(!$this->AclManager->save_permission($aro_node->toArray(), $aco_path, 'deny'))
            {
                $this->set('acl_error', true);
            }
        }
        else
        {
        	$this->set('acl_error', true);
        }

        $this->set('role_id', $role_id);
        $this->_set_aco_variables();

        if($this->request->is('ajax'))
        {
            $this->render('ajax_role_denied');
        }
        else
        {
            $this->_return_to_referer();
        }
	}

	function admin_get_user_controller_permission($user_id)
	{
        $user =& $this->{Configure :: read('acl.aro.user.model')};

	    $user_data = $user->find('first', array(
			'conditions' => [$this->_get_user_primary_key_name() => $user_id]
		));

        $aro_node = $this->Aros->node($user_data);
        if(!empty($aro_node))
        {
	        $plugin_name        = $this->request->getQuery('plugin') ?: '';
	        $controller_name    = $this->request->getQuery('controller');
	        $controller_actions = $this->AclReflector->get_controller_actions($controller_name);

	        $user_controller_permissions = array();

	        foreach($controller_actions as $action_name)
	        {
	        	$aco_path  = $plugin_name;
		        $aco_path .= empty($aco_path) ? $controller_name : '/' . $controller_name;
		        $aco_path .= '/' . $action_name;

		        $aco_node = $this->Acl->Aco->node('controllers/' . $aco_path);
        	    if(!empty($aco_node))
        	    {
        	        $authorized = $this->Acl->check($user_data, 'controllers/' . $aco_path);
        	        $user_controller_permissions[$action_name] = $authorized;
        	    }
        	    else
        	    {
        	        $user_controller_permissions[$action_name] = -1;
        	    }
	        }
	    }
		else
        {
        	//$this->set('acl_error', true);
            //$this->set('acl_error_aro', true);
        }

		if($this->request->is('ajax'))
        {
        	Configure::write('debug', 0); //-> to disable printing of generation time preventing correct JSON parsing
        	echo json_encode($user_controller_permissions);
        	$this->autoRender = false;
        }
        else
        {
            $this->_return_to_referer();
        }
	}
	function admin_grant_user_permission($user_id)
	{
	    $user = $this->{Configure :: read('acl.aro.user.model')}->newEmptyEntity();

        $user->id = $user_id;

        $aco_path = $this->_get_passed_aco_path();

        /*
         * Check if the user exists in the ARO table
         */
        $aro_node = $this->Aros->node($user);
        if(!empty($aro_node))
        {
        	$aco_node = $this->Acl->Aco->node('controllers/' . $aco_path);
        	if(!empty($aco_node))
        	{
	            if(!$this->AclManager->save_permission($aro_node->toArray(), $aco_path, 'grant'))
	            {
	                $this->set('acl_error', true);
	            }
        	}
        	else
        	{
        		$this->set('acl_error', true);
            	$this->set('acl_error_aco', true);
        	}
        }
        else
        {
            $this->set('acl_error', true);
            $this->set('acl_error_aro', true);
        }

        $this->set('user_id', $user_id);
        $this->_set_aco_variables();

        if($this->request->is('ajax'))
        {
            $this->render('ajax_user_granted');
        }
        else
        {
            $this->_return_to_referer();
        }
	}
	function admin_deny_user_permission($user_id)
	{
	    $user = $this->{Configure :: read('acl.aro.user.model')}->newEmptyEntity();

        $user->id = $user_id;

        $aco_path = $this->_get_passed_aco_path();

        /*
         * Check if the user exists in the ARO table
         */
        $aro_node = $this->Aros->node($user);
        if(!empty($aro_node))
        {
        	$aco_node = $this->Acl->Aco->node('controllers/' . $aco_path);
        	if(!empty($aco_node))
        	{
        	    if(!$this->AclManager->save_permission($aro_node->toArray(), $aco_path, 'deny'))
	            {
	                $this->set('acl_error', true);
	            }
        	}
        	else
        	{
        		$this->set('acl_error', true);
            	$this->set('acl_error_aco', true);
        	}
        }
        else
        {
            $this->set('acl_error', true);
            $this->set('acl_error_aro', true);
        }
        
        $this->set('user_id', $user_id);
        $this->_set_aco_variables();
        
        if($this->request->is('ajax'))
        {
            $this->render('ajax_user_denied');
        }
        else
        {
            $this->_return_to_referer();
        }
	}
}
