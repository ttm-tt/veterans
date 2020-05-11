<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="types form">
<?php echo $this->Form->create($type);?>
	<fieldset>
 		<legend><?php echo __('Edit Function'); ?></legend>
	<?php
		echo $this->Form->control('id', array('type' => 'hidden'));
		echo $this->Form->control('name');
		echo $this->Form->control('description');
	?>
	</fieldset>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List Functions'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
