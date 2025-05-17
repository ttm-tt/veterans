<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="users view">
<h2><?php echo __('User');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Username'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $user['username']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Group'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $user['group']['name']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Email'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $user['email']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Add. Email'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo implode('<br>', explode("\n", $user['add_email'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Newsletter'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $user['newsletter']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Enabled'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $user['enabled']; ?>
			&nbsp;
		</dd>
		<?php if (!empty($user['nation']['description'])) { ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Association'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $user['nation']['description']; ?>
			&nbsp;
		</dd>
		<?php } ?>
		<?php if (!empty($user['tournament']['description'])) { ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Tournament'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $user['tournament']['description']; ?>
			&nbsp;
		</dd>
		<?php } ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Last Login'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $user['last_login']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Failed since last'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $user['count_failed_since']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Successful Logins'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $user['count_successful']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Failed Logins'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $user['count_failed']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $user['modified']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Created'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $user['created']; ?>
			&nbsp;
		</dd>
	</dl>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php if ($Acl->check($current_user, 'Users/send_welcome_mail')) echo '<li>' . $this->Html->link(__('Send Password'), array('action' => 'send_welcome_mail', $user['id'], true)) . '</li>'; ?>
		<?php if ($Acl->check($current_user, 'Users/edit')) echo '<li>' . $this->Html->link(__('Edit User'), array('action' => 'edit', $user['id'])) . '</li>'; ?>
		<?php if ($Acl->check($current_user, 'Users/notifications')) echo '<li>' . $this->Html->link(__('Edit Notifications'), array('action' => 'notifications', $user['id'])) . '</li>'; ?>
		<?php if ($Acl->check($current_user, 'Users/delete')) echo '<li>' . $this->Html->link(__('Delete User'), array('action' => 'delete', $user['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $user['username'])]) . '</li>'; ?> 
		<?php if ($Acl->check($current_user, 'Users/index')) echo '<li>' . $this->Html->link(__('List Users'), array('action' => 'index')) . '</li>'; ?> 
		<?php if ($Acl->check($current_user, 'Users/add')) echo '<li>' . $this->Html->link(__('New User'), array('action' => 'add')) . '</li>'; ?> 
		<?php if ($Acl->check($current_user, 'Groups/index')) echo '<li>' . $this->Html->link(__('List Groups'), array('controller' => 'groups', 'action' => 'index')) . '</li>'; ?> 
		<?php if ($Acl->check($current_user, 'People/index')) echo '<li>' . $this->Html->link(__('List People'), array('controller' => 'people', 'action' => 'index',  '?' => ['user_id' => $user['id']])) . '</li>'; ?>
		<?php if ($Acl->check($current_user, 'Registrations/index')) echo '<li>' . $this->Html->link(__('List Registrations'), array('controller' => 'registrations', 'action' => 'index', '?' => ['user_id' => $user['id']])) . '</li>'; ?>
	</ul>
<?php $this->end(); ?>
