<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="users index">
	<h2><?php echo __('Users');?></h2>
	<?php
		echo '<div class="filter">';
		echo '<fieldset>';
		echo '<legend>' . __d('user', 'Filter') . '</legend>';
		echo '<table>';

		echo $this->element('filter', [
			'label'=> __('Groups'),
			'id' => 'group_id',
			'options' => $groups
		]);

		echo '<tr><td><label class="filter">' . __('Username') .	'</td><td>';

		if (isset($username))
			echo $this->Html->link(__('all'), ['?' => ['username' => 'all']]);
		else
			echo __('all');

		foreach ($allchars as $char) {
			if (isset($username) && $username == $char)
				echo ' ' . $char;
			else
				echo ' ' . $this->Html->link($char, ['?' => ['username' => $char]]);
		}	

		echo '</td></tr>';
		echo '</table>' . "\n";
		echo '</fieldset></div>' . "\n";
	?>
	<table>
	<tr>
			<th><?php echo $this->Paginator->sort('username');?></th>
			<th><?php echo $this->Paginator->sort('group_id');?></th>
			<th><?php echo $this->Paginator->sort('email');?></th>
			<th><?php echo $this->Paginator->sort('prefix_people', __('Prefix'));?></th>
			<th><?php echo $this->Paginator->sort('last_login');?></th>
			<th><?php echo $this->Paginator->sort('count_successful', __('Successful'));?></th>
			<th><?php echo $this->Paginator->sort('count_failed', __('Failed'));?></th>
			<th><?php echo $this->Paginator->sort('count_failed_since', __('Since'));?></th>
			<th><?php echo $this->Paginator->sort('count_requests', __('Requests'));?></th>
			<th><?php echo $this->Paginator->sort('modified', __('Updated'));?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$mayView = $Acl->check($current_user, 'Users/view');
	$mayEdit = $Acl->check($current_user, 'Users/edit');
	$mayDelete = $Acl->check($current_user, 'Users/delete');

	$i = 0;
	foreach ($users as $user):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $user['username']; ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($user['group']['name'], array('controller' => 'groups', 'action' => 'view', $user['Group']['id'])); ?>
		</td>
		<td><?php echo $user['email']; ?>&nbsp;</td>
		<td><?php echo $user['prefix_people']; ?>&nbsp;</td>
		<td><?php echo $user['last_login']; ?>&nbsp;</td>
		<td><?php echo $user['count_successful']; ?>&nbsp;</td>
		<td><?php echo $user['count_failed']; ?>&nbsp;</td>
		<td><?php echo $user['count_failed_since']; ?>&nbsp;</td>
		<td><?php echo $user['count_requests']; ?>&nbsp;</td>
		<td><?php echo $user['modified']; ?>&nbsp;</td>
		<td class="actions">
			<?php if ($mayView) echo $this->Html->link(__('View'), array('action' => 'view', $user['id'])); ?>
			<?php if ($mayEdit) echo $this->Html->link(__('Edit'), array('action' => 'edit', $user['id'])); ?>
			<?php if ($mayDelete) echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $user->id), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $user->username)]); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<?php echo $this->element('paginator'); ?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php if ($Acl->check($current_user, 'Users/add')) echo $this->Html->link(__('New User'), array('action' => 'add')); ?></li>
		<li><?php if ($Acl->check($current_user, 'Groups/index')) echo $this->Html->link(__('List Groups'), array('controller' => 'groups', 'action' => 'index')); ?> </li>
	</ul>
<?php $this->end(); ?>
