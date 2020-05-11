<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use App\Model\Table\GroupsTable;
?>

<div class="groups view">
<h2><?php echo __('Group');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $group['name']; ?>
			&nbsp;
		</dd>
		<?php // Hide allowed functions for admins: They are allowed to do everything. ?>
		<?php if ($group['Group']['id'] != GroupsTable::getAdminId()) { ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Functions'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $group['type_names']; ?>
			&nbsp;
		</dd>
		<?php } ?>
	</dl>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Group'), array('action' => 'edit', $group['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Group'), array('action' => 'delete', $group['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $group['name'])]); ?> </li>
		<li><?php echo $this->Html->link(__('List Groups'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Group'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
	</ul>
<?php $this->end(); ?>
