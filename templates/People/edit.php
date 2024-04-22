<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use Cake\Utility\Hash;
?>

<?php $this->Html->scriptStart(array('block' => true)); ?>
$(document).ready(function() {
	// Show / hide para settings
	$('select#ptt-class').parent().hide();
	$('select#wchc').parent().hide();
	
	$('input#is-para').change(function() {
		if (this.checked) {
			$('select#ptt-class').parent().show();
		} else {
			$('select#ptt-class').parent().hide();
		}
	});
	
	$('select#ptt-class').change(function() {
		if (this.value <= 5)
			$('select#wchc').parent().show();
		else
			$('select#wchc').parent().hide();
	});

	$('input#is-para').trigger('change');
	$('select#ptt-class').trigger('change');
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

<?php $this->Html->scriptEnd(); ?>

<div class="people form">
<?php echo $this->Form->create($person);?>
	<fieldset>
 		<legend><?php echo __('Edit Person'); ?></legend>
	<?php
		$sex = array('F' => __('Woman'), 'M' => __('Man'));

		echo $this->Form->control('id', array('type' => 'hidden'));
		echo $this->Form->control('user_id', array('type' => 'hidden'));
		echo $this->Form->control('first_name', array(
			'label' => __('Given Name'), 
			'onBlur' => 'this.value = camelizeName(this.value);'
        ));
		echo $this->Form->control('last_name', array(
			'label' => __('Family Name'), 
			'onBlur' => 'this.value = $.trim(this.value.toUpperCase());'
        ));
		if ($hasRootPrivileges) {
			echo $this->Form->control('display_name', array(
                'onBlur' => 'this.value = $.trim(this.value);'
            ));
		} else{
			echo $this->Form->control('display_name', array('type' => 'hidden'));
		}

		if ($hasRootPrivileges)
			echo $this->Form->control('username');
		else if (!empty($person['user_id']))
			echo $this->Form->control('username', array('readonly' => 'readonly'));

		if ($hasRootPrivileges) {
			echo $this->Form->control('sex', array(
				'type' => 'select',
				'options' => $sex
			));
		} else {
			echo $this->Form->control('sex', array(
				'value' => $sex[$person['sex']],
				'type' => 'text',
				'label' => __('Sex'),
				'readonly' => 'readonly',
				'name' => false
			));
		}
		
		if (!$hasRootPrivileges) {
			echo $this->Form->control('dob', array('type' => 'hidden'));
		} else if (strpos($person['dob'], '-00-00') > 0) {
			echo $this->Form->control('dob', array(
				'type' => 'text',
				'label' => __('Date born')
			));			
		} else {
			echo $this->Form->control('dob', array(
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

		if ($hasRootPrivileges) {
			echo $this->Form->control('extern_id', array('label' => 'Extern ID', 'type' => 'text'));
		} else {
			echo $this->Form->control('extern_id', array('label' => 'Extern ID', 'type' => 'hidden'));			
		}

		if (!empty($havePara) || ($person['ptt_class'] ?: 0) > 0) {
			echo $this->Form->control('is_para', array(
				'label' => __('Paralympic athlete'),
				'type' => 'checkbox',
			));
			
			echo $this->Form->control('ptt_class', array(
				'label' => 'ITTF paralympic classification', 
				'type' => 'select',
				'options' => Hash::combine(range(1, 10), '{n}', '{n}'),
				'empty' => __('Select your ITTF paralympic classification'),
				'required' => true,
			));

if (false) {			
			echo $this->Form->control('wchc', array(
				'label' => __('Wheelchair Required'),
				'type' => 'select',
				'options' => [
					1 => __('Wheel chair completely'),
					2 => __('Wheel char ramp')
				],
				'empty' => __('Select when a wheel chair is required'),
				'required' => true,
			));
} else {
			echo $this->Form->control('wchc', array(
				'type' => 'hidden',
				'value' => 0
			));	
}
		}

		if ($hasRootPrivileges) {
			echo $this->Form->control('nation_id', array(
				'label' => __('Association')
			));
		} else {
			echo $this->Form->control('nation_id', array(
				'value' => $nations[$person['nation_id']],
				'type' => 'text',
				'label' => __('Association'),
				'readonly' => 'readonly',
				'name' => false
			));
		}

		echo $this->Form->control('email');
		echo $this->Form->control('phone');
	?>
	</fieldset>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php if ($Acl->check($current_user, 'People/index'))
			echo '<li>' . $this->Html->link(__('List People'), array('action' => 'index')) . '</li>';
		?>
	</ul>
<?php $this->end(); ?>
