<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="tournaments index">
	<h2><?php echo __('Tournaments');?></h2>
	<table>
	<tr>
			<th><?php echo $this->Paginator->sort('description', __('Description'));?></th>
			<th><?php echo $this->Paginator->sort('location', __('Location'));?></th>
			<th><?php echo $this->Paginator->sort('start_on', __('Date'));?></th>
			<th><?php echo $this->Paginator->sort('enter_before', __('Enter before'));?></th>
			<th><?php echo $this->Paginator->sort('modify_before', __('Modify before'));?></th>
			<th><?php echo $this->Paginator->sort('modified', __('Updated'));?></th>
			<?php if ($hasRootPrivileges) { ?>
				<th class="actions"><?php echo __('Actions');?></th>
			<?php } ?>
	</tr>
	<?php
	$mayView = $Acl->check($current_user, 'Tournaments/view');
	$mayEdit = $Acl->check($current_user, 'Tournaments/edit');
	$mayDelete = $Acl->check($current_user, 'Tournaments/delete');

	$i = 0;
	foreach ($tournaments as $tournament):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $this->Html->link(
				$tournament['description'], 
				array('controller' => '/registrations', 'tournament_id' => $tournament['id'])
			); ?>&nbsp;</td>
		<td><?php echo $tournament['location']; ?>&nbsp;</td>
		<td>
			<?php
				$start = strtotime($tournament['start_on']);
				$end = strtotime($tournament['end_on']);
				if (date('m', $start) == date('m', $end)) {
					if (date('d', $start) == date('d', $end))
						echo str_replace(' ', '&nbsp;', date('j M Y', $start));
					else
						echo str_replace(' ', '&nbsp;', date('j', $start) . ' - ' . date('j', $end) . ' ' . date('M Y', $start));
				} else if (date('Y', $start) == date('Y', $end)) {
					echo str_replace(' ', '&nbsp;', date('j M', $start) . ' - ' . date('j M Y', $end));
				} else {
					echo str_replace(' ', '&nbsp;', date('j M Y', $start) . ' - ' . date('j M Y', $end));
				}
			?>
		</td>
		<td><?php echo $tournament['enter_before']; ?>&nbsp;</td>
		<td><?php echo $tournament['modify_before']; ?>&nbsp;</td>
		<td><?php echo $tournament['modified']; ?>&nbsp;</td>
		<td class="actions">
			<?php if ($mayView) echo $this->Html->link(__('View'), array('action' => 'view', $tournament['id'])); ?>
			<?php if ($mayEdit) echo $this->Html->link(__('Edit'), array('action' => 'edit', $tournament['id'])); ?>
			<?php if ($mayDelete) echo $this->Html->link(__('Delete'), array('action' => 'delete', $tournament['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $tournament['description'])]); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<?php 
		if (!$hasRootPrivileges) {
			if ($all)
				echo $this->Html->link(__('Hide past tournaments'), array('all' => false));
			else
			echo $this->Html->link(__('Show past tournaments'), array('all' => true)); 
		}
	?>
	<?php echo $this->element('paginator'); ?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php if ($Acl->check($current_user, 'Tournaments/add')) 
			echo '<li>' . $this->Html->link(__('New Tournament'), array('action' => 'add')) . '</li>'; 
		?>
	</ul>
<?php $this->end(); ?>
