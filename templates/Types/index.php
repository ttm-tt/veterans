<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="types index">
	<h2><?php echo __('Functions');?></h2>
	<table>
	<tr>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th><?php echo $this->Paginator->sort('description');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$mayView = $Acl->check($current_user, 'Types/view');
	$mayEdit = $Acl->check($current_user, 'Types/edit');
	$mayDelete = $Acl->check($current_user, 'Types/delete');

	$i = 0;
	foreach ($types as $type):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $type['name']; ?>&nbsp;</td>
		<td><?php echo $type['description']; ?>&nbsp;</td>
		<td class="actions">
			<?php if ($mayView) echo $this->Html->link(__('View'), array('action' => 'view', $type['id'])); ?>
			<?php if ($mayEdit) echo $this->Html->link(__('Edit'), array('action' => 'edit', $type['id'])); ?>
			<?php if ($mayDelete) echo $this->Html->link(__('Delete'), array('action' => 'delete', $type['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $type['description'])]); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<?php echo $this->element('paginator'); ?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php
			if ($Acl->check($current_user, 'Types/add'))
				echo '<li>' . $this->Html->link(__('New Function'), array('action' => 'add')) . '</li>'; 
		?>
	</ul>
<?php $this->end(); ?>
