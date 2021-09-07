<?php
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
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

<?php
	$db = ConnectionManager::get('default');
	$addressSchema = 
		$db
			->getSchemaCollection()
			->describe('shop_order_addresses')
	;
	$orderSchema = 
		$db
			->getSchemaCollection()
			->describe('shop_orders')
	;
?>

<?php $this->Html->scriptStart(array('block' => true)); ?>

$(document).ready(function() {
	try {
		$('#phoneinput').intlTelInput( {
			customContainer: 'cell small-12 medium-9 large-6',
			preferredCountries: [],
			separateDialCode: true,
			utilsScript: "<?php echo $this->Url->assetUrl('intlTelInputUtils', array('pathPrefix' => Configure::read('App.jsBaseUrl'), 'ext' => '.js'));;?>",
			initialCountry: "<?php echo $countryCode;?>",
			onlyCountries: [<?php echo '"' . implode('", "', array_values($countryCodes)) . '"';?>]
		} );

		$('#phoneinput').intlTelInput("setNumber", $('#phone').val());
	} catch (err) {
	
	}
	
	$('#phoneinput').blur(function() {
		var number = $('#phoneinput').intlTelInput("getNumber");
		$('#phone').val(number);
	});
	
	$('#country-id').change(function() {
		var id = $('#country-id').val();
		var codes = <?php echo json_encode($countryCodes);?>;
		var name = codes[id];
		$('#phoneinput').intlTelInput("setCountry", name);
	});
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

<div class="order address form">
	<?php echo $this->Wizard->create(null);?>
	<?php echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Billing Address');?></h2>
	<div class="hint cell">
	<?php 
		echo '<p>';
		echo __d('user', 'Enter here your billing address.');
		echo '</p>';
		
		echo '<p>';
		echo __d('user', 'All fields are required.');
		echo '</p>';
		
		echo '<p>';
		echo __d('user', 'Please make sure you enter a valid email address.');
		echo '</p>';
	?>

	</div>
	<table>
	</table>
	<?php
		echo $this->Form->control('type', array(
			'type' => 'hidden',
			'value' => 'P'
		));
		echo $this->Form->control('title', array(
			'label' => __d('user', 'Title'),
			'required' => 'required',
			'type' => 'select',
			'options' => array('Mr' => __d('user', 'Mr'), 'Mrs' => __d('user', 'Mrs')),
			'empty' => __d('user', 'Please select')
		));
		echo $this->Form->control('first_name', array(
			'label' => __d('user', 'Given name'),
			'required' => 'required',
			'onBlur' => 'this.value = camelizeName(this.value);',
			'maxLength' => $addressSchema->getColumn('first_name')['length']
		));
		echo $this->Form->control('last_name', array(
			'label' => __d('user', 'Family name'),
			'required' => 'required',
			// 'onBlur' => 'this.value = $.trim(this.value.toUpperCasse());'
			'onBlur' => 'this.value = $.trim(this.value).toUpperCase();',
			'maxLength' => $addressSchema->getColumn('last_name')['length']
		));
		echo $this->Form->control('street', array(
			'label' => __d('user', 'Street'),
			'required' => 'required',
			'maxLength' => $addressSchema->getColumn('street')['length']
		));
		echo $this->Form->control('zip_code', array(
			'label' => __d('user', 'Postal code'),
			'required' => 'required',
			'maxLength' => $addressSchema->getColumn('zip_code')['length']
		));
		echo $this->Form->control('city', array(
			'label' => __d('user', 'City'),
			'required' => 'required',
			'maxLength' => $addressSchema->getColumn('city')['length']
		));
		echo $this->Form->control('country_id', array(
			'label' => __d('user', 'Country'),
			'required' => 'required',
			'options' => $countries,
			'empty' => __d('user', 'Select Country')
		));
		echo $this->Form->control('email', array(
			'label' => __d('user', 'Email'),
			'required' => 'required',
			'maxLength' => $orderSchema->getColumn('email')['length']
		));

		echo $this->Form->control('phone', array(
			'type' => 'hidden'
		));
		
		$this->Form->unlockField('phone');
		
		echo $this->Form->control('phoneinput', array(
			'label' => __('Phone'),
			'type' => 'tel',
			'name' => false
		));
	?>
	<?php echo $this->element('shop_footer'); ?>
	<?php echo $this->Form->end(); ?>
</div>

