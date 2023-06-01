<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$fieldNames = array(
		'created' => __('Created'),
		'cancelled' => __('Cancelled'),
		'first_name' => __('Given Name'),
		'last_name' => __('Family Name'),
		'display_name' => __('Display Name'),
		'user_id' => __('User'),
		'dob' => __('Birthday'),
		'sex' => __('Sex'),
		'nation_id' => __('Nation')
	);

?>
<div class="person index">
	<h2>
	<?php 
		echo __('History of') . ' ' . $person['display_name'];
	?>
	</h2>

	<table cellpadding="0" cellspacing="0">
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
	echo '<td>' . $this->Html->link(__('Current'), array('action' => 'view', $person['id'])) . '</td>';
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
			echo 
				'<td>' . 
				$this->Html->link($history['created'], array(
					'action' => 'revision', 
					$person['id'], 
					'?' => ['date' => $this->Time->format($history['created'], 'yyyy-MM-dd HH:mm:ss')]
				)) . 
				'</td>';
			echo '<td>' . ($history['user']['username'] ?? '') . '</td>';
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
			echo '<li>' . $this->Html->link(__('List People'), array('action' => 'index')) . '</li>';

			echo '<li>' . $this->Html->link(__('View Person'), array('action' => 'view', $person['id'])) . '</li>';

			if ($Acl->check($current_user, 'People/edit'))
				echo '<li>' . $this->Html->link(__('Edit Person'), array('action' => 'edit', $person['id'])) . '</li>';
		?>
	</ul>
<?php $this->end(); ?>
