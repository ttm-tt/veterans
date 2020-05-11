<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="people form">
	<?php echo $this->Form->create('Person', array('action' => 'update', 'type' => 'file')); ?>
	<fieldset>
 		<legend><?php echo __('Update People'); ?></legend>
	<?php
    	echo $this->Form->file('File');
	?>
	</fieldset>
	<?php
		echo $this->element('savecancel', array('save' => __('Update')));
    	echo $this->Form->end();
	?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List People'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
