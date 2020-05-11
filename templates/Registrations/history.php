<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$fieldNames = array(
		'created' => __('Created'),
		'cancelled' => __('Cancelled'),
		'single_id' => __('Singles'),
		'double_id' => __('Doubles'),
		'mixed_id'  => __('Mixed'),
		'team_id'   => __('Teams'),
		'double_partner_id' => __('Request double partner'),
		'mixed_partner_id'  => __('Request mixed partner'),
		'double_partner_wanted' => __('Wanted as double partner by'),
		'mixed_partner_wanted'  => __('Wanted as mixed partner by'),
		'double_partner_confirmed' => __('Confirmed as double partner by'),
		'mixed_partner_confirmed'  => __('Confirmed as mixed partner by'),
		'double_partner_withdrawn' => __('Rejected as double partner by'),
		'mixed_partner_withdrawn'  => __('Rejected as mixed partner by'),
		'single_cancelled' => __('Single cancelled'),
		'double_cancelled' => __('Double cancelled'),
		'mixed_cancelled' => __('Mixed cancelled'),
		'team_cancelled' => __('Team cancelled'),
		'replaced_by_id' => __('Replaced by')
	);

?>
<div class="registrations index">
	<h2><?php echo __('Registration History of') . ' ' . $registration['person']['display_name'];?></h2>

	<table>
		<tr>
			<th><?php echo __('Date');?></th>
			<th><?php echo __('User');?></th>
			<th><?php echo __('Field');?></th>
			<th><?php echo __('Old Value');?></th>
			<th><?php echo __('New Value');?></th>
		</tr>
<?php
	$i = 0;
	$lastTime = null;

	echo '<tr>';
	echo '<td>' . $this->Html->link(__('Current', true), array('action' => 'view', $registration['id'])) . '</td>';
	echo '<td></td>'; // user
	echo '<td></td>'; // field_name
	echo '<td></td>'; // old_value
	echo '<td></td>'; // new_value
	echo '</tr>';	

	// Increment row number

	foreach ($histories as $history) {
		$class = null;
		if ($i++ %2 == 0) {
			$class = ' class="altrow"';
		}

		echo '<tr $class>';
		if ($history['created'] == $lastTime) {
			echo '<td></td>';
			echo '<td></td>';
		} else {
			echo '<td>' . $this->Html->link($history['created'], array(
				'action' => 'revision', 
				$registration['id'], 
				'?' => ['date' => $history['created']->format('Y-m-d H:i:s')]
			)) . '</td>';
			echo '<td>' . $history['user']['username'] . '</td>';
		}

		$lastTime = $history['created'];
			
		$field_name = $history['field_name'];
		$old_name   = $history['old_name'];
		$new_name   = $history['new_name'];
   
		if (array_key_exists($field_name, $fieldNames))
			echo '<td>' . $fieldNames[$field_name] . '</td>';
		else
			echo '<td>' . $field_name . '</td>';
		if ($field_name == 'created' || $field_name == 'cancelled') {
			echo '<td></td><td></td>';
		} else {
			if (is_array($old_name))
				echo '<td>' . $this->Html->link($old_name[0], $old_name[1]) . '</td>';
			else
				echo '<td>' . $old_name . '</td>';

			if (is_array($new_name))
				echo '<td>' . $this->Html->link($new_name[0], $new_name[1]) . '</td>';
			else
				echo '<td>' . $new_name . '</td>';
		}

		echo '</tr>';
	}
?>
	</table>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php
			echo '<li>' . $this->Html->link(__('List Registrations'), array('action' => 'index')) . '</li>';

			echo '<li>' . $this->Html->link(__('View Registration'), array('action' => 'view', $registration['id'])) . '</li>';

			if ($Acl->check($current_user, 'Registrations/edit'))
				echo '<li>' . $this->Html->link(__('Edit Registration'), array('action' => 'edit', $registration['id'])) . '</li>';
		?>
	</ul>
<?php $this->end(); ?>
