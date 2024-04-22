<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use App\Model\Table\TypesTable;
use App\Model\Table\RegistrationsTable;
?>

<?php $participant = $registration['participant']; ?>
<div class="registrations view">
<h2>
<?php  
	echo __('Registration of') . ' ' . $registration['person']['display_name'];
	if (!empty($revision)) {
		echo ' (' .  $revision . ')';
	}
?>
</h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Person'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php 
				if ($hasRootPrivileges || (
						$Acl->check($current_user, 'People/view') &&
						(empty($current_user['nation_id']) || $current_user['nation_id'] == $registration['person']['nation_id']) &&
						(empty($registration['person']['user_id']) || $current_user['id'] == $registration['person']['user_id']) 
				) )
					echo $this->Html->link($registration['person']['display_name'], array('controller' => 'people', 'action' => 'view', $registration['person']['id'])); 
				else
					echo $registration['person']['display_name'];
			?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Function'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['type']['description']; ?>
			&nbsp;
		</dd>
		<?php // Hide if this is not a player ?>
		<?php if ($registration['type']['id'] == TypesTable::getPlayerId()) { ?>
			<?php if (!empty($registration['ReplacedBy'])) { ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Replaced By'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>	
					<?php
						$replaced_by = $registration['ReplacedBy']['person']['display_name'];
						if ($registration['Pprson']['nation_id'] != $registration['ReplacedBy']['person']['nation_id'])
							$replaced_by .= ' (' . $registration['ReplacedBy']['person']['nation']['name'] . ')';

						echo $this->Html->link($replaced_by, array('action' => 'view', $registration['participant']['replaced_by_id']));
					?>
					&nbsp;
				</dd>
			<?php } ?>
			<?php if ($hasRootPrivileges) { ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Start No.'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php 
						if ($registration['cancelled'])
							echo '<del>';
						echo $participant['start_no']; 
						if ($registration['cancelled'])
							echo '</del>';
					?>
					&nbsp;
				</dd>
			<?php } ?>
			<?php // Hide rows if there is no such competition type (e.g. mixed in EC) ?>
			<?php if ($count['S'] > 0) { ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Single'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php 
						if ($participant['single_cancelled'])
							echo '<del>';
						if (!empty($participant['single']))
							echo $participant['single']['description']; 
						if ($participant['single_cancelled'])
							echo '</del>';
					?>
					&nbsp;
				</dd>
				<?php if ($registration->person->ptt_class > 0) { ?>
					<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Able-bodied'); ?></dt>
					<dd<?php if ($i++ % 2 == 0) echo $class;?>>
						<?php 
							if ($participant->ptt_nonpara)
								echo __('Yes');
							else
								echo __('No');
						?>
						&nbsp;						
				<?php } ?>
			<?php } ?>
			<?php if ($count['D'] > 0) { ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Double'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php 
						if ($participant['double_cancelled'])
							echo '<del>';
						if (!empty($participant['double']))
							echo $participant['double']['description']; 
						if ($participant['double_cancelled'])
							echo '</del>';
					?>
					&nbsp;
				</dd>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Double Partner'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php 
						if ($participant['double_cancelled'])
							echo '<del>';

						if (empty($participant['double_id'])) {
						} else if (empty($participant['double_partner_id'])) {
							echo '<em>' . __('Partner wanted') . '</em>';
						} else {
							$double_partner_name = $participant['double_partner']['person']['display_name'];
							if ($registration['person']['nation_id'] != $participant['double_partner']['person']['nation_id'])
								$double_partner_name .= ' (' . $participant['double_partner']['person']['nation']['name'] . ')';

							if (!RegistrationsTable::isDoublePartnerConfirmed($registration))
								$double_partner_name = '<em>' . $double_partner_name . ' (' . __('wanted') . ')' . '</em>';
							
							if ($hasRootPrivileges)
								echo $this->Html->link($double_partner_name, array('action' => 'view', $participant['double_partner_id']));
							else
								echo $double_partner_name;
						}

						if ($participant['double_cancelled'])
							echo '</del>';
					?>
					&nbsp;
				</dd>
			<?php } ?>
			<?php if ($count['X'] > 0) { ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Mixed'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php 
						if ($participant['mixed_cancelled'])
							echo '<del>';
						if (!empty($participant['mixed']))
							echo $participant['mixed']['description']; 
						if ($participant['mixed_cancelled'])
							echo '</del>';
					?>
					&nbsp;
				</dd>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Mixed Partner'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php 
						if ($participant['mixed_cancelled'])
							echo '<del>';

						if (empty($participant['mixed_id'])) {
						} else if (empty($participant['mixed_partner_id'])) {
							echo '<em>' . __('Partner wanted') . '</em>';
						} else {
							$mixed_partner_name = $participant['mixed_partner']['person']['display_name'];
							if ($registration['person']['nation_id'] != $participant['mixed_partner']['person']['nation_id'])
								$mixed_partner_name .= ' (' . $participant['mixed_partner']['person']['nation']['name'] . ')';

							if (!RegistrationsTable::isMixedPartnerConfirmed($registration))
								$mixed_partner_name = '<em>' . $mixed_partner_name . ' (' . __('wanted') . ')' . '</em>';
							
							if ($hasRootPrivileges)
								echo $this->Html->link($mixed_partner_name, array('action' => 'view', $participant['mixed_partner_id']));
							else
								echo $mixed_partner_name;
						}

						if ($participant['mixed_cancelled'])
							echo '</del>';
					?>
					&nbsp;
				</dd>
			<?php } ?>
			<?php if ($count['T'] > 0) { ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Team'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php 
						if ($participant['team_cancelled'])
							echo '<del>' . $participant['team']['description'] . '</del>';
						else if (!empty($participant['team']))
							echo $participant['team']['description']; 
					?>
					&nbsp;
				</dd>
			<?php } ?>
		<?php } ?>
