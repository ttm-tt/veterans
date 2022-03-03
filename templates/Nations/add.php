<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="nations form">
<?php echo $this->Form->create($nation);?>
	<fieldset>
 		<legend><?php echo __('Add Association'); ?></legend>
	<?php
		echo $this->Form->control('name');
		echo $this->Form->control('description');
		echo $this->Form->control('continent');
		echo $this->Form->control('enabled');
	?>
	</fieldset>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>

		<li><?php echo $this->Html->link(__('List Associations'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List People'), array('controller' => 'people', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Person'), array('controller' => 'people', 'action' => 'add')); ?> </li>
	</ul>
<?php $this->end(); ?>
