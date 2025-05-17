<div class="row">
	<div id="processing" class="order billing form" style="display:none">
		<h2>
			<?php echo __d('user', 'Please wait while your payment is being processed.');?>
			<br>
			<?php echo __d('user', 'This may take several minutes.');?>
		</h2>
	</div>

	<div id="billing" class="order billing form">
		<h2><?php echo __d('user', 'Billing Information');?></h2>
		<div class="hint">
		<?php
			echo __d('user', 'Payment will be processed in a secure way by {0}.', $paymentName) . '<br>';
			echo __d('user', 'After the payment is completed you will be redirected to the registration.') . '<br>';
		?>
		</div>	
	</div>

	<div class="order billing form">
		<form id="cart" >
			<?= $this->element('shop_order'); ?>
		</form>

		<?= $this->element('creditcard'); ?>

		<?php 
			echo $this->Form->create(null, array(
				'id' => 'submit', 
				'onsubmit' => 'onPay(); return false;', 
				'url' => $submitUrl
			)); 
		?>

		<?php if (!empty($ticket)) echo $this->Form->hidden(false, ['name' => 'ticket', 'value' => $ticket]); ?>
		
		<?= $this->element('savecancel', ['save' => __('Confirm')]); ?>
		
		<?= $this->Form->end(); ?>
	</div>
</div>