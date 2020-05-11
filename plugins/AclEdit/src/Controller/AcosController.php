<?php
/**
 *
 * @author   Nicolas Rod <nico@alaxos.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.alaxos.ch
 *
 * @property AclManagerComponent $AclManager
 */

namespace AclEdit\Controller;

use AclEdit\Controller\AclEditAppController;


class AcosController extends AclEditAppController {
	 
	function admin_index()
	{
	    
	}
	
	function admin_empty_acos($run = null)
	{
	    if(isset($run))
	    {
    		if($this->Aco->deleteAll(array('id > 0')))
    	    {
    	        $this->request->session()->setFlash(__d('acl', 'The ACO table has been cleared'), 'flash_message', null, 'plugin_acl');
    	    }
    	    else
    	    {
    	        $this->request->session()->setFlash(__d('acl', 'The ACO table could not be cleared'), 'flash_error', null, 'plugin_acl');
    	    }
    	    
    	    $this->set('run', true);
	    }
	    else
	    {
	        $this->set('run', false);
	    }
	}
	
	function admin_build_acl($run = null)
	{
	    if(isset($run))
	    {
    		$logs = $this->AclManager->create_acos();
    		
    		$this->set('logs', $logs);
    		$this->set('run', true);
	    }
	    else
	    {
	        $missing_aco_nodes = $this->AclManager->get_missing_acos();
	        
	        $this->set('missing_aco_nodes',  $missing_aco_nodes);
	        
	        $this->set('run', false);
	    }
	}

    function admin_prune_acos($run = null)
    {
        if(isset($run))
        {
             $logs = $this->AclManager->prune_acos();
        
             $this->set('logs', $logs);
             $this->set('run', true);
        }
        else
        {
            $nodes_to_prune    = $this->AclManager->get_acos_to_prune();
            
            $this->set('nodes_to_prune', $nodes_to_prune);
            
            $this->set('run', false);
        }
    }
    
    function admin_synchronize($run = null)
    {
        if(isset($run))
        {
             $prune_logs  = $this->AclManager->prune_acos();
             $create_logs = $this->AclManager->create_acos();
    		
    		 $this->set('create_logs', $create_logs);
             $this->set('prune_logs',  $prune_logs);
             
             $this->set('run', true);
        }
        else
        {
            $nodes_to_prune    = $this->AclManager->get_acos_to_prune();
            $missing_aco_nodes = $this->AclManager->get_missing_acos();
            
            $this->set('nodes_to_prune', $nodes_to_prune);
            $this->set('missing_aco_nodes',  $missing_aco_nodes);
            
            $this->set('run', false);
        }
    }
}
