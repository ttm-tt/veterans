<div class="articles view">
<h2><?php echo __('Allotment');?></h2>
	<?php $i = 0; $class = ' class="altrow"';?>
	<dl style="width:60%;">
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Article'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $allotment['article']['description']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('User'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $allotment['user']['username']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Allotment'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $allotment['allotment']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Updated At'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $allotment['modified']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Created At'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $allotment['created']; ?>
			&nbsp;
		</dd>
	</dl>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Allotment'), array('action' => 'edit', $allotment['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Allotment'), array('action' => 'delete', $allotment['id']), ['confirm' => sprintf(__('Are you sure you want to delete this allotment?'))]); ?> </li>
		<li><?php echo $this->Html->link(__('List Allotments'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Allotment'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Articles'), array('controller' => 'Articles', 'action' => 'index')); ?> </li>
	</ul>
<?php $this->end(); ?>
