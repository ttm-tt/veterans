<?php
	$fieldNames = array(
		'created' => __('Created'),
		'user_id' => __('User'),
		'order_status_id' => __('Status'),
		'total' => __('Gross Total'),
		'invoice' => __('Invoice'),
		'invoice_paid' => __('Invoice Paid'),
		'invoice_cancelled' => __('Invoice Cancelled'),
		'paid' => __('Paid'),
		'discount' => __('Discount'),
		'cancellation_fee' => __('Cancellation Fee'),
		'cancellation_discount' => __('Cancellation Discount'),
		'email' => __('Email')
	);

?>
<div class="order index">
	<h2>
	<?php 
		echo __('History of order') . ' ' . $order['invoice'];
	?>
	</h2>

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
	echo '<td>' . $this->Html->link(__('Current'), array('action' => 'view', $order['id'])) . '</td>';
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
					$history['order_id'], 
					'?' => ['date' => $history['created']->format('Y-m-d H:i:s')]
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
			echo '<li>' . $this->Html->link(__('List Order'), array('action' => 'index')) . '</li>';
		?>
	</ul>
<?php $this->end(); ?>
