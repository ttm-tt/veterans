<div class="order address form">
	<?php echo $this->Wizard->create(null);?>
	<?php echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Waiting List');?></h2>
	<div class="hint">
		<?php
			echo __d('user', 'If you confirm your order it will be put on a waiting list.');
			echo __d('user', 'This waiting list will be processed first come first served as new registrations may become available.');
		?>
	</div>
	<?php echo $this->element('shop_footer', array('next' => __d('user', 'Confirm Order'))); ?>
	<?php echo $this->Form->end(); ?>
</div>
