<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use App\Model\Table\GroupsTable;
use App\Model\Table\RegistrationsTable;
use App\Model\Table\TypesTable;

use Cake\Routing\Router;
use Cake\Utility\Hash;
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
	var options = '<option value="">' + <?php echo '"' . __d('user', 'Partner wanted') . '"' ?> + '</option>';

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
	var options = '<option value="">' + <?php echo '"' . __d('user', 'Partner wanted') . '"' ?> + '</option>';

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

$(document).ready(function() {
	// Show / hide para settings
	$('select#person-ptt-class').parent().hide();
	$('select#perspn-wchw').parent().hide();
	
	$('input#person-is-para').change(function() {
		if (this.checked) {
			$('select#person-ptt-class').parent().show();
			$('input#person-ptt-nonpara').parent().show();
		} else {
			$('select#person-ptt-class').parent().hide();
			$('select#person-wchc').parent().hide();
			$('input#person-ptt-nonpara').parent().hide();
		}
	});
	
	$('select#person-ptt-class').change(function() {
		if (!$('input#person-is-para').is(':checked')) {
			$('select#person-wchc').parent().hide();
		} else if (this.value > 5) {
			$('select#person-wchc').parent().hide();
		} else {
			$('select#person-wchc').parent().show();
		}
	});
	
	$('input#person-is-para').trigger('change');
	$('select#person-ptt-class').trigger('change');
});
	
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

function _setHint(input, hint) {
	$.each(input, function(index, value) {
		if ($(value).val().length == 0) {
			$(value).val(hint);
			$(value).addClass('hint');
		}
	});

	input.focus(function() {
		if ($(this).hasClass('hint')) {
			$(this).removeClass('hint');
			$(this).val('');
		}
	});

	input.blur(function() {
		if ($(this).val().length == 0) {
			$(this).val(hint);
			$(this).addClass('hint');
		}
	});
}

$(document).ready(function() {
   _setHint($('#person-phone'), 'e.g. +4366412345678');

	// On submit clear the hint
	$('form').submit(function() {
		$('input.hint').val('');
		
		$('#person-phone').val($('#person-phone').val().replace('/ /g', ""));
		
		return true;
	});
	
});

<?php
	$this->Html->scriptEnd();
?>
	

<?php
	$isParticipant = $current_user['group_id'] == GroupsTable::getParticipantId();
	$isTourOperator = $current_user['group_id'] == GroupsTable::getTourOperatorId();
	$mayChangeDOB = 
		!$isParticipant &&
		empty($registration['participant']['double_partner_id']) &&
		empty($registration['participant']['mixed_partner_id'])
	;
?>

