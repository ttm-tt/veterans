<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$wcOptions = [
		0 => __('None'),
		1 => __('Complete'),
		2 => __('Ramp')
	];
?>

<div class="people view">
<h2>
<?php 
	echo __('Person');
	if (!empty($revision)) 
		echo ' (' . $revision . ')';
?>
</h2>
	<?php
		$sex = array('M' => __('Man'), 'F' => __('Woman'));
	?>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('First Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $person['first_name']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Last Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $person['last_name']; ?>
			&nbsp;
		</dd>
		<?php if ($hasRootPrivileges) { ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Display Name'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['display_name']; ?>
				&nbsp;
			</dd>
		<?php } ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Sex'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $sex[$person['sex']]; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Born'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $person['dob']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Para TT Class'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php 
				if (($person['ptt_class'] ?? 0) == 0)
					echo __('No para event');
				else if ($person['ptt_class'] == -1)
					echo __('Need ITTF paralympic classification');
				else
					echo $person['ptt_class'];
			?>
			&nbsp;
		</dd>
		<dt><?= __('Wheelchair'); ?></dt>
		<dd><?= $wcOptions[$person['ptt_wchc']]; ?>
		<?php if ($hasRootPrivileges) { ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Extern ID'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['extern_id']; ?>
				&nbsp;
			</dd>
		<?php } ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Association'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $person['nation']['description']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Email'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $person['email']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Phone'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $person['phone']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('User'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php if (!empty($person['user']['username'])) echo $this->Html->link($person['user']['username'], array('controller' => 'users', 'action' => 'view', $person['user_id'])); ?>
			&nbsp;
		</dd>
		<?php if (!empty($person['order']['invoice'])) { ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Invoice'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php 
					if ($Acl->check($current_user, 'controllers/Shop/Orders/view'))
						echo $this->Html->link($person['order']['invoice'], array('plugin' => 'shop', 'controller' => 'orders', 'action' => 'view', $person['order']['id'])); 
					else
						echo $person['order']['invoice'];
				?>
				&nbsp;
			</dd>
		<?php } ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Updated At'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $person['modified']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Created At'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $person['created']; ?>
			&nbsp;
		</dd>
	</dl>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php 
			if ($Acl->check($current_user, 'People/history'))
				echo '<li>' . $this->Html->link(__('View History'), array('action' => 'history', $person['id'])) . '</li>';
		?>
		<?php if ($Acl->check($current_user, 'People/edit'))
			echo '<li>' . $this->Html->link(__('Edit Person'), array('action' => 'edit', $person['id'])) . '</li>';
		?>
		<?php if ($Acl->check($current_user, 'People/delete') && count($person['registrations'] ?? []) === 0)
			echo '<li>' . $this->Html->link(__('Delete Person'), array('action' => 'delete', $person['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $person['last_name'])]) . '</li>';
		?>
		<?php if ($Acl->check($current_user, 'People/index'))
			echo '<li>' . $this->Html->link(__('List People'), array('action' => 'index')) . '</li>';
		?>
		<?php if ($Acl->check($current_user, 'People/add'))
			echo '<li>' . $this->Html->link(__('New Person'), array('action' => 'add')) . '</li>';
		?>
	</ul>

<?php $this->end(); ?>
