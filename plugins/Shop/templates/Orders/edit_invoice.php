<?php
	$this->Html->scriptStart(array('block' => true));
?>

function updateSubtotal() {
	var subtotal = 0;
	subtotal += parseFloat($('input#total').val());
	subtotal -= parseFloat($('input#cancellation-fee').val());  // Negative number
	subtotal -= parseFloat($('input#discount').val());
	
	$('input#subtotal').val(subtotal);
	
	updateOutstanding();
}

function updateOutstanding() {
	var subtotal = 0.;
	subtotal += parseFloat($('input#subtotal').val());
	subtotal -= parseFloat($('input#paid').val());
	$('input#outstanding').val(subtotal);
	
	if (subtotal <= 0.) {
		$('select#OrderOrderStatusId').attr('disabled', false);
	} else {	
		$('select#OrderOrderStatusId').val(<?php echo $order['order_status_id'];?>);
		$('select#OrderOrderStatusId').attr('disabled', true);
	}
}

$(document).ready(function() {
	updateSubtotal();
	updateOutstanding();
});	

<?php
	$this->Html->scriptEnd();
?>


<div class="order form">
<?php echo $this->Form->create($order); ?>
	<fieldset>
		<legend><?php echo __('Edit Invoice');?></legend>
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
				'onBlur' => 'updateSubtotal(); return false;'
			));
			
			echo $this->Form->control('subtotal', array(
				'name' => false,
				'id' => 'subtotal',
				'label' => __('Subtotal'),
				'readonly' => 'readonly',
				'type' => 'text',
				'after' => '&nbsp;' . $shopSettings['currency']
			));
			
			echo $this->Form->control('paid', array(
				'label' => __('Paid'),
				'id' => 'paid',
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
			
			echo $this->Form->control('invoice_split', array(
				'label' => __('Split No')
			));
			
			echo '<p></p>';
			
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
	
	<p></p>
<?php
?>
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
