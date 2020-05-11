<?php
echo $this->element('design/header');
?>

<?php
echo $this->element('Aros/links');
?>

<?php
echo $this->Form->create('User', array('url' => array('plugin' => 'AclEdit', 'controller' => 'aros', 'action' => 'admin_users')));
echo __d('acl', 'name');
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
	
	$headers = array($this->Paginator->sort($user_display_field, __d('acl', 'name')));
	
	foreach($roles as $role)
	{
	    $headers[] = $role[$role_display_field];
	    $column_count++;
	}
	
	echo $this->Html->tableHeaders($headers);
	
	?>
	
</tr>
<?php
foreach($users as $user)
{
    $style = isset($user['Aro']) ? '' : ' class="line_warning"';
    
    echo '<tr' . $style . '>';
    echo '  <td>' . $user[$user_display_field] . '</td>';
    
    foreach($roles as $role)
	{
	   if(isset($user['Aro']) && $role[$role_pk_name] == $user[$role_fk_name])
	   {
	       echo '  <td>' . $this->Html->image('/AclEdit/img/design/tick.png') . '</td>';
	   }
	   else
	   {
	   	   $title = __d('acl', 'Update the user role');
	       echo '  <td>' . $this->Html->link($this->Html->image('/AclEdit/img/design/tick_disabled.png'), '/AclEdit/aros/admin_update_user_role/user:' . $user[$user_pk_name] . '/role:' . $role[$role_pk_name], array('title' => $title, 'alt' => $title, 'escape' => false)) . '</td>';
	   }
	}
	
    //echo '  <td>' . (isset($user['Aro']) ? $this->Html->image('/AclEdit/img/design/tick.png') : $this->Html->image('/AclEdit/img/design/cross.png')) . '</td>';
    
    echo '</tr>';
}
?>
</table>

<?php
if($missing_aro)
{
?>
    <div style="margin-top:20px">
    
    <p class="warning"><?php echo __d('acl', 'Some users AROS are missing. Click on a role to assign one to a user.') ?></p>
    
    </div>
<?php
}
?>

<?php
	echo $this->element('paginator');
?>

<?php
echo $this->element('design/footer');
?>