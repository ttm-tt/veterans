<?php
/**
 *
 * @author   Nicolas Rod <nico@alaxos.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.alaxos.ch
 */
namespace AclEdit\Controller;

use AclEdit\Controller\AclEditAppController;

class AclEditController extends AclEditAppController {

	function index()
	{
	    $this->redirect('/AclEdit/aros/admin_index');
	}
	
	function admin_index()
	{
	    $this->redirect('/AclEdit/acos/admin_index');
	}
	
}
