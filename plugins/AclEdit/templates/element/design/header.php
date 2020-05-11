<?php
echo $this->Html->css('/AclEdit/css/acl.css');
?>
<div id="plugin_acl">
	
	<?php
	echo $this->Flash->render('plugin_acl');
	?>
	
	<h1><?php echo __d('acl', 'ACL plugin'); ?></h1>
	
	<?php

	if(!isset($no_acl_links))
	{
	    $selected = isset($selected) ? $selected : $this->request->getParam('controller');
    
        $links = array();
        $links[] = $this->Html->link(__d('acl', 'Permissions'), ['controller' => 'Aros', 'action' => 'admin_index'], array('class' => ($selected == 'Aros' )? 'selected' : null));
        $links[] = $this->Html->link(__d('acl', 'Actions'), ['controller' => 'Acos', 'action' => 'admin_index'], array('class' => ($selected == 'Acos' )? 'selected' : null));
        
        echo $this->Html->nestedList($links, array('class' => 'acl_links'));
	}
	?>