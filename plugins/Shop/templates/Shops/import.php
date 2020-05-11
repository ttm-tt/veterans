<div class="people form">
	<?php echo $this->Form->create(false, array('action' => 'import', 'type' => 'file')); ?>
	<fieldset>
 		<legend><?php echo __('Import Orders'); ?></legend>
	<?php
    	echo $this->Form->control('File', array(
			'type' => 'file',
			'label' => __('File')
		));
		echo $this->Form->control('Order.email', array(
			'type' => 'text',
			'label' => __('Email')
		));
	?>
	</fieldset>
	<?php
		echo $this->element('savecancel', array('save' => __('Import')));
    	echo $this->Form->end();
	?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
