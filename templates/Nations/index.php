<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="nations index">
	<h2><?php echo __('Associations');?></h2>
	<table>
	<tr>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th><?php echo $this->Paginator->sort('description');?></th>
			<th><?php echo $this->Paginator->sort('continent');?></th>
			<th><?php echo $this->Paginator->sort('modified', __('Updated'));?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$mayView = $Acl->check($current_user, 'Nations/view');
	$mayEdit = $Acl->check($current_user, 'Nations/edit');
	$mayDelete = $Acl->check($current_user, 'Nations/delete');

	$i = 0;

	foreach ($nations as $nation):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $nation['name']; ?>&nbsp;</td>
		<td><?php echo $nation['description']; ?>&nbsp;</td>
		<td><?php echo $nation['continent']; ?>&nbsp;</td>
		<td><?php echo $nation['modified']; ?>&nbsp;</td>
		<td class="actions">
			<?php if ($mayView) echo $this->Html->link(__('View'), array('action' => 'view', $nation['id'])); ?>
			<?php if ($mayEdit) echo $this->Html->link(__('Edit'), array('action' => 'edit', $nation['id'])); ?>
			<?php if ($mayDelete) echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $nation['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $nation['description'])]); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<?php echo $this->element('paginator'); ?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php
			if ($Acl->check($current_user, 'Nations/add'))
				echo '<li>' . $this->Html->link(__('New Association'), array('action' => 'add')) . '</li>'; 
		?>
	</ul>
<?php $this->end(); ?>
