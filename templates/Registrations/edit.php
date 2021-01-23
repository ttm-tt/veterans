<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use App\Model\Table\GroupsTable;
use App\Model\Table\TypesTable;
use App\Model\Table\RegistrationsTable;

use Cake\Routing\Router;
?>

<?php $participant = $registration['participant']; ?>
<?php
	$this->Html->scriptStart(array('block' => true));
?>

function onChange(url, callback) {
	$.post(
		url, 
		$('form').serialize(), 
		function(data) {callback(data);},
		'json'
	);		
}

function onChangeDouble(data) {
	var options = '<option value="">' + <?php echo '"' . __('Partner wanted') . '"' ?> + '</option>';

	$.each(data, function(i, s) {
		var id = s.id;
		var value = s.display_name;
		options += '<option value="' + id + '"';
		<?php if (!empty($registration['participant']['double_partner_id'])) { ?>
		if (id == <?php echo $registration['participant']['double_partner_id'] ?>)
			options += ' selected="selected"';
		<?php } ?>
		options += '>' + value + '</option>';
	});

	$('#participant-double-partner-id').html(options);
	$('#participant-double-partner-id').parent().css('display', $('#participant-double-id').val() ? 'flex' : 'none');
}


function onChangeMixed(data) {
	var options = '<option value="">' + <?php echo '"' . __('Partner wanted') . '"' ?> + '</option>';

	$.each(data, function(i, s) {
		var id = s.id;
		var value = s.display_name;
		options += '<option value="' + id + '"'
		<?php if (!empty($registration['participant']['mixed_partner_id'])) { ?>
		if (id == <?php echo $registration['participant']['mixed_partner_id'] ?>)
			options += ' selected="selected"';
		<?php } ?>
		options += '>' + value + '</option>';
	});

	$('#participant-mixed-partner-id').html(options);
	$('#participant-mixed-partner-id').parent().css('display', $('#participant-mixed-id').val() ? 'block' : 'none');
}

function camelizeName(name) {
	name = name.trim();
	if (name === name.toUpperCase() || name === name.toLowerCase()) {
		name = name.replace(/\w+/g, function(txt){
			return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
		});
	}
	
	// Special cases
	// TODO
	return name;
}

<?php
	$this->Html->scriptEnd();
?>
	

<?php
	$isParticipant = $current_user['group_id'] == GroupsTable::getParticipantId();
	$isTourOperator = $current_user['group_id'] == GroupsTable::getTourOperatorId();
		
?>

