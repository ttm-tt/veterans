<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="nations form">
<?php echo $this->Form->create($nation);?>
	<fieldset>
 		<legend><?php echo __('Edit Association'); ?></legend>
	<?php
		echo $this->Form->control('id', array('type' => 'hidden'));
		echo $this->Form->control('name');
		echo $this->Form->control('description');
		echo $this->Form->control('continent');
	?>
	</fieldset>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>

		<li><?php echo $this->Html->link(__('View Association'), array('action' => 'view', $this->Form->value('Nation.id')));?></li>
		<li><?php echo $this->Html->link(__('List Associations'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
