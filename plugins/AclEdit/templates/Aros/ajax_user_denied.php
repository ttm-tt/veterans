<?php
    if(isset($acl_error))
    {
    	if(isset($acl_error_aro))
    	{
    		$title = __d('acl', 'The user node does not exist in the ARO table');
    	}
    	elseif(isset($acl_error_aco))
    	{
    		$title = __d('acl', 'The ACO node is probably missing. Please try to rebuild the ACOs first.');
    	}
    	else
    	{
    		$title = __d('acl', 'The ARO or the ACO node is probably missing. Please try to rebuild the ACOs first.');
    	}
    	
        echo $this->Html->image('/AclEdit/img/design/important16.png', array('class' => 'pointer', 'alt' => $title, 'title' => $title));
    }
    else
    {
        echo $this->Html->image('/AclEdit/img/design/cross.png', array('class' => 'pointer'));
    }
?>