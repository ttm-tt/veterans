<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="groups index">
	<h2><?php echo __('Groups');?></h2>
	<table>
	<tr>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th><?php echo __('Functions');?></th>
			<th><?php echo $this->Paginator->sort('modified', __('Updated'));?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$mayView = $Acl->check($current_user, 'Groups/view');
	$mayEdit = $Acl->check($current_user, 'Groups/edit');
	$mayDelete = $Acl->check($current_user, 'Groups/delete');

	$i = 0;
	foreach ($groups as $group):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $group['name']; ?>&nbsp;</td>
		<td><?php echo $group['typenames']; ?>&nbsp;</td>
		<td><?php echo $group['modified']; ?>&nbsp;</td>
		<td class="actions">
			<?php if ($mayView) echo $this->Html->link(__('View'), array('action' => 'view', $group['id'])); ?>
			<?php if ($mayEdit) echo $this->Html->link(__('Edit'), array('action' => 'edit', $group['id'])); ?>
			<?php if ($mayDelete) echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $group['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $group['name'])]); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<?php echo $this->element('paginator'); ?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php 
			if ($Acl->check($current_user, 'Groups/add')) 
				echo '<li>' . $this->Html->link(__('New Group'), array('action' => 'add')) . '</li>'; 
			if ($Acl->check($current_user, 'Users/index')) 
				echo '<li>' . $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')) . '</li>'; 
		?>
	</ul>
<?php $this->end(); ?>
