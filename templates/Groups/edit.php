<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use App\Model\Table\GroupsTable;
?>

<div class="groups form">
<?php echo $this->Form->create($group);?>
	<fieldset>
 		<legend><?php echo __('Edit Group'); ?></legend>
	<?php
		echo $this->Form->control('id', array('type' => 'hidden'));
		echo $this->Form->control('name');
		if ($group->id !== GroupsTable::getAdminId())
			echo $this->Form->control('types', array(
				'options' => $types, 
				'multiple' => true, 
				'size' => count($types), 
				'label' => __('Functions'),
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
