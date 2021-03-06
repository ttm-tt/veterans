<?php
	$this->Html->scriptStart(array('block' => true));
?>

function updateTotal() {
	var total = 0;
	total += parseFloat($('input#total').val());
	total -= parseFloat($('input#cancellation-fee').val());  // Negative number
	total -= parseFloat($('input#discount').val());
	
	$('input#total-calculated').val(total);
	
	updateOutstanding();
}

function updateOutstanding() {
	var total = 0.;
	total += parseFloat($('input#total-calculated').val());
	total -= parseFloat($('input#paid').val());
	$('input#outstanding').val(total);
	
	if (total <= 0.) {
		$('select#order-status-id').attr('readonly', false);
	} else {	
		$('select#order-status-id').val(<?php echo $order['order_status_id'];?>);
		$('select#order-status-id').attr('readonly', true);
	}
}

$(document).ready(function() {
	updateTotal();
	updateOutstanding();
});	

<?php
	$this->Html->scriptEnd();
?>


<div class="order form">
<?php echo $this->Form->create($order); ?>
	<fieldset>
		<legend><?php echo __('Payment Received');?></legend>
		<?php
			echo $this->Form->control('id', array('type' => 'hidden'));
			
			echo $this->Form->control('total', array(
				'label' => __('Amount'),
				'readonly' => 'readonly',
				'type' => 'text',
				'after' => '&nbsp;' . $shopSettings['currency']
			));
			
			echo $this->Form->control('cancellation_fee', array(
				'label' => __('Canc. Fee'),
				'readonly' => 'readonly',
				'type' => 'text',
				'value' => -$order['cancellation_fee'],
				'after' => '&nbsp;' . $shopSettings['currency']
			));
			
			echo $this->Form->control('discount', array(
				'label' => __('Discount'),
				'type' => 'text',
				'after' => '&nbsp;' . $shopSettings['currency'],
				'onBlur' => 'updateTotal(); return false;'
			));
			
			echo $this->Form->control('total_calculated', array(
				'name' => false,
				'id' => 'total-calculated',
				'label' => __('Total'),
				'readonly' => 'readonly',
				'type' => 'text',
				'value' => 0,
				'after' => '&nbsp;' . $shopSettings['currency']
			));
			
			echo $this->Form->control('paid', array(
				'label' => __('Paid'),
				'type' => 'text',
				'after' => '&nbsp;' . $shopSettings['currency'],
				'onBlur' => 'updateOutstanding(); return false;'
			));
			
			echo $this->Form->control('outstanding', array(
				'name' => false,
				'id' => 'outstanding',
				'label' => __('Outstanding'),
				'readonly' => 'readonly',
				'after' => '&nbsp;' . $shopSettings['currency']
			));
			
			echo $this->Form->control('order_status_id', array(
				'label' => __('Status'),
				'options' => $stati,
				'empty' => false
			));
			
			echo $this->Form->control('order_comments.' . count($order['order_comments']) . '.comment', array(
				'label' => __('Comment'),
				'type' => 'textarea'
			));
			
			if (count($order['order_comments']) > 0) {
				foreach ($order['order_comments'] as $idx => $comment) {
					$msg = '';
					$msg .= '[' . $comment['user']['username'] . ': ' . $comment['created'] . ']' . "\n";
					$msg .= $comment['comment'];
					
					echo $this->Form->control('order_comments.' . $idx . '.comment', array(
						'name' => false,
						'label' => '#' . ($idx + 1),
						'type' => 'textarea',
						'readonly' => 'readonly',
						'value' => $msg,
						'rows' => count(explode("\n", $msg))
					));
				}
			}			
		?>
	</fieldset>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List Orders'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
