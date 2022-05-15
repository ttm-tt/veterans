<?php
use Cake\Core\Configure;
use Cake\Utility\Hash;
?>

<?php
	echo $this->Html->css('intlTelInput');
	echo $this->Html->css('intlTelInputUser');
	echo $this->Html->script('intlTelInput');
?>

<?php
	$countryCode = strtolower($countryCode);
	foreach ($countryCodes as $id => $val) {
		$countryCodes[$id] = strtolower($val);
	}
?>

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
	// Show / hide para settings
	$('select#ptt-class').parent().hide();
	$('select#wchc').parent().hide();
	
	$('input#is_para').change(function() {
		if (this.checked)
			$('select#ptt-class').parent().show();
		else
			$('select#ptt-class').parent().hide();
	});
	
	$('select#ptt-class').change(function() {
		if (this.value <= 5)
			$('select#wchc').parent().show();
		else
			$('select#wchc').parent().hide();
	});
	
	// intlTelInput may throw an exception which we can't handle
	try {
		$('#phoneinput').intlTelInput( {
			customContainer: 'cell small-12 medium-9 large-6',
			preferredCountries: [],
			separateDialCode: true,
			utilsScript: "<?php echo $this->Url->assetUrl('intlTelInputUtils', array(
				'pathPrefix' => Configure::read('App.jsBaseUrl'), 
				'ext' => '.js'
			));?>",
			initialCountry: "<?php echo $countryCode;?>",
			onlyCountries: [<?php echo '"' . implode('", "', array_values($countryCodes)) . '"';?>]
<?php 
/*	
			geoIpLookup: function(callback) {
			  $.get('http://ipinfo.io', function() {}, "jsonp").always(function(resp) {
					var countryCode = (resp && resp.country) ? resp.country : "";
					callback(countryCode);
				  });	
			  }, 
 */
?>

		} );

		$('#phoneinput').intlTelInput("setNumber", $('#phone').val());	
	} catch (err) {
	
	}
	
	$('#phoneinput').blur(function() {
		var number = $('#phoneinput').intlTelInput("getNumber");
		$('#phone').val(number);
	});
	
	// _setHint($('#phone'), 'e.g. +4366412345678');
	
	// TODO: onchange nation-id update of initialCountry in phone number
	
	// On submit clear the hint
	$('form').submit(function() {
		$('input.hint').val('');
		
		// $('#phone').val($('#phone').val().replace('/ /g', ""));
		
		return true;
	});
	
	onChangeType($('#type'));
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

function onChangeType(cb) {
	if (cb.val() == 'PLA') {
		$('#playerdata').show();		
	} else {
		$('#playerdata').hide();
	}
}
<?php $this->Html->scriptEnd(); ?>

<div class="people form">
<?php echo $this->Form->create(null);?>
	<fieldset>
 		<legend><?php echo __d('user', 'Add Player or Accompanying Person'); ?></legend>
		<div class="hint cell">
		<?php			
			echo '<p>';
			echo __d('user', 'Enter here the data for players or accompanying persons.');
			echo '</p>';
			
			echo '<p>';
			echo __d('user', 'For players the birthday is required.');
			echo '</p>';
			
			echo '<p>';
			echo __d('user', 'For players you may add email and a phone number of a cell phone.');
			echo '</p>';
			
			echo '<p>';
			echo __d('user', 'If you enter an email address, this player will also receive the notifications when he is requested as a double partner or a double partner is chosen for him.');
			echo '</p>';
			
			echo '<p>';
			echo __d('user', 'If you enter a phone number the player will receive free SMS notifications about schedules and results of his matches, if the organizer chooses to do so.');
			echo '</p>';
			
			echo '<p>';
			echo __d('user', 'By entering email address or phone number you must agree to the terms and conditions.'); 
			echo '</p>';
			
			echo '<p>';
			echo __d('user', 'You can add or change email address and phone number later, too.');
			echo '</p>';
			
			echo '<p>';
			echo __d('user', 'If you are a Paralympic Athlete flag the appropriate field and put your international classification.');
			echo '</p>';
		?>
		</div>
	<?php
		echo $this->Form->control('type', array(
			'lable' => __d('user', 'Type'),
			'type' => 'select',
			'options' => $types,
			'empty' => false,
			'required' => true,
			'onChange' => 'onChangeType($(this)); return false;'
		));

		echo $this->Form->control('first_name', array(
			'label' => __d('user', 'Given Name'), 
			'required' => true,
			'onBlur' => 'this.value = camelizeName(this.value);',
		));

		echo $this->Form->control('last_name', array(
			'label' => __d('user', 'Family Name'), 
			'required' => true,
			'onBlur' => 'this.value = $.trim(this.value.toUpperCase());'
		));

		echo $this->Form->control('sex', array(
			'label' => __d('user', 'Sex'),
			'type' => 'select', 
			'empty' => __d('user', 'Select gender'),
			'required' => true,
			'options' =>array('M' => __d('user', 'Man'), 'F' => __d('user', 'Woman')
		)));

		echo $this->Form->control('nation_id', array(
			'label' => __d('user', 'Association'),
			'type' => 'select',
			'options' => $nations,
			'empty' => __d('user', 'Select Association'),
			'required' => true
		));

		echo '<div id="playerdata">';
		echo $this->Form->control('dob', array(
			'type' => 'date',
			'dateFormat' => 'YMD',
			'minYear' => date('Y') - 120,
			'maxYear' => isset($maxYear) ? $maxYear : null,
			'empty' => [
				'year' => __('Year'), 
				'month' => __('Month'), 
				'day' => __('Day')
			],
			'required' => true,
			'label' => __d('user', 'Date Born'),
			'templates' => [
			]
		));
		
		if (!empty($variants['PLA'])) {
			foreach ($variants['PLA'] as $label => $options) {
				echo $this->Form->control('variant_id', array(
					'options' => $options,
					'label' => __d('user', $label),
					'required' => true
				));
			}
		}
		
		if (!empty($havePara)) {
			echo $this->Form->control('isPara', array(
				'label' => __('Paralympic athlete'),
				'type' => 'checkbox',
				'id' => 'is_para',
				'checked' => false,
			));
			
			echo $this->Form->control('ptt_class', array(
				'label' => 'ITTF paralympic classification', 
				'type' => 'select',
				'options' => Hash::combine(range(1, 10), '{n}', '{n}'),
				'empty' => __('Select your ITTF paralympic classification'),
				'required' => true,
			));
			
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
		}

		echo $this->Form->control('email', array(
			'type' => 'text',
			'label' => __d('user', 'Email')
		));
		
		echo $this->Form->control('phone', array(
			'type' => 'hidden'
		));
		
		$this->Form->unlockField('phone');

		echo $this->Form->control('phoneinput', array(
			'type' => 'tel',
			'label' => __d('user', 'Mobile'),
			'id' => 'phoneinput',
			'name' => false
		));

		echo $this->Form->control('privacy', array(
			'type' => 'checkbox',
			'checked' => false,
			'label' => '',
			'templateVars' => [
				'after' => sprintf(
					__d('user', 'I agree to the %s regarding email address and phone number'), 
					$this->Html->link(
						__d('user', 'privacy policy'), 
						array('controller' => 'pages', 'action' => 'players_privacy'), 
						array('target' => '_blank')
					)
				)
			],
			'templates' => [
				'inputContainer' => '<div class="grid-x input {{type}}{{required}}">{{content}}&nbsp;<p class="small-9 medium-6">{{after}}</p></div>',
			]
		));

		echo '</div>';
	?>
	</fieldset>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>
