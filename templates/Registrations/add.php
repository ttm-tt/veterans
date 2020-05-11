<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use App\Model\Table\TypesTable;
use Cake\Routing\Router;
?>

<?php
	$this->Html->scriptStart(['block' => true]);
?>

function onChange(url, callback) {
	$.post(
		url, 
		$('form').serialize(), 
		function(data) {callback(data);},
		'json'
	);		
}

function onChangePerson(data) {
	$.each(data['Competitions'], function(widget, values) {
		var map = {
			'singles' : 'participant-single',
			'doubles' : 'participant-double',
			'mixed'   : 'participant-mixed',
			'teams'   : 'participant-team'
		};

		var options = '<option value="">' + <?php echo '"' . __('Select Event') . '"' ?> + '</option>';

		$.each(values, function(id, text) {
			options += '<option value="' + id + '">' + text + '</option>';
		});

		$('#' + map[widget] + '-id').html(options);
		$('#' + map[widget]).css('display', $.isEmptyObject(values) ? 'none' : 'block');
	});

	var options = '<option value="" selected="selected">' + <?php echo '"' . __('Select Function') . '"' ?> + '</option>';
	$.each(data['Types'], function(id, text) {
		options += '<option value="' + id + '">' + text + '</option>';
	});

	// Set allowed functions	
	$('#type-id').html(options);

	// Hide competitions and set to "select one"
	$('#participants').css('display', 'none');
	$('#participant-single-id').val("");
	$('#participant-double-id').val("");
	$('#participant-double-partner-id').val("");
	$('#participant-mixed-id').val("");
	$('#participant-mixed-partner-id').val("");
	$('#participant-team-id').val("");
}


function onChangeDouble(data) {
	var options = '<option value="">' + <?php echo '"' . __('Partner wanted') . '"' ?> + '</option>';

	$.each(data, function(i, s) {
		var id = s.id;
		var value = s.display_name;
		options += '<option value="' + id + '">' + value + '</option>';
	});

	$('#participant-double-partner-id').html(options);
	$('#participant-double-partner').css('display', $('#participant-double-id').val() ? 'block' : 'none');
}


function onChangeMixed(data) {
	var options = '<option value="">' + <?php echo '"' . __('Partner wanted') . '"' ?> + '</option>';

	$.each(data, function(i, s) {
		var id = s.id;
		var value = s.display_name;
		options += '<option value="' + id + '">' + value + '</option>';
	});

	$('#participant-mixed-partner-id').html(options);
	$('#participant-mixed-partner').css('display', $('#participant-mixed-id').val() ? 'block' : 'none');
}

<?php
	$this->Html->scriptEnd();
?>
	

<div class="registrations form">
<?php echo $this->Form->create($registration);?>
	<fieldset>
 		<legend><?php echo __('Add Registration'); ?></legend>
	<?php
		echo $this->Form->control('tournament_id', array(
			'type' => 'hidden', 
			'value' => $this->request->getSession()->read('Tournaments.id')
		));
		echo $this->Form->control('person_id', array(
				'empty' => __('Select Person'), 
				'selected' => '',
				'onchange' => 'onChange("' . Router::url(['action' => 'onChangePerson'], true) . '", onChangePerson);'					
		));
		echo $this->Form->control('type_id', array(
				'label' => __('Function'),
				'empty' => __('Select Function'), 
				'onchange' => 
					'$(this).val() == ' . TypesTable::getPlayerId() . '? ' .
					'$("#participant").show() : $("#participant").hide(); '
		));
		
		echo '<div id="participant" style="display:none">';
			echo '<div id="participant-single" style="display:none">';
				echo $this->Form->control('participant.single_id', array('empty'));
			echo '</div>';
			echo '<div id="participant-double" style="display:none">';
				echo $this->Form->control('participant.double_id', array(
					'empty',
					'onchange' => 'onChange("' . Router::url(['action' => 'onChangeDouble'], true) . '", onChangeDouble);'
				));
				echo $this->Form->control('participant.double_partner_id', array(
					'empty' => __('Select Partner')
				));
			echo '</div>';
			echo '<div id="participant-mixed" style="display:none">';
				echo $this->Form->control('participant.mixed_id', array(
					'empty',
					'onchange' => 'onChange("' . Router::url(['action' => 'onChangeMixed'], true) . '", onChangeMixed);'					
				));
				echo $this->Form->control('participant.mixed_partner_id', array(
					'empty' => __('Select Partner')
				));
			echo '</div>';
			echo '<div id="participant-team" style="display:none">';
				echo $this->Form->control('participant.team_id', array('empty'));
			echo '</div>';
		echo '</div>';
	?>
	</fieldset>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>

		<li><?php echo $this->Html->link(__('List Registrations'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List People'), array('controller' => 'people', 'action' => 'index')); ?> </li>
	</ul>
<?php $this->end(); ?>
