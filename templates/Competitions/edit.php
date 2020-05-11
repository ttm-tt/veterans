<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="competitions form">
<?php echo $this->Form->create($competition);?>
	<fieldset>
 		<legend><?php echo __('Edit Competition'); ?></legend>
	<?php
		echo $this->Form->control('id', array('type' => 'hidden'));
		echo $this->Form->control('sex', array('type' => 'hidden'));
		echo $this->Form->control('type_of', array('type' => 'hidden'));
		echo $this->Form->control('tournament_id', array('type' => 'hidden'));
		echo $this->Form->control('name');
		echo $this->Form->control('description');
		echo $this->Form->control('value_sex', array(
			'readonly' => 'readonly', 
			'type' => 'text', 
			'value' => $sex[$competition->sex],
			'label' => __('Sex')
		));
		echo $this->Form->control('value_type_of', array(
			'readonly' => 'readonly',
			'type' => 'text',
			'value' => $types[$competition->type_of],
			'label' => __('Type')
		));
		echo $this->Form->control('born', array('label' => __('Cutoff year')));
		echo $this->Form->control('entries', array('label' => __('No of Entries')));
		echo $this->Form->control('entries_host', array('label' => __('No of Entries Host')));
	?>
	</fieldset>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('View Competition'), array('action' => 'view', $competition->id));?></li>
		<li><?php echo $this->Html->link(__('List Competitions'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