<div class="registrations form">
<?php echo $this->Form->create($registration);?>
	<fieldset>
 		<legend><?php echo __('Edit Registration'); ?></legend>
	<?php
		echo $this->Form->control('id', array('type' => 'hidden'));
		echo $this->Form->control('tournament_id', array('type' => 'hidden'));

		echo $this->Form->control('person.id', array('type' => 'hidden'));
		echo $this->Form->control('person.nation_id', array('type' => 'hidden'));
		echo $this->Form->control('person.dob', array('type' => 'hidden'));

		echo $this->Form->control('participant.id', array('type' => 'hidden'));

		echo $this->Form->control('participant.cancelled', array('type' => 'hidden'));
		echo $this->Form->control('participant.team_cancelled', array('type' => 'hidden'));

		if ($isParticipant) {
			echo $this->Form->control('person.display_name', array(
				'label' => __('Person'), 
				'readonly' => 'readonly'
			));

			echo $this->Form->control('person.first_name', array('type' => 'hidden'));
			echo $this->Form->control('person.last_name', array('type' => 'hidden'));
		} else {
			// $hasRootPrivileges || $isTourOperator
			echo $this->Form->control('person.display_name', array('type' => 'hidden'));
			echo $this->Form->control('person.first_name', array(
				'label' => __d('user', 'Given Name'), 
				'onBlur' => 'this.value = camelizeName(this.value);'
			));

			echo $this->Form->control('person.last_name', array(
				'label' => __d('user', 'Family Name'), 
				'onBlur' => 'this.value = $.trim(this.value.toUpperCase());'
			));			
		}

		if ($registration['type_id'] == TypesTable::getPlayerId()) {
			// Players may not be changed to something else.
			// That simplifies dealing with changed partners
			echo $this->Form->control('type_id', array('type' => 'hidden'));
			echo $this->Form->control('type.description', array('label' => __('Function'), 'readonly' => 'readonly'));
		} else if ($this->request->getSession()->check('Types.id') && $registration['type_id'] == $this->request->getSession()->read('Types.id')) {
			// If there is a filter for a type (e.g. media officer) don't change it.
			echo $this->Form->control('type_id', array('type' => 'hidden'));
			echo $this->Form->control('type.description', array('label' => __('Function'), 'readonly' => 'readonly'));
		} else {
			// Let the user select the type
			echo $this->Form->control('type_id', array(
				'label' => __('Function'),
				'onchange' => 
					'$(this).val() == ' . TypesTable::getPlayerId() . '? ' .
					'$("#participant").show() : $("#participant").hide(); '
			));
		}
		
		// Sex
		if ($registration['type_id'] != TypesTable::getPlayerId()) {
			echo $this->Form->control('person.sex', array(
				'type' => 'select', 
				'empty' => false,
				'options' =>array('M' => __d('user', 'Man'), 'F' => __d('user', 'Woman'))
			));						
		} else {
			echo $this->Form->control('person.sex', array('type' => 'hidden'));
			echo $this->Form->control('person_sex', array(
				'type' => 'select', 
				'empty' => __d('user', 'Select gender'),
				'options' =>array('M' => __d('user', 'Man'), 'F' => __d('user', 'Woman')),
				'readonly' => true,
				'id' => false
			));
		}
		

		$empty = ($isParticipant ? false : __('Select Event'));

		echo '<div id="participant" style="' . ($registration['type_id'] == TypesTable::getPlayerId() ? 'display:block;' : 'display:none;') . '">';
			if ($hasRootPrivileges)
				echo $this->Form->control('participant.start_no', array('empty' => true));

			echo '<div id="ParticipantSingle" style="padding:0;' . (count($competitions['singles']) ? 'display:block' : 'display:none') . '">';
				if (!$hasRootPrivileges) {
					echo $this->Form->control('participant.single_id', array(
						'type' => 'hidden'
					));
					echo $this->Form->control('participant.single.description', array(
						'required' => false,
						'readonly' => 'readonly', 
						'label' => __('Single')
					));
				} else {
					echo $this->Form->control('participant.single_id', array(
						'div' => 'singles', 
						'empty' => $empty,
						'options' => $competitions['singles']
					));
				}
			echo '</div>';

			$label = __('Double Partner Category');

			$style = 'display:block';
			if (count($competitions['doubles']) == 0)
				$style = 'display:none';
			if ($isParticipant && empty($registration['participant']['double_id']))
				$style = 'display:none';
			if ($isParticipant && $registration['participant']['double_cancelled'] != 0)
				$style = 'display:none';
			
			echo '<div id="ParticipantDouble" style="padding:0;' . $style . '">';
				echo $this->Form->control('participant.double.description', array(
					'required' => false,
					'readonly' => 'readonly', 
					'label' => __('Double')
				));

				echo $this->Form->control('participant.double_id', array(
					'div' => 'doubles', 
					'empty' => $empty,
					'options' => $competitions['doubles'],
					'label' => $label,
					'onchange' => 'onChange("' . Router::url(['action' => 'onChangeDouble'], true) . '", onChangeDouble);'
				));

				if ( !$hasRootPrivileges && $tournament['modify_before'] < date('Y-m-d') && 
					 RegistrationsTable::isDoublePartnerConfirmed($registration)) {
					echo $this->Form->control('participant.double_partner_id', array('div' => false, 'type' => 'hidden'));
					echo $this->Form->control('participant.double_partner.person.display_name', array(
						'div' => array('id' => 'ParticipantDoublePartner'),
						'readonly' => 'readonly',
						'label' => __('Double Partner')
					));
				} else {
					echo $this->Form->control('participant.double_partner_id', array(
						'div' => array(
							'id' => 'ParticipantDoublePartner', 
							'style' => ($registration['participant']['double_id'] ? '"display:block"' : '"display:none"')
						), 
						'empty' => __('Partner wanted'), 
						'options' => $double_partner
					));
				}
			echo '</div>';

			$label = __('Mixed Partner Category');

			$style = 'display:block';
			if (count($competitions['mixed']) == 0)
				$style = 'display:none';
			if ($isParticipant && empty($registration['participant']['mixed_id']))
				$style = 'display:none';
			if ($isParticipant && $registration['participant']['mixed_cancelled'] != 0)
				$style = 'display:none';
			
			echo '<div id="ParticipantMixed" style="padding:0;' . $style . '">';
				echo $this->Form->control('participant.mixed.description', array('readonly' => 'readonly', 'label' => __('Mixed')));

				echo $this->Form->control('participant.mixed_id', array(
					'div' => 'mixed', 
					'empty' => $empty,
					'options' => $competitions['mixed'],
					'label' => $label,
					'onchange' => 'onChange("' . Router::url(['action' => 'onChangeMixed'], true) . '", onChangeMixed);'					
				));

				if (!$hasRootPrivileges && $tournament['modify_before'] < date('Y-m-d') && 
						RegistrationsTable::isMixedPartnerConfirmed($registration)) {
					echo $this->Form->control('participant.mixed_partner_id', array('div' => false, 'type' => 'hidden'));
					echo $this->Form->control('participant.mixed_partner.person.display_name', array(
						'div' => array('id' => 'ParticipantMixedPartner'),
						'readonly' => 'readonly',
						'label' => __('Mixed Partner')
					));
				} else {
					echo $this->Form->control('participant.mixed_partner_id', array(
						'div' => array(
							'id' => 'ParticipantMixedPartner', 
							'style' => ($registration['participant']['mixed_id'] ? '"display:block"' : '"display:none"')
						), 
						'empty' => __('Partner wanted'), 
						'options' => $mixed_partner
					));
				}
			echo '</div>';

			echo '<div id="ParticipantTeam" style=' . (count($competitions['teams']) ? '"display:block"' : '"display:none"') . '>';
				echo $this->Form->control('participant.team_id', array(
					'div' => 'teams', 
					'empty' => $empty,
					'options' => $competitions['teams']
				));
			echo '</div>';
			
			echo $this->Form->control('person.email', array(
				'label' => __('Email')
			));
			
			echo $this->Form->control('person.phone', array(
				'label' => __('Phone')
			));
			
		echo '</div>';
		
		if ($hasRootPrivileges) {
			echo $this->Form->control('comment', array(
				'type' => 'textarea',				
			));
		} else {
			echo $this->Form->control('comment', ['type' => 'hidden']);
		}
	?>
	</fieldset>
	
	<?php if (!$isParticipant) { ?>
	<fieldset>
		<legend><?php echo __('Items');?></legend>
		<?php
			foreach ($articles as $article) {
				if (!$article['visible'])
					continue;
				
				$aid = $article['id'];
				$value = (empty($orderarticles[$aid]) ? 0 : $orderarticles[$aid]);
				
				echo $this->Form->control('Article.' . $article['name'], array(
					'label' => $article['description'],
					'type' => 'checkbox',
					'checked' => $value ? 'checked' : false,
					'readonly' => ($hasRootPrivileges ? false : 'readonly')
				));
			}
		?>		
	</fieldset>
	<?php } ?>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php 
			if ($Acl->check($current_user, 'Registrations/index'))
				echo '<li>' . $this->Html->link(__('List Registrations'), array('action' => 'index')) . '</li>';

			if ($Acl->check($current_user, 'Registrations/list_partner_wanted'))
				echo '<li>' . $this->Html->link(__('List Partner Wanted'), array('action' => 'list_partner_wanted')) . '</li>';
		?>
	</ul>
<?php $this->end(); ?>
