<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	use App\Model\Table\GroupsTable;
	use App\Model\Table\TypesTable;
	use App\Model\Table\RegistrationsTable;
?>

<?php
	$isPartnerWanted = $this->request->getParam('action') == 'list_partner_wanted';
	
	$isParticipant = $current_user['group_id'] == GroupsTable::getParticipantId();
	$isTourOperator = $current_user['group_id'] == GroupsTable::getTourOperatorId();

	$isFiltered = 
		empty($current_user['nation_id']) && !empty($nation_id) ||
		count($types) > 1 && !empty($type_id) ||
		isset($cancelled) ||
		!empty($competition_id) ||
		!empty($last_name) ||
		!empty($user_id)
	;

	$isPartnerWanted = $this->request->getParam('action') == 'list_partner_wanted';
?>


<div class="registrations index">
	<h2><?php echo ($isPartnerWanted ? __d('user', 'Partner Wanted') : __d('user', 'Registrations'));?></h2>
	<?php
		$params = $this->Paginator->params();
		if ($Acl->check($current_user, 'Registrations/add_participant') || $params['pageCount'] > 1 || $isFiltered) {
			echo '<div class="filter">';
			echo '<fieldset>';
			echo '<legend>' . __d('user', 'Filter') . '</legend>';
			echo '<table>';

			if (empty($current_user['nation_id'])) {
				echo $this->element('filter', [
					'label'=> __d('user', 'Association'),
					'id' => 'nation_id',
					'options' => $nations
				]);
			}

			if (count($types) > 1) {
				echo $this->element('filter', [
					'label'=> $isParticipant || $isTourOperator ? __d('user', 'Type') :  __('Function'),
					'id' => 'type_id',
					'options' => $types
				]);
			}

			echo $this->element('filter', [
				'label'=> __d('user', 'Competition'),
				'id' => 'competition_id',
				'options' => $competitions
			]);

			if (!$isPartnerWanted) {
				echo $this->element('filter', [
					'label'=> __d('user', 'Age Category'),
					'id' => 'age_category',
					'options' => [
						'different' => __d('user', 'Different'),
						'missing' => __d('user', 'Not in all events')
					]
				]);
			}

			if (!$isPartnerWanted) {
				echo $this->element('filter', [
					'label'=> __d('user', 'Partner'),
					'id' => 'partner',
					'options' => [
						'wanted' => __d('user', 'Wanted'),
						'requested' => __d('user', 'Requested As'),
						'multiple' => __d('user', 'Multiple'),
						'unconfirmed' => __d('user', 'Not Confirmed'),
						'confirmed' => __d('user', 'Confirmed')
					]
				]);
			}
			
			if (!$isPartnerWanted) {
				echo $this->element('filter', [
					'label'=> __d('user', 'Status'),
					'id' => 'cancelled',
					'options' => [
						0 => __d('user', 'Not cancelled'),
						1 => __d('user', 'Cancelled')
					]
				]);
			}

			echo '<tr><td><label class="filter">' . __d('user', 'Family Name') .	'</td><td>';

			foreach ($allchars as $idx => $chars) {
				if (count($chars) == 0)
					continue;

				if ($idx > 0)
					echo '<br>';

				if ($idx == 0) {
					if (isset($last_name))
						echo $this->Html->link(__('all'), ['?' => ['last_name' => '*']]);
					else
						echo __('all');
				} else {
					$name = str_replace(' ', '_', mb_convert_case(mb_strtolower(mb_substr($chars[0], 0, mb_strlen($chars[0]) - 1)), MB_CASE_TITLE));

					if (mb_strlen($last_name) >= mb_strlen($chars[0]))
						echo $this->Html->link($name, ['?' => ['last_name' => urlencode(str_replace(' ', '_', mb_substr($chars[0], 0, mb_strlen($chars[0]) - 1)))]]);
					else
						echo $name;
				}

				foreach ($chars as $char) {
					$name = str_replace(' ', '_', mb_convert_case(mb_strtolower($char), MB_CASE_TITLE));

					if (mb_substr($last_name, 0, mb_strlen($char)) == $char)
						echo ' ' . $name;
					else
						echo ' ' . $this->Html->link($name, ['?' => ['last_name' => urlencode(str_replace(' ', '_', $char))]]);
				}
			}

			echo '</td></tr>';
			
			echo '<tr/>';

			if (!empty($user_id)) {
				echo '<tr><td><label class="filter">' . __d('user', 'Username') . '</td><td>';
				echo $this->Html->link(__d('user', 'all'), ['?' => ['user_id' => 'all']]);
				echo ' ' . $username;

				echo '</td></tr>';
				
				echo '<tr/>';
			}

			echo '</table>' . "\n";
			echo '</fieldset></div>' . "\n";
		}
	?>
	<table class="ttm-table">
	<?php
		$wantAssociation = 
			$hasRootPrivileges || $isPartnerWanted ||
			$current_user['User']['group_id'] == GroupsTable::getOrganizerId() ||
			empty($current_user['User']['nation_id'])
		;

		$wantSex = 
			$isPartnerWanted && !empty($type_of) && $type_of == 'X' && $count['X'] > 0;
		
		$wantCompetitions =
			(!$this->request->getSession()->check('Types.id') || TypesTable::getPlayerId() == $this->request->getSession()->read('Types.id') || $isPartnerWanted) &&
			(!$this->request->getSession()->check('Groups.type_ids') || in_array(TypesTable::getPlayerId(), explode(',', $this->request->getSession()->read('Groups.type_ids'))))
		;

		$allowPersonView =
			$Acl->check($current_user, 'People/view')
		;
		
		$allowPersonEdit =
			$Acl->check($current_user, 'People/edit')
		;

		$allowView = 
			$Acl->check($current_user, 'Registrations/view')
		;

		$allowHistory =
			$Acl->check($current_user, 'Registrations/history')
		;

		$allowEdit =
			$Acl->check($current_user, 'Registrations/edit') &&
			($hasRootPrivileges || $tournament['modify_before'] >= date('Y-m-d')) &&
			($hasRootPrivileges || $tournament['enter_after'] <= date('Y-m-d'))
		;
		
		$allowEditParticipant = 
			$Acl->check($current_user, 'Registrations/edit_participant') &&
			($hasRootPrivileges || $tournament['modify_before'] >= date('Y-m-d')) &&
			($hasRootPrivileges || $tournament['enter_after'] <= date('Y-m-d'))
		;

		$allowDelete =
			$Acl->check($current_user, 'Registrations/delete') &&
			($hasRootPrivileges || $tournament['modify_before'] >= date('Y-m-d'))
		;
		
		$allowDeleteParticipant =
			$Acl->check($current_user, 'Registrations/delete_participant') &&
			($hasRootPrivileges || $tournament['modify_before'] >= date('Y-m-d'))
		;
		
		$allowRequests =
			$this->request->getParam('action') == 'index' && 
			$Acl->check($current_user, 'Registrations/requests') &&
			($hasRootPrivileges || $tournament['modify_before'] >= date('Y-m-d'))
		;
	?>
	<thead><tr>
			<th><?php echo $this->Paginator->sort('People.display_name', __d('user', 'Name'));?></th>
			<?php
				if ($wantSex)
					echo '<th class="ttm-table-col">' . $this->Paginator->sort('People.sex', __d('user', 'Sex')) . '</th>';
			?>
			<?php // To save space only root (who can view multiple assoc.) or in "partner wanted" view show association ?>
			<?php 
				if ($wantAssociation)
					echo '<th class="ttm-table-col">' . $this->Paginator->sort('Nations.name', __d('user', 'Association')) . '</th>';
			?>
			<?php // In "partner wanted" view all entries are players, so to save space don't show the type ?>
			<?php if (!$isPartnerWanted) { ?>
				<?php if ($isParticipant) { ?>
					<th class="ttm-table-col"><?php echo __d('user', 'Player');?></th>
				<?php } else { ?>
					<th  class="ttm-table-col"><?php echo $this->Paginator->sort('Types.name', __d('user', 'Type'));?></th>
				<?php } ?>
			<?php } ?>
			<?php // Only players show start number and competition ?>
			<?php if ($wantCompetitions) { ?>
				<?php // Only as root or in veterans we can see the WR ID ?>
				<?php $wrid = __d('user', 'Reg. ID'); ?>
				<?php if (!$isPartnerWanted) { ?>
					<th class="ttm-table-col"><?php echo $this->Paginator->sort('People.extern_id', $wrid);?></th>
				<?php } ?>
				<?php if ($current_user['User']['group_id'] != GroupsTable::getParticipantId()) { ?>
					<th class="ttm-table-col"><?php echo $this->Paginator->sort('Participants.start_no', __d('user', 'Start No.'));?></th>
				<?php } ?>
				<?php // In "partner wanted" view we don't need to show the singles competition ?>
				<?php if (!$isPartnerWanted && $count['S'] > 0) { ?>
					<th class="ttm-table-col"><?php echo __d('user', 'Single');?></th>
				<?php } ?>
				<?php if ($count['D'] > 0) { ?>
					<th class="ttm-table-col"><?php echo __d('user', 'Double');?></th>
					<th class="ttm-table-col"><?php echo __d('user', 'Double Partner');?></th>
				<?php } ?>
				<?php if ($count['X'] > 0) { ?>
					<th class="ttm-table-col"><?php echo __d('user', 'Mixed');?></th>
					<th class="ttm-table-col"><?php echo __d('user', 'Mixed Partner');?></th>
				<?php } ?>
				<?php // In "partner wanted" view we don't need to show the teams competition ?>
				<?php if (!$isPartnerWanted && $count['T'] > 0) { ?>
					<th class="ttm-table-col"><?php echo __d('user', 'Team');?></th>
				<?php } ?>
			<?php } ?>
			<th class="ttm-table-col"><?php echo $this->Paginator->sort('Registrations.modified', __d('user', 'Updated'));?></th>
			<th class="actions" colspan="4"><?php echo __d('user', 'Actions');?></th>
	</tr></thead>
	<tbody>
	<?php
	$i = 0;

	foreach ($registrations as $registration):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}

		$participant = $registration['participant'];

		$classCancelled = ' class="cancelled"';    // Class for cancelled participation
		$classWanted = ' class="partnerwanted"';   // Class for not yet confirmed partner

		// class attribute for the competitions (Single, Double, ...)
		$cancelled = $registration['cancelled'] ? $classCancelled : null;
		$singleClass = $participant['single_cancelled'] ? $classCancelled : null;
		$doubleClass = $participant['double_cancelled'] ? $classCancelled : null;
		$mixedClass = $participant['mixed_cancelled'] ? $classCancelled : null;
		$teamClass = $participant['team_cancelled'] ? $classCancelled : null;

		// class attribute for the double and mixed partner
		$doublePartnerClass = null;
		$mixedPartnerClass = null;

		// May the user view / edit / change this entry
		$allowThis = $hasRootPrivileges;
		
		if (!$hasRootPrivileges) {
			// Participant and Tour Operator may only edit their own players
			// All others, e.g. Referee, may only edit those without a set user id
			if ($current_user['group_id'] == GroupsTable::getParticipantId()) {
				$allowThis |= $registration['person']['user_id'] == $current_user['id'];
			} else  if ($current_user['group_id'] == GroupsTable::getTourOperatorId()) {
				$allowThis |= $registration['person']['user_id'] == $current_user['id'];
			} else {
				$allowThis |= empty($registration['person']['user_id']);
			}
			
			// Test of assocation and functions
			if (!empty($current_user['nation_id']))
				$allowThis &= $current_user['nation_id'] == $registration['person']['nation_id'];
			if (!empty($current_user['group']['type_ids']))
				$allowThis &= in_array($registration['type_id'],  explode(",", $current_user['group']['type_ids']));
		}

		// If the partner is confirmed take the competition class (maybe cancelled)
		// If the partner is not confirmed and the player has cancelled, it is both
		// If the player is playing and has no partner or partner is not confirmed, it is "partnerwanted"
		if ($participant['double_partner_id'] && RegistrationsTable::isDoublePartnerConfirmed($registration))
			$doublePartnerClass = $doubleClass;
		else if ($participant['double_cancelled'])
			$doublePartnerClass = ' class="cancelled partnerwanted"';
		else if ($participant['double_id'])
			$doublePartnerClass = ' class="partnerwanted"';
	
		// Same for mixed	
		if ($participant['mixed_partner_id'] && RegistrationsTable::isMixedPartnerConfirmed($registration))
			$mixedPartnerClass = $mixedClass;
		else if ($participant['mixed_cancelled'])
			$mixedPartnerClass = ' class="cancelled partnerwanted"';
		else if ($participant['mixed_id'])
			$mixedPartnerClass = ' class="partnerwanted"';
	?>
	<tr<?php echo $class;?>>
		<td <?php echo $cancelled;?>>
			<?php 
				// Only root or the user responsible for this association may view the person details
				if ($allowPersonView && $allowThis) {
					echo $this->Html->link($registration['person']['display_name'], array(
						'controller' => 'people', 'action' => 'view', $registration['person']['id']
					)); 
				} else if ($allowPersonEdit && $allowThis) {
					echo $this->Html->link($registration['person']['display_name'], array(
						'controller' => 'people', 'action' => 'edit', $registration['person']['id']
					)); 					
				} else {
					echo $registration['person']['display_name'];
				}
			?>
		</td>
		<?php
			if ($wantSex)
				echo '<td ' . $cancelled . '>' . $registration['person']['sex'] . '</td>';
		?>
		<?php
			// Only root or organizer or in "partner wanted" view the association is shown
			if ($wantAssociation) {
				if (isset($registration['person']['nation_id']))
					echo '<td ' . $cancelled . '>' . $allNations[$registration['person']['nation_id']] . '</td>';
				else
					echo '<td></td>';
			}

			// Only show function if not in "partner wanted" view 
			// Also hide columns if there is no such competition type, e.g. mixed in EC 
			if (!$isPartnerWanted) { 
				echo '<td ' .$cancelled . '>';
				if ($current_user['group_id'] == GroupsTable::getParticipantId())
					echo ($registration['type_id'] == TypesTable::getPlayerId() ? __d('user', 'Yes') : __d('user', 'No'));
				else
					echo $types[$registration['type_id']];
				echo '</td>';
			} 
		?>

		<?php // Only with players show start no and competitions ?>
		<?php if ($wantCompetitions) { ?>
			<?php
				if (!$isPartnerWanted) {
					if (!empty($registration['person']))
						echo '<td ' . $cancelled . '>' . $registration['person']['extern_id'] . '</td>';
					else
						echo '<td/>';
				}

				if ($current_user['group_id'] != GroupsTable::getParticipantId()) {
					if (!empty($registration['participant']['start_no']))
						echo '<td ' . $cancelled . '>' . $registration['participant']['start_no'] . '</td>';
					else
						echo '<td/>';
				}
			?>
			<?php // Only shown if not in "partner wanted" view ?>
			<?php if (!$isPartnerWanted && $count['S'] > 0) { ?>
				<td <?php echo $singleClass;?>>
					<?php 
						if (!empty($participant['single_id']))
							echo $competitions[$participant['single_id']];
					?>
				</td>
			<?php } ?>
			<?php if ($count['D'] > 0) { ?>
				<td <?php echo $doubleClass;?>>
					<?php 
						if (!empty($participant['double_id']))
							echo $competitions[$participant['double_id']];
					?>
				</td>
				<td <?php echo $doublePartnerClass;?>>
					<?php 
						if (!empty($participant['double_id'])) {
							if (empty($participant['double_partner_id'])) {
								echo __d('user', 'Partner wanted');
							} else {
								$double_partner_name = $participant['double_partner']['person']['display_name'];
								if ($registration['person']['nation_id'] != $participant['double_partner']['person']['nation_id'])
									$double_partner_name .= ' (' . $allNations[$participant['double_partner']['person']['nation_id']] . ')';

								if (!RegistrationsTable::isDoublePartnerConfirmed($registration))
									echo $double_partner_name . ' (' . __d('user', 'wanted') . ')';
								else
									echo $double_partner_name;
							}
						}
					?>
				</td>
			<?php } ?>
			<?php if ($count['X'] > 0) { ?>
				<td <?php echo $mixedClass;?>>
					<?php 
						if (!empty($participant['mixed_id']))
							echo $competitions[$participant['mixed_id']];
					?>
				</td>
				<td <?php echo $mixedPartnerClass;?>>
					<?php 
						if (!empty($participant['mixed_id'])) {
							if (empty($participant['mixed_partner_id'])) {
								echo __d('user', 'Partner wanted');
							} else {
								$mixed_partner_name = $participant['mixed_partner']['person']['display_name'];
								if ($registration['person']['nation_id'] != $participant['mixed_partner']['person']['nation_id'])
									$mixed_partner_name .= ' (' . $allNations[$participant['mixed_partner']['person']['nation_id']] . ')';

								if (!RegistrationsTable::isMixedPartnerConfirmed($registration))
									echo $mixed_partner_name . ' (' . __d('user', 'wanted') . ')';
								else
									echo $mixed_partner_name;
							}
						}
					?>
				</td>
			<?php } ?>
			<?php if (!$isPartnerWanted && $count['T'] > 0) { ?>
				<td <?php echo $teamClass;?>>
					<?php 
						if (!empty($participant['team_id']))
							echo $competitions[$participant['team_id']];
					?>
				</td>
			<?php } ?>
		<?php } ?>

		<td><?php echo $registration['modified']; ?>&nbsp;</td>
		<td class="actions">
		<?php // View and changes are allowed for owners or admins only ?>
		<?php 
			if ($allowHistory && $allowThis && $registration['type_id'] == TypesTable::getPlayerId())
				echo $this->Html->link(__d('user', 'History'), array('action' => 'history', $registration['id'])); 			
			else if ($allowView && $allowThis)
				echo $this->Html->link(__d('user', 'View'), array('action' => 'view', $registration['id'])); 
		?>
		</td>

		<?php // Changes are allowed for admin only or before 2nd deadline ?>
		<td class="actions">
		<?php 
			if ( $allowEditParticipant && $allowThis && ($hasRootPrivileges || empty($registration['cancelled'])) )
				echo $this->Html->link(__d('user', 'Edit'), array('action' => 'edit_participant', $registration['id'])); 
			else if ($allowEdit && $allowThis && ($hasRootPrivileges || empty($registration['cancelled'])) )
				echo $this->Html->link(__d('user', 'Edit'), array('action' => 'edit', $registration['id'])); 
		?>
		</td>
		<td class="actions">
		<?php 
			if ($allowDelete && $allowThis && empty($registration['cancelled'])) {
				echo $this->Form->postLink(__d('user', 'Delete'), array('action' => 'delete', $registration['id']),  
				['confirm' => sprintf(__d('user', 'Are you sure you want to delete %s?'), $registration['person']['display_name'])]);
			} else if ($allowDeleteParticipant && $allowThis && empty($registration['cancelled'])) {
				if (!empty($registration['OrderStatus']) && $registration['OrderStatus'] === 'INVO') {
					echo $this->Form->postLink(__d('user', 'Delete'), array('action' => 'delete_participant', $registration['id']), 
						['confirm' => sprintf(__d('user', 'Are you sure you want to delete %s?'), $registration['person']['display_name'])]);
				}
			}
		?>
		</td>
		<td class="actions">
		<?php 
			if ($allowRequests && $registration['requests'] 
				&& (
					( 
						!empty($registration['participant']['double_id']) && 
						empty($registration['participant']['double_cancelled']) &&
						!RegistrationsTable::isDoublePartnerConfirmed($registration)
					)
					||
					(
						!empty($registration['participant']['mixed_id']) && 
						empty($registration['participant']['mixed_cancelled']) &&
						!RegistrationsTable::isMixedPartnerConfirmed($registration)
					) 
				)
			)
					
				echo $this->Html->link(__d('user', 'Requests'), array('action' => 'requests', $registration['id']));
		?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<?php echo $this->element('paginator'); ?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php 
			if (false && $Acl->check($current_user, 'Registrations/add'))
				echo '<li>' . $this->Html->link(__d('user', 'New Registration'), array('action' => 'add')) . '</li>';
			else if ($Acl->check($current_user, 'Registrations/add_participant'))
				echo '<li>' . $this->Html->link(__d('user', 'New Registration'), array('action' => 'add_participant')) . '</li>';
		?>
		<?php 
			if ($isPartnerWanted) {
				if ($Acl->check($current_user, 'Registrations/index'))
					echo '<li>' . $this->Html->link(__d('user', 'List Registrations'), array('action' => 'index')) . '</li>';
			} else {
				if ($Acl->check($current_user, 'Registrations/list_partner_wanted'))
					echo '<li>' . $this->Html->link(__d('user', 'List Partner Wanted'), array('action' => 'list_partner_wanted')) . '</li>';
			}
		?>
		<?php
			if ($Acl->check($current_user, 'Registrations/assign_numbers'))
				echo '<li>' . $this->Html->link(__('Assign Start Numbers'), array('action' => 'assign_numbers')) . '</li>';
		?>
		<?php
			if ($Acl->check($current_user, 'Registrations/count'))
				echo '<li>' . $this->Html->link(__('Count Participants'), array('action' => 'count')) . '</li>';
		?>
		<?php 
			if (false && $Acl->check($current_user, 'Registrations/import'))
				echo '<li>' . $this->Html->link(__('Import'), array('action' => 'import')) . '</li>';
		?>
		<?php 
			if (false && $Acl->check($current_user, 'Registrations/import_partner'))
				echo '<li>' . $this->Html->link(__('Import Partner'), array('action' => 'import_partner')) . '</li>';
		?>
		<?php 
			if ($Acl->check($current_user, 'Registrations/export'))
				echo '<li>' . $this->Html->link(__d('user', 'Export'), array('action' => 'export.csv')) . '</li>';
			if ($Acl->check($current_user, 'Registrations/export_participants'))
				echo '<li>' . $this->Html->link(__d('user', 'Export Order'), array('action' => 'export_participants.csv')) . '</li>';
		?>
	</ul>
<?php $this->end(); ?>
