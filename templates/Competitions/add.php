<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="competitions form">
<?php echo $this->Form->create($competition);?>
	<fieldset>
 		<legend><?php echo __('Add Competition'); ?></legend>
	<?php
		echo $this->Form->control('tournament_id', array(
			'type' => 'hidden', 
			'value' => $this->request->getSession()->read('Tournaments.id')
		));
		echo $this->Form->control('name');
		echo $this->Form->control('description');
		echo $this->Form->control('sex', array(
			'type' => 'select',
			'options' => $sex
		));
		echo $this->Form->control('type_of', array(
			'type' => 'select',
			'options' => $types,
			'label' => __('Type')
		));
		echo $this->Form->control('born', array('label' => __('Cutoff year')));
		echo $this->Form->control('entries', array('label' => __('No of Entries')));
		echo $this->Form->control('entries_host', array('label' => __('No of Entries Host')));
	?>
	</fieldset>
<?php 
	echo $this->element('savecancel', array('continue' => __('Save & Cont.')));
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List Competitions'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
