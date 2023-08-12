<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="competitions view">
<h2><?php echo __('Competition');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $competition['name']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Description'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $competition['description']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Sex'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $sex[$competition['sex']]; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Type'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $types[$competition['type_of']]; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Born'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $competition['born']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Para TT Class'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php 
				if (($competition->ptt_class ?? 0) == 0)
					echo __('No para event');
				else if ($competition->ptt_class == -1)
					echo __('Need ITTF paralympic classification');
				else
					echo $competition['ptt_class'];
			?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Optional'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $competition['optin']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Entries'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $competition['entries']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Entries Host'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $competition['entries_host']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Updated At'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $competition['modified']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Created At'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $competition['created']; ?>
			&nbsp;
		</dd>
	</dl>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Competition'), array('action' => 'edit', $competition['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Competition'), array('action' => 'delete', $competition['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $competition['description'])]); ?> </li>
		<li><?php echo $this->Html->link(__('List Competitions'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Competition'), array('action' => 'add')); ?> </li>
	</ul>
<?php $this->end(); ?>
