<?php
use Cake\Core\Configure;
?>

<?php
	echo $this->Html->css('intlTelInput');
	echo $this->Html->css('intlTelInputUser');
	echo $this->Html->script('intlTelInput');
?>
<?php $this->Html->scriptStart(array('block' => true)); ?>

$(document).ready(function() {
	$('#phone').intlTelInput( {
		preferredCountries: [],
		nationalMode: true,
		utilsScript: "<?php echo $this->Url->assetUrl('intlTelInputUtils', array('pathPrefix' => Configure::read('App.jsBaseUrl'), 'ext' => '.js'));;?>",
		initialCountry: "<?php echo $countryCode;?>",
		onlyCountries: [<?php echo '"' . implode('", "', array_values($countryCodes)) . '"';?>]
	} );
	
	$('#phone').intlTelInput("setNumber", $('#InvoiceAddressPhone').val());
	
	$('#phone').blur(function() {
		var number = $('#phone').intlTelInput("getNumber");
		$('#InvoiceAddressPhone').val(number);
	});
	
	$('#InvoiceAddressCountryId').change(function() {
		var id = $('#InvoiceAddressCountryId').val();
		var codes = <?php echo json_encode($countryCodes);?>;
		var name = codes[id];
		$('#phone').intlTelInput("setCountry", name);
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

<div class="order form">

<?php echo $this->Form->create($order);?>
<fieldset>
	<legend><?php echo __('Edit Invoice Address in Order {0}', $order['invoice']);?></legend>

	<?php
		echo $this->Form->control('id', array('type' => 'hidden'));
		echo $this->Form->control('user_id', array('type' => 'hidden'));
		echo $this->Form->control('invoice_address.id', array('type' => 'hidden'));
		echo $this->Form->control('invoice_address.order_id', array('type' => 'hidden'));
		echo $this->Form->control('invoice_address.type', array('type' => 'hidden'));
		
		echo $this->Form->control('invoice_address.title', array(
			'label' => __('Title'),
			'type' => 'select',
			'options' => array('Mr' => __('Mr'), 'Mrs' => __('Mrs')),
			'empty' => __('Please select')
		));
		echo $this->Form->control('invoice_address.first_name', array(
			'label' => __('Given name'),
			'onBlur' => 'this.value = camelizeName(this.value);'
		));
		echo $this->Form->control('invoice_address.last_name', array(
			'label' => __('Family name'),
			// 'onBlur' => 'this.value = $.trim(this.value.toUpperCasse());'
			'onBlur' => 'if ($("#invoice-address-title").val() !== "") this.value = $.trim(this.value).toUpperCase();'
		));
		echo $this->Form->control('invoice_address.street', array(
			'label' => __('Street'),
		));
		echo $this->Form->control('invoice_address.zip_code', array(
			'label' => __('Postal code'),
		));
		echo $this->Form->control('invoice_address.city', array(
			'label' => __('City'),
		));
		echo $this->Form->control('invoice_address.country_id', array(
			'label' => __('Country'),
			'required' => 'required',
			'options' => $countries,
			'empty' => __('Select Country')
		));
		echo $this->Form->control('email', array(
			'label' => __('Email'),
			'required' => 'required'
		));
		
		echo $this->Form->control('invoice_address.phone', array(
			'type' => 'hidden'
		));
		
		$this->Form->unlockField('invoice_address.phone');
		
		echo $this->Form->control('phone', array(
			'label' => __('Phone'),
			'type' => 'tel',
			'id' => 'phone',
			'name' => false
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
		<li><?php echo $this->Html->link(__('List Orders'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
