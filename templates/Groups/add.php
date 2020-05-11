<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="groups form">
<?php echo $this->Form->create($group);?>
	<fieldset>
 		<legend><?php echo __('Add Group'); ?></legend>
	<?php
		echo $this->Form->control('name');
		echo $this->Form->control('types', array(
			'options' => $types, 
			'multiple' => true, 
			'size' => count($types), 
			'label' => __('Functions')
		));
	?>
	</fieldset>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>

		<li><?php echo $this->Html->link(__('List Groups'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
	</ul>
<?php $this->end(); ?>