<?php if (empty($revision)) { ?>
		<?php if (!empty($registration['cancelled'])) { ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Cancelled'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $registration['cancelled']; ?>
				&nbsp;
			</dd>
		<?php } ?>
		<?php if ($hasRootPrivileges && !empty($registration['comment'])) { ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Comment'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $registration['comment']; ?>
				&nbsp;
			</dd>
		<?php } ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Updated At'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['modified']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Created At'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['created']; ?>
			&nbsp;
		</dd>
<?php } ?>
	</dl>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php // Show "List Registrations" action only if an entry can't be changed ?>
		<?php 
			if ($hasRootPrivileges || 
			    $registration['tournament']['enter_before'] >= date('Y-m-d') || 
			    $registration['tournament']['modify_before'] >= date('Y-m-d') && $registration['type_id'] != TypesTable::getPlayerId()) {
		
				if ($hasRootPrivileges && $registration['type_id'] == TypesTable::getPlayerId())
					echo '<li>' . $this->Html->link(__('View History'), array('action' => 'history', $registration['id'])) . '</li>';	
				if ($Acl->check($current_user, 'Registrations/edit_participant'))
					echo '<li>' . $this->Html->link(__('Edit Registration'), array('action' => 'edit_participant', $registration['id'])) . '</li>';
				else if ($Acl->check($current_user, 'Registrations/edit'))
					echo '<li>' . $this->Html->link(__('Edit Registration'), array('action' => 'edit', $registration['id'])) . '</li>';
				if ($Acl->check($current_user, 'Registrations/delete'))
					echo '<li>' . $this->Form->postLink(__('Delete Registration'), array('action' => 'delete', $registration['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $registration['person']['display_name'])]) . '</li>';
				if ($Acl->check($current_user, 'Registrations/index'))
					echo '<li>' . $this->Html->link(__('List Registrations'), array('action' => 'index')) . '</li>';
				if ($Acl->check($current_user, 'Registrations/list_partner_wanted'))
					'<li>' . $this->Html->link(__('List Partner Wanted'), array('action' => 'list_partner_wanted')) . '</li>';
				if ($Acl->check($current_user, 'Registrations/add'))
					echo '<li>' . $this->Html->link(__('New Registration'), array('action' => 'add')) . '</li>';
			} else {
				if ($Acl->check($current_user, 'Registrations/index'))
					echo '<li>' . $this->Html->link(__('List Registrations'), array('action' => 'index')) . '</li>';
				if ($Acl->check($current_user, 'Registrations/list_partner_wanted'))
					'<li>' . $this->Html->link(__('List Partner Wanted'), array('action' => 'list_partner_wanted')) . '</li>';
			}
		?>
	</ul>
<?php $this->end(); ?>
