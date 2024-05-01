<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="registrations index">
	<h2><?php echo __d('user', 'Partner Requests for {0}', $me['person']['display_name']);?></h2>
	<table>
	<tr>
		<?php
			echo '<th>' . __d('user', 'Name') . '</th>';
			echo '<th>' . __d('user', 'Association') . '</th>';
			echo '<th>' . __d('user', 'Reg. ID') . '</th>';
			if ($count['S'] > 0)
				echo '<th>' . __d('user', 'Single') . '</th>';
			if ($count['D'] > 0)
				echo '<th>' . __d('user', 'Double') . '</th>';
			if ($count['X'] > 0)
				echo '<th>' . __d('user', 'Mixed') . '</th>';
		?>
		<th class="actions" colspan="3"><?php echo __d('user', 'Actions');?></th>
	</tr>
	<?php
	$i = 0;

	foreach ($registrations as $registration):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}

		$participant = $registration['participant'];

	?>
	<tr<?php echo $class;?>>
		<?php 
			echo '<td>' . $registration['person']['display_name'] . '</td>';
			echo '<td>' . $nations[$registration['person']['nation_id']] . '</td>';
			echo '<td>' . $registration['person']['extern_id'] . '</td>';
		?>
		<?php if ($count['S'] > 0) { ?>
			<td>
				<?php 
					if (!empty($participant['single_id']))
						echo $competitions[$participant['single_id']];
				?>
			</td>
		<?php } ?>
		<?php if ($count['D'] > 0) { ?>
			<td>
				<?php 
					if (!empty($participant['double_id']))
						echo $competitions[$participant['double_id']];
				?>
			</td>
		<?php } ?>
		<?php if ($count['X'] > 0) { ?>
			<td>
				<?php 
					if (!empty($participant['mixed_id']))
						echo $competitions[$participant['mixed_id']];
				?>
			</td>
		<?php } ?>

		<td class="actions">
			<?php
				echo $this->Html->link(__d('user', 'Accept'), array('action' => 'accept', $me['id'], $registration['id']));
				echo $this->Html->link(
					__d('user', 'Reject'), 
					array('action' => 'reject', $me['id'], $registration['id']),
					['confirm' => __d('user', 'Are you sure you want to reject the partner request from {0}?', $registration['person']['display_name'])]
				);
				
				if ($hasRootPrivileges)
					echo $this->Html->link(__d('user', 'View'), ['action' => 'view', $registration->id]);
			?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php 
			if ($Acl->check($current_user, 'Registrations/index'))
				echo '<li>' . $this->Html->link(__d('user', 'List Registrations'), array('action' => 'index')) . '</li>';
			if ($Acl->check($current_user, 'Registrations/list_partner_wanted'))
				echo '<li>' . $this->Html->link(__d('user', 'List Partner Wanted'), array('action' => 'list_partner_wanted')) . '</li>';
		?>
	</ul>
<?php $this->end(); ?>
