<?php
echo $this->Html->script('/AclEdit/js/jquery');
echo $this->Html->script('/AclEdit/js/acl_plugin');

echo $this->element('design/header');
?>

<?php
echo $this->element('Aros/links');
?>

<?php
if(isset($users))
{
?>
	<?php
	echo '<p>&nbsp;</p>';
	echo '<p>';
	echo __d('acl', 'This page allows to manage users specific rights');
	echo '</p>';
	echo '<p>&nbsp;</p>';
	?>
	<?php
    echo $this->Form->create($users);
    echo __d('acl', 'user');
    echo '<br/>';
    echo $this->Form->input($user_display_field, array('label' => false, 'div' => false));
    echo ' ';
    echo $this->Form->end(array('label' =>__d('acl', 'filter'), 'div' => false));
    echo '<br/>';
    ?>
    <table border="0" cellpadding="5" cellspacing="2">
    <tr>
    	<?php
    	$column_count = 1;
    	
    	$headers = array($this->Paginator->sort(__d('acl', 'user'), $user_display_field));
    	
    	echo $this->Html->tableHeaders($headers);
    	?>
    </tr>
    <?php
    foreach($users as $user)
    {
        echo '<tr>';
        echo '  <td>' . $user[$user_display_field] . '</td>';
        $title = __d('acl', 'Manage user specific rights');

        $link = '/AclEdit/aros/admin_user_permissions/' . $user[$user_pk_name];
        if(Cake\Core\Configure :: read('acl.gui.users_permissions.ajax') === true)
        {
             $link .= '/ajax:true';
        }
        
        echo '  <td>' . $this->Html->link($this->Html->image('/AclEdit/img/design/lock.png'), $link, array('alt' => $title, 'title' => $title, 'escape' => false)) . '</td>';

        echo '</tr>';
    }
    ?>
    </table>
	<?php echo $this->element('paginator'); ?>
<?php
}
else
{
?>
    <h1><?php echo  __d('acl', $user_model_name) . ' : ' . $user[$user_display_field]; ?></h1>
    
    <h2><?php echo __d('acl', 'Role'); ?></h2>
    
    <table border="0">
    <tr>
    	<?php
    	foreach($roles as $role)
    	{
    	    echo '<td>';
    	    
    	    echo $role[$role_display_field];
    	    if($role[$role_pk_name] == $user[$role_fk_name])
    	    {
    	        echo $this->Html->image('/AclEdit/img/design/tick.png');
    	    }
    	    else
    	    {
    	    	$title = __d('acl', 'Update the user role');
    	        echo $this->Html->link($this->Html->image('/AclEdit/img/design/tick_disabled.png'), array('plugin' => 'AclEdit', 'controller' => 'aros', 'action' => 'admin_update_user_role', 'user' => $user[$user_pk_name], 'role' => $role[$role_pk_name]), array('title' => $title, 'alt' => $title, 'escape' => false));
    	    }
    	    
    	    echo '</td>';
    	}
    	?>
    </tr>
    </table>
    
    <h2><?php echo __d('acl', 'Permissions'); ?></h2>
    
    <?php
	if($user_has_specific_permissions)
	{
	    echo '<div class="separator"></div>';
    	echo $this->Html->image('/AclEdit/img/design/bulb24.png') . __d('acl', 'This user has specific permissions');
    	echo ' (';
    	echo $this->Html->link($this->Html->image('/AclEdit/img/design/cross2.png', array('style' => 'vertical-align:middle;')) . ' ' . __d('acl', 'Clear'), '/AclEdit/aros/admin_clear_user_specific_permissions/' . $user[$user_pk_name], array('confirm' => __d('acl', 'Are you sure you want to clear the permissions specific to this user ?'), 'escape' => false));
    	echo ')';
    	echo '<div class="separator"></div>';
	}
	?>
	
    <table border="0" cellpadding="5" cellspacing="2">
	<tr>
    	<?php
    	
    	$column_count = 1;
    	
    	$headers = array(__d('acl', 'action'), __d('acl', 'authorization'));

    	echo $this->Html->tableHeaders($headers);
    	?>
	</tr>
	
	<?php
	$previous_ctrl_name = '';
	
	//debug($actions);
	if(isset($actions['app']) && is_array($actions['app']))
	{
    	foreach($actions['app'] as $controller_name => $ctrl_infos)
    	{
    		if($previous_ctrl_name != $controller_name)
    		{
    			$previous_ctrl_name = $controller_name;
    			
    			$color = (isset($color) && $color == 'color1') ? 'color2' : 'color1';
    		}
    		
    		foreach($ctrl_infos as $ctrl_info)
    		{
    			//debug($ctrl_info);
    			
        		echo '<tr class="' . $color . '">
        		';
        		
        		echo '<td>' . $controller_name . '->' . $ctrl_info['name'] . '</td>';
        		
    	    	echo '<td>';
    	    	echo '<span id="right__' . $user[$user_pk_name] . '_' . $controller_name . '_' . $ctrl_info['name'] . '">';
            			
        		if($ctrl_info['permissions'][$user[$user_pk_name]] == 1)
        		{
        		    // $this->Js->buffer('register_user_toggle_right(true, "' . $this->Url->build('/') . '", "right__' . $user[$user_pk_name] . '_' . $controller_name . '_' . $ctrl_info['name'] . '", "' . $user[$user_pk_name] . '", "", "' . $controller_name . '", "' . $ctrl_info['name'] . '")');
        		    $this->Html->scriptBlock('register_user_toggle_right(true, "' . $this->Url->build('/') . '", "right__' . $user[$user_pk_name] . '_' . $controller_name . '_' . $ctrl_info['name'] . '", "' . $user[$user_pk_name] . '", "", "' . $controller_name . '", "' . $ctrl_info['name'] . '")', ['block' => true]);
        		    
        		    echo $this->Html->image('/AclEdit/img/design/tick.png', array('class' => 'pointer'));
        		}
        		elseif($ctrl_info['permissions'][$user[$user_pk_name]] == 0)
        		{
        		    // $this->Js->buffer('register_user_toggle_right(false, "' . $this->Url->build('/') . '", "right__' . $user[$user_pk_name] . '_' . $controller_name . '_' . $ctrl_info['name'] . '", "' . $user[$user_pk_name] . '", "", "' . $controller_name . '", "' . $ctrl_info['name'] . '")');
        		    $this->Html->scriptBlock('register_user_toggle_right(false, "' . $this->Url->build('/') . '", "right__' . $user[$user_pk_name] . '_' . $controller_name . '_' . $ctrl_info['name'] . '", "' . $user[$user_pk_name] . '", "", "' . $controller_name . '", "' . $ctrl_info['name'] . '")', ['block' => true]);
        		    
        			echo $this->Html->image('/AclEdit/img/design/cross.png', array('class' => 'pointer'));
        		}
        		elseif($ctrl_info['permissions'][$user[$user_pk_name]] == -1)
        		{
        		    echo $this->Html->image('/AclEdit/img/design/important16.png');
        		}
        		
    	    	echo '</span>';
    	    	
    	    	echo ' ';
    	    	echo $this->Html->image('/AclEdit/img/ajax/waiting16.gif', array('id' => 'right__' . $user[$user_pk_name] . '_' . $controller_name . '_' . $ctrl_info['name'] . '_spinner', 'style' => 'display:none;'));
        		
    	    	echo '</td>';
    	    	echo '</tr>
    	    	';
    		}
    	}
	}
	?>
	<?php
	if(isset($actions['plugin']) && is_array($actions['plugin']))
	{
    	foreach($actions['plugin'] as $plugin_name => $plugin_ctrler_infos)
    	{
    	    echo '<tr class="title"><td colspan="2">' . __d('acl', 'Plugin') . ' ' . $plugin_name . '</td></tr>
    	    ';
    	    
    	    foreach($plugin_ctrler_infos as $plugin_ctrler_name => $plugin_methods)
    	    {
        	    if($previous_ctrl_name != $plugin_ctrler_name)
        		{
        			$previous_ctrl_name = $plugin_ctrler_name;
        			
        			$color = (isset($color) && $color == 'color1') ? 'color2' : 'color1';
        		}
    			
    	        foreach($plugin_methods as $method)
    	        {
    	            echo '<tr class="' . $color . '">
    	            ';
    	            
    	            echo '<td>' . $plugin_ctrler_name . '->' . $method['name'] . '</td>';
    	            //debug($method['name']);
    	            
    	            echo '<td>';
    	            echo '<span id="right_' . $plugin_name . '_' . $user[$user_pk_name] . '_' . $plugin_ctrler_name . '_' . $method['name'] . '">';
    	            
		    		if($method['permissions'][$user[$user_pk_name]] == 1)
		    		{
		    		    // $this->Js->buffer('register_user_toggle_right(true, "' . $this->Url->build('/') . '", "right_' . $plugin_name . '_' . $user[$user_pk_name] . '_' . $plugin_ctrler_name . '_' . $method['name'] . '", "' . $user[$user_pk_name] . '", "' . $plugin_name . '", "' . $plugin_ctrler_name . '", "' . $method['name'] . '")');
		    		    $this->Html->scriptBlock('register_user_toggle_right(true, "' . $this->Url->build('/') . '", "right_' . $plugin_name . '_' . $user[$user_pk_name] . '_' . $plugin_ctrler_name . '_' . $method['name'] . '", "' . $user[$user_pk_name] . '", "' . $plugin_name . '", "' . $plugin_ctrler_name . '", "' . $method['name'] . '")', ['block' => true]);
		    		    
		    			echo $this->Html->image('/AclEdit/img/design/tick.png', array('class' => 'pointer'));
		    		}
		    		elseif($method['permissions'][$user[$user_pk_name]] == 0)
		    		{
		    		    // $this->Js->buffer('register_user_toggle_right(false, "' . $this->Url->build('/') . '", "right_' . $plugin_name . '_' . $user[$user_pk_name] . '_' . $plugin_ctrler_name . '_' . $method['name'] . '", "' . $user[$user_pk_name] . '", "' . $plugin_name . '", "' . $plugin_ctrler_name . '", "' . $method['name'] . '")');
		    		    $this->Html->scriptBlock('register_user_toggle_right(false, "' . $this->Url->build('/') . '", "right_' . $plugin_name . '_' . $user[$user_pk_name] . '_' . $plugin_ctrler_name . '_' . $method['name'] . '", "' . $user[$user_pk_name] . '", "' . $plugin_name . '", "' . $plugin_ctrler_name . '", "' . $method['name'] . '")', ['block' => true]);
		    			
		    		    echo $this->Html->image('/AclEdit/img/design/cross.png', array('class' => 'pointer'));
		    		}
		    		elseif($method['permissions'][$user[$user_pk_name]] == -1)
            		{
            		    echo $this->Html->image('/AclEdit/img/design/important16.png');
            		}
            		else
            		{
            		    echo '?';
            		}
            		
    		    	echo '</span>';
    		    	
    		    	echo ' ';
    		    	echo $this->Html->image('/AclEdit/img/ajax/waiting16.gif', array('id' => 'right_' . $plugin_name . '_' . $user[$user_pk_name] . '_' . $plugin_ctrler_name . '_' . $method['name'] . '_spinner', 'style' => 'display:none;'));
    		    	
        		    echo '</td>';
    	            echo '</tr>
    	            ';
    	        }
    	    }
    	}
	}
    ?>
	</table>
    <?php
    echo $this->Html->image('/AclEdit/img/design/tick.png') . ' ' . __d('acl', 'authorized');
    echo '&nbsp;&nbsp;&nbsp;';
    echo $this->Html->image('/AclEdit/img/design/cross.png') . ' ' . __d('acl', 'blocked');
    ?>
<?php
}
?>
<?php
echo $this->element('design/footer');
?>