<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="people form">
	<?php echo $this->Form->create('Registration', array('url' => array('action' => 'import_partner'), 'type' => 'file')); ?>
	<fieldset>
 		<legend><?php echo __('Import Double Partners'); ?></legend>
	<?php
    	echo $this->Form->file('File');
	?>
	</fieldset>
	<?php
		echo $this->element('savecancel', array('save' => __('Import')));
    	echo $this->Form->end();
	?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List Registrations'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
