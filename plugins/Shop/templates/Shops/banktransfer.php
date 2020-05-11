<div class="order address form">
	<?php echo $this->Wizard->create(null);?>
	<?php echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Payment by Bank Transfer');?></h2>
	<div class="hint">
		<?php
			echo __d('user', 'You will receive instructions in your confirmation email.');
			echo '<br>';
			echo __d('user', 'Please note that your registration is not valid until we have received the full amount of due payment.');
		?>
	</div>
	<?php echo $this->element('shop_footer', array('next' => __d('user', 'Confirm Registration'))); ?>
	<?php echo $this->Form->end(); ?>
</div>
