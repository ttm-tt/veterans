<?php $this->Html->scriptStart(array('block' => true)); ?>
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
	_setHint($('#phone'), 'e.g. +4366412345678');

	// On submit clear the hint
	$('form').submit(function() {
		$('input.hint').val('');
		
		$('#phone').val($('#phone').val().replace('/ /g', ""));
		
		return true;
	});
});
<?php $this->Html->scriptEnd(); ?>

<?php
	$invoice = $order['invoice'];
	if (empty($invoice))
		$invoice = '#' . $order['id'];
?>
<div class="order form">

<?php echo $this->Form->create(null);?>
<fieldset>
	<legend><?php echo __('Edit Person in Order {0}', $invoice);?></legend>

	<?php
		echo $this->Form->control('first_name', array(
			'label' => __('Given Name'), 
			'onBlur' => 'this.value = $.trim(this.value);'
		));

		echo $this->Form->control('last_name', array(
			'label' => __('Family Name'), 
			'onBlur' => 'this.value = $.trim(this.value.toUpperCase());'
		));

		echo $this->Form->control('sex', array(
			'type' => 'select', 
			'empty' => __('Select gender'),
			'options' =>array('M' => __('Man'), 'F' => __('Woman')
		)));
		
		if ($person['type'] === 'PLA') {
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
		
		echo $this->Form->control('nation_id', array(
			'label' => __('Association'),
			'type' => 'select',
			'options' => $nations,
			'empty' => __('Select Association')
		));

		if ($person['type'] === 'PLA') {
			echo $this->Form->control('email', array(
				'type' => 'text',
				'label' => __('Email')
			));

			echo $this->Form->control('phone', array(
				'type' => 'text',
				'label' => __('Phone'),
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
		<li><?php echo $this->Html->link(__('List Orders'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
