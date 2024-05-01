<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="notifications form">
<?php echo $this->Form->create($notification);?>
<fieldset>
	<legend><?php echo __('Edit Notification of {0}', $user->username); ?></legend>
<?php
	$d = array();
	$d['new_player'] = __('New Player');
	$d['delete_registration_player'] = __('Delete player');
	$d['delete_registration_player_after'] = __('Delete player after deadline');
	$d['edit_registration_player_after'] = __('Edit player after deadline');

	echo $this->Form->control('id', array('type' => 'hidden'));
	echo $this->Form->control('user_id', array('type' => 'hidden'));
	echo $this->Form->control('all_notifications', array('type' => 'checkbox'));

	foreach ($columns as $c) {
		if ($c == 'all_notifications' || $c == 'id' || $c == 'user_id')
			continue;

		echo $this->Form->control($c, array(
			'type' => 'checkbox',
			'label' => $d[$c]
		));
	}
?>
</fieldset>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
	</ul>
<?php $this->end(); ?>
