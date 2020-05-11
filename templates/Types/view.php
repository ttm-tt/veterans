<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="types view">
<h2><?php echo __('Function');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $type['name']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Description'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $type['description']; ?>
			&nbsp;
		</dd>
	</dl>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Function'), array('action' => 'edit', $type['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Function'), array('action' => 'delete', $type['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $type['description'])]); ?> </li>
		<li><?php echo $this->Html->link(__('List Functions'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Function'), array('action' => 'add')); ?> </li>
	</ul>

<?php $this->end(); ?>
