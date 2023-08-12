<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use App\Model\Table\TypesTable;

use Cake\Utility\Hash;
?>	
<?php $this->Html->scriptStart(array('block' => true)); ?>

$(document).ready(function() {
	// Show / hide para settings
	$('select#person-ptt-class').parent().hide();
	$('select#person-wchc').parent().hide();
	
	$('input#person-is-para').change(function() {
		if (this.checked)
			$('select#person-ptt-class').parent().show();
		else {
			$('select#person-ptt-class').parent().hide();
			$('select#person-wchc').parent().hide();
		}
	});
	
	$('select#person-ptt-class').change(function() {
		if (!$('input#person-is-para').is(':checked'))
			$('select#person-wchc').parent().hide();
		else if (this.value > 5)
			$('select#person-wchc').parent().hide();
		else
			$('select#person-wchc').parent().show();
	});
	
	$('input#person-is-para').trigger('change');
	$('select#person-ptt-class').trigger('change');
});
	
function onChangeType() {
	var type_id = $('#type-id').val();
	if (type_id == <?php echo TypesTable::getPlayerId();?>) {
		$('#person-dob').parent().show();
		$('#person-username').parent().show();
		$('#person-email').parent().show();
		$('#person-phone').parent().show();
		$('fieldset#items').show();
	} else if (type_id == <?php echo TypesTable::getAccId();?>) {
		$('#person-dob').parent().hide();
		$('#peson-username').parent().show();
		$('#person-email').parent().hide();
		$('#person-phone').parent().hide();	
		$('fieldset#items').show();
	} else {
		$('#person-dob').parent().hide();
		$('#person-username').parent().hide();
		$('#person-email').parent().hide();
		$('#person-phone').parent().hide();	
		$('fieldset#items').hide();
	}
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
		
		$('#PersonPhone').val($('#PersonPhone').val().replace('/ /g', ""));
		
		return true;
	});
	
});

<?php $this->Html->scriptEnd(); ?>

<div class="registrations form">
<?php echo $this->Form->create($registration);?>
	<fieldset id='person'>
 		<legend><?php echo __d('user', 'Add Participant'); ?></legend>
		<?php
			echo $this->Form->control('type_id', array(
				'options' => $types,
				'empty' => __d('user', 'Select Type'),
				'label' => __d('user', 'Type'),
				'default' => ($this->request->getSession()->check('Types.id') ? $this->request->getSession()->read('Types.id') : false),
				'onchange' => 'onChangeType(); return false;'
			));
			
			echo $this->Form->control('person.first_name', array(
				'label' => __d('user', 'Given Name'), 
				'onBlur' => 'this.value = camelizeName(this.value);'
			));
			
			echo $this->Form->control('person.last_name', array(
				'label' => __d('user', 'Family Name'), 
				'onBlur' => 'this.value = $.trim(this.value.toUpperCase());'
			));
			
			if ($hasRootPrivileges) {
				echo $this->Form->control('person.display_name', array('onBlur' => 'this.value = $.trim(this.value);'));
			}

			echo $this->Form->control('person.sex', array(
				'type' => 'select', 
				'empty' => __d('user', 'Select gender'),
				'options' =>array('M' => __d('user', 'Man'), 'F' => __d('user', 'Woman')
			)));
			
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
				'minYear' => date('Y') - 120,
				'templateVars' => ['id' => 'person-dob']
			));

			if (!empty($havePara)) {
				echo $this->Form->control('person.is_para', array(
					'label' => __('Paralympic athlete'),
					'type' => 'checkbox',
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

				echo $this->Form->control('person.wchc', array(
					'label' => __('Wheelchair Required'),
					'type' => 'select',
				'options' => [
					0 => __('Wheel chair not required'),
					1 => __('Wheel chair completely'),
					2 => __('Wheel char ramp')
				],
				'empty' => __('Select when a wheel chair is required')
				));
			}

			$options = array();
			if ($this->request->getSession()->check('Nations.id'))
				$options = array('default' => $this->request->getSession()->read('Nations.id'));
			else
				$options = array('empty' => __d('user', 'Select Association'));
			$options['label'] = __d('user', 'Association');
			echo $this->Form->control('person.nation_id', $options);
			
			if ($hasRootPrivileges)
				echo $this->Form->control('person.username', array(
					'required' => 'required'
				));
			else
				echo $this->Form->control('person.username', array(
					'value' => $current_user['username'],
					'type' => 'hidden'
				));
			
			echo $this->Form->control('person.email');
			echo $this->Form->control('person.phone');

		?>
	</fieldset>	
	<fieldset id='items'>
		<legend><?php echo __d('user', 'Items');?></legend>
		<?php
			foreach ($articles as $article) {
				if (!$article['visible'])
					continue;
				
				echo $this->Form->control('Article.' . $article['name'], array(
					'label' => $article['description'],
					'type' => 'checkbox'
				));
			}
		?>
	</fieldset>
<?php 
	echo $this->element('savecancel', array('continue' => __d('user', 'Save & Cont.')));
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__d('user', 'List Registrations'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
