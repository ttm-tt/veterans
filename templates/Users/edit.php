<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use App\Model\Table\GroupsTable;
?>

<?php echo $this->Html->scriptStart() ?>
	function onChangeGroup() {
		document.getElementById("nation-id").parentElement.style.display = "none";

		if (document.getElementById("group-id").value == <?php echo GroupsTable::getOrganizerId() ?>)
			document.getElementById("tournament-id").parentElement.style.display = "flex";
		else if (document.getElementById("group-id").value == <?php echo GroupsTable::getRefereeId() ?>)
			document.getElementById("tournament-id").parentElement.style.display = "flex";
		else if (document.getElementById("group-id").value == <?php echo GroupsTable::getParticipantId() ?>)
			document.getElementById("tournament-id").parentElement.style.display = "flex";
		else if (document.getElementById("group-id").value == <?php echo GroupsTable::getGuestId() ?>)
			document.getElementById("tournament-id").parentElement.style.display = "flex";
		else if (document.getElementById("group-id").value == <?php echo GroupsTable::getTourOperatorId() ?>)
			document.getElementById("tournament-id").parentElement.style.display = "flex";
		else if (document.getElementById("group-id").value == <?php echo GroupsTable::getCompetitionManagerId() ?>)
			document.getElementById("tournament-id").parentElement.style.display = "flex";
		else
			document.getElementById("tournament-id").parentElement.style.display = "none";

		if (document.getElementById("group-id").value == <?php echo GroupsTable::getTourOperatorId() ?>)
			document.getElementById("prefix-people").parentElement.style.display = "flex";
		else
			document.getElementById("prefix-people").parentElement.style.display = "none";
	}

	// Call onChangeGroup after the page is loaded to show / hide the association block 'UserNation'
	// $(function() {onChangeGroup();});
	$(document).ready(function() {onChangeGroup();});

<?php echo $this->Html->scriptEnd() ?>

<div class="users form">
<?php echo $this->Form->create($user);?>
	<fieldset>
 		<legend><?php echo __('Edit User'); ?></legend>
	<?php
		echo $this->Form->control('id', array('type' => 'hidden'));
		echo $this->Form->control('username');
		echo $this->Form->control('password'); // , array('after' => __('(Leave empty to keep current password)')));
		echo $this->Form->control('enabled', array('type' => 'checkbox'));
		echo $this->Form->control('email');
		echo $this->Form->control('add_email', array('label' => __('Add. Email')));
		echo $this->Form->control('group_id', array(
			'onchange' => 'onChangeGroup()'
		));

		echo $this->Form->control('nation_id', array(
			'empty' => true,
			'label' => __('Association')
		));
		
		echo $this->Form->control('language_id', array(
			'empty' => true,
			'label' => __('Language'),
			'options' => $languages			
		));

		echo $this->Form->control('tournament_id', array(
			'empty' => true
		));

		echo $this->Form->control('prefix_people', array(
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
		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