<div class="registrations form">
<?php echo $this->Form->create($registration);?>
	<fieldset>
 		<legend><?php echo __d('user', 'Edit Registration'); ?></legend>
	<?php
		echo $this->Form->control('id', array('type' => 'hidden'));
		echo $this->Form->control('tournament_id', array('type' => 'hidden'));

		echo $this->Form->control('person.id', array('type' => 'hidden'));
		echo $this->Form->control('person.user_id', array('type' => 'hidden'));
		// echo $this->Form->control('person.sex', array('type' => 'hidden'));
		// echo $this->Form->control('person.dob', array('type' => 'hidden'));

		echo $this->Form->control('participant.id', array('type' => 'hidden'));

		echo $this->Form->control('participant.cancelled', array('type' => 'hidden'));
		echo $this->Form->control('participant.team_cancelled', array('type' => 'hidden'));

		if ($isParticipant) {
			echo $this->Form->control('person.display_name', array(
				'label' => __d('user', 'Person'), 
				'readonly' => 'readonly'
			));

			echo $this->Form->control('person.first_name', array('type' => 'hidden'));
			echo $this->Form->control('person.last_name', array('type' => 'hidden'));
			echo $this->Form->control('person.nation_id', array('type' => 'hidden'));
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
			
			echo $this->Form->control('person.nation_id', array(
				'label' => __d('user', 'Association'),
				'options' => $nations,
				'empty' => false
			));
		}
			
		if ($registration['type_id'] == TypesTable::getPlayerId()) {
			// Players may not be changed to something else.
			// That simplifies dealing with changed partners
			echo $this->Form->control('type_id', array('type' => 'hidden'));
			echo $this->Form->control('type.description', array('label' => __d('user', 'Type'), 'readonly' => 'readonly'));
		} else if (false && $this->request->getSession()->check('Types.id') && $registration['type_id'] == $this->request->getSession()->read('Types.id')) {
			// If there is a filter for a type (e.g. media officer) don't change it.
			echo $this->Form->control('type_id', array('type' => 'hidden'));
			echo $this->Form->control('type.description', array('label' => __d('user', 'Type'), 'readonly' => 'readonly'));
		} else if (count($types) <= 1) {
			// If there are no types to choose from
			echo $this->Form->control('type_id', array('type' => 'hidden'));
			echo $this->Form->control('type.description', array('label' => __('Function'), 'readonly' => 'readonly'));
		} else {
			// Let the user select the type
			echo $this->Form->control('type_id', array(
				'label' => __d('user', 'Type'),
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
			echo $this->Form->control('person.sex', array(
				'type' => 'select', 
				'empty' => __d('user', 'Select gender'),
				'options' =>array('M' => __d('user', 'Man'), 'F' => __d('user', 'Woman')),
				'readonly' => true,
				'id' => false
			));
		}
		
		// DOB
		if ($registration['type_id'] != TypesTable::getPlayerId()) {
			echo $this->Form->control('person.dob', array('type' => 'hidden'));			
		} else if (!$mayChangeDOB) {
			echo $this->Form->control('person.dob', array('type' => 'hidden'));
			echo $this->Form->control('person.dob', array(
				'name' => false,
				'type' => 'text',
				'label' => __d('user', 'Date Born'),
				'readonly' => true,
				// 'value' => $registration['person']['dob']
			));
		} else {
			echo $this->Form->control('person.dob', array(
				'type' => 'date',
				'dateFormat' => 'YMD',
				'separator' => '<span>-</span>',
				'empty' => [
					'year' => __('Year'), 
					'month' => __('Month'), 
					'day' => __('Day')
				],
				'required' => 'required',
				'label' => __('Date Born'),
				'maxYear' => date('Y'),
				'minYear' => date('Y') - 120
			));		
		}

		// PTT
		if (!empty($havePara) || $registration['person']['is_para']) {
			echo $this->Form->control('person.is_para', array(
				'label' => __('Paralympic athlete'),
				'type' => 'checkbox'
			));

			echo $this->Form->control('person.ptt_class', array(
				'label' => 'ITTF paralympic classification', 
				'type' => 'select',
				'options' => 
					[0 => __('Select your ITTF paralympic classification')] +
					[-1 => __('Need ITTF paralympic classification')] +
					Hash::combine(range(1, 10), '{n}', '{n}'),
				'empty' => false,
			));

if (false) {
			echo $this->Form->control('person.wchc', array(
				'label' => __('Wheelchair Required'),
				'type' => 'select',
				'options' => [
					0 => __('Wheel chair not required'),
					1 => __('Wheel chair completely'),
					2 => __('Wheel char ramp')
				],
				'empty' => false // __('Select when a wheel chair is required')
			));
} else {
			
			echo $this->Form->control('person.wchc', array(
				'type' => 'hidden',
				'value' => 0
			));
}

			echo $this->Form->control('participant.ptt_nonpara', array(
				'type' => 'checkbox',
				'label' => __('Want to participate in able-bodied singles'),
			));
		}

		echo '<div id="participant" style="' . ($registration['type_id'] == TypesTable::getPlayerId() ? 'display:block;' : 'display:none;') . '">';
			if ($hasRootPrivileges)
				echo $this->Form->control('participant.start_no', array('empty' => true));

			echo '<div id="ParticipantSingle" style="padding:0;' . (count($competitions['singles']) ? 'display:block' : 'display:none') . '">';
				if ($isParticipant) {
					echo $this->Form->control('participant.single_id', array(
						'type' => 'hidden'
					));
					echo $this->Form->control('participant.single.description', array(
						'required' => false,
						'readonly' => 'readonly', 
						'label' => __d('user', 'Single')
					));
				} else {
					echo $this->Form->control('participant.single_id', array(
						'div' => 'singles', 
						'empty' => __d('user', 'Not Playing Singles'),
						'options' => $competitions['singles']
					));
				}
			echo '</div>';

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
					'label' => __d('user', 'Double')
				));

				echo $this->Form->control('participant.double_id', array(
					'div' => 'doubles', 
					'empty' => $isParticipant ? false : __d('user', 'Not Playing Doubles'),
					'options' => $competitions['doubles'],
					'label' => __d('user', 'Double Partner Category'),
					'onchange' => 'onChange("' . Router::url(['action' => 'onChangeDouble'], true) . '", onChangeDouble);'
				));

				if ( !$hasRootPrivileges && $tournament['modify_before'] < date('Y-m-d') && 
					 RegistrationsTable::isDoublePartnerConfirmed($registration)) {
					echo $this->Form->control('participant.double_partner_id', array('div' => false, 'type' => 'hidden'));
					echo $this->Form->control('participant.double_partner.person.display_name', array(
						'div' => array('id' => 'ParticipantDoublePartner'),
						'readonly' => 'readonly',
						'label' => __d('user', 'Double Partner')
					));
				} else {
					echo $this->Form->control('participant.double_partner_id', array(
						'div' => array(
							'id' => 'ParticipantDoublePartner', 
							'style' => (empty($registration['participant']['double_id']) ? '"display:none"' : '"display:block"')
						), 
						'empty' => __d('user', 'Partner wanted'), 
						'options' => $double_partner
					));
				}
				if ($hasRootPrivileges) {
					echo $this->Form->control('participant.double_partner_confirmed', array(
						'type' => 'checkbox',
						'label' => __d('user', 'Partner confirmed')
					));
				}
			echo '</div>';

			$style = 'display:block';
			if (count($competitions['mixed']) == 0)
				$style = 'display:none';
			if ($isParticipant && empty($registration['participant']['mixed_id']))
				$style = 'display:none';
			if ($isParticipant && $registration['participant']['mixed_cancelled'] != 0)
				$style = 'display:none';
			
			echo '<div id="ParticipantMixed" style="padding:0;' . $style . '">';
					echo $this->Form->control('participant.mixed.description', array('readonly' => 'readonly', 'label' => __d('user', 'Mixed')));

				echo $this->Form->control('participant.mixed_id', array(
					'div' => 'mixed', 
					'empty' => $isParticipant ? false : __d('user', 'Not Playing Mixed'),
					'options' => $competitions['mixed'],
					'label' => __d('user', 'Mixed Partner Category'),
					'onchange' => 'onChange("' . Router::url(['action' => 'onChangeMixed'], true) . '", onChangeMixed);'
				));

				if (!$hasRootPrivileges && $tournament['modify_before'] < date('Y-m-d') && 
						RegistrationsTable::isMixedPartnerConfirmed($registration)) {
					echo $this->Form->control('participant.mixed_partner_id', array('div' => false, 'type' => 'hidden'));
					echo $this->Form->control('participant.mixed_partner.person.display_name', array(
						'div' => array('id' => 'ParticipantMixedPartner'),
						'readonly' => 'readonly',
						'label' => __d('user', 'Mixed Partner')
					));
				} else {
					echo $this->Form->control('participant.mixed_partner_id', array(
						'div' => array(
							'id' => 'ParticipantMixedPartner', 
							'style' => (empty($registration['participant']['mixed_id']) ? '"display:none"' : '"display:block"')
						), 
						'empty' => __d('user', 'Partner wanted'), 
						'options' => $mixed_partner
					));
				}
				if ($hasRootPrivileges) {
					echo $this->Form->control('participant.mixed_partner_confirmed', array(
						'type' => 'checkbox',
						'label' => __d('user', 'Partner confirmed')
					));
				}
			echo '</div>';

			echo '<div id="ParticipantTeam" style=' . (count($competitions['teams']) ? '"display:block"' : '"display:none"') . '>';
				echo $this->Form->control('participant.team_id', array(
					'div' => 'teams', 
					'empty' => $isParticipant ? false : __d('user', 'Not Playing Teams'),
					'options' => $competitions['teams']
				));
			echo '</div>';
			
			echo $this->Form->control('person.email', array(
				'label' => __d('user', 'Email')
			));
			
			echo $this->Form->control('person.phone', array(
				'label' => __d('user', 'Phone')
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
		<legend><?php echo __d('user', 'Items');?></legend>
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
				echo '<li>' . $this->Html->link(__d('user', 'List Registrations'), array('action' => 'index')) . '</li>';

			if ($Acl->check($current_user, 'Registrations/list_partner_wanted'))
				echo '<li>' . $this->Html->link(__d('user', 'List Partner Wanted'), array('action' => 'list_partner_wanted')) . '</li>';
		?>
	</ul>
<?php $this->end(); ?>
