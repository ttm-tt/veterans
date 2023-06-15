<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="competitions index">
	<h2><?php echo __('Competitions');?></h2>
	<?php
		echo '<div class="filter">';
		echo '<fieldset>';
		echo '<legend>' . __d('user', 'Filter') . '</legend>';
		echo '<table>';
		
		echo $this->element('filter', [
			'label' => __('Sex'),
			'id' => 'cp_sex',
			'options' => [
				'F' => __('Women'),
				'M' => __('Men'),
				'X' => __('Mixed')
			]
		]);
		
		echo $this->element('filter', [
			'label' => __('Type'),
			'id' => 'type_of',
			'options' => [
				'S' => __('Singles'),
				'D' => __('Doubles'),
				'X' => __('Mixed'),
				'T' => __('Teams')
			]
		]);

		echo '</table>' . "\n";
		echo '</fieldset></div>' . "\n";
	?>

	<table>
	<tr>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th><?php echo $this->Paginator->sort('description');?></th>
			<th><?php echo $this->Paginator->sort('category');?></th>
			<th><?php echo $this->Paginator->sort('sex');?></th>
			<th><?php echo $this->Paginator->sort('type_of', __('Type'));?></th>
			<th><?php echo $this->Paginator->sort('born');?></th>
			<th><?php echo $this->Paginator->sort('modified', __('Updated'));?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$mayView = $Acl->check($current_user, 'Competitions/view');
	$mayEdit = $Acl->check($current_user, 'Competitions/edit');
	$mayDelete = $Acl->check($current_user, 'Competitions/delete');

	$i = 0;
	foreach ($competitions as $competition):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $competition['name']; ?>&nbsp;</td>
		<td><?php echo $competition['description']; ?>&nbsp;</td>
		<td><?php echo $competition['category']; ?>&nbsp;</td>
		<td><?php echo $competition['sex']; ?>&nbsp;</td>
		<td><?php echo $competition['type_of']; ?>&nbsp;</td>
		<td><?php echo $competition['born']; ?>&nbsp;</td>
		<td><?php echo $competition['modified']; ?>&nbsp;</td>
		<td class="actions">
			<?php if ($mayView) echo $this->Html->link(__('View'), array('action' => 'view', $competition['id'])); ?>
			<?php if ($mayEdit) echo $this->Html->link(__('Edit'), array('action' => 'edit', $competition['id'])); ?>
			<?php if ($mayDelete) echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $competition['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $competition['description'])]); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<?php echo $this->element('paginator'); ?>
</div>

<?php $this->start('action'); ?>
	<ul> 
		<?php 
			if ($Acl->check($current_user, 'Competitions/add'))
				echo '<li>' . $this->Html->link(__('New Competition'), array('action' => 'add')) . '</li>'; 
		?>
	</ul>
<?php $this->end(); ?>
