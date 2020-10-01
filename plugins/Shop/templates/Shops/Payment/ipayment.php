<?php
use Cake\Routing\Router;
?>

<?php
	$this->Html->scriptStart(array('block' => true));
?>

function onCancel() {
	$('form#cancel').submit();
}
	

function onPrevious() {
	$('form#previous').submit();
}


function onPay() {
	// this.document.submit();
	$('div#processing').show();
	$('div#billing').hide();

	$.ajax({
		'type' : 'POST',
		'dataType' : 'json',
		'url'  : '<?php echo Router::url(array('action' => 'onPrepareCreditcard')); ?>',
		'success' : function(data) {
			if (typeof(data) !== 'string' || data === '') {
				$('div#processing h2').html("<?php echo __d('user', 'The payment could not be initiated. Please try again later.');?>");
			} else {
				$('#ipayment_session_id').val(data);

				$('form#ipayment').submit();
			}
		},
		'error' : function(xhr, status, error) {
			$('div#processing h2').html("<?php echo __d('user', 'The payment could not be initiated. Please try again later.');?>");
		}
	});
}

<?php
	$this->Html->scriptEnd();
?>

<div id="processing" class="order billing form" style="display:none">
	<h2>
		<?php echo __d('user', 'Please wait while your payment is being processed.');?>
		<br>
		<?php echo __d('user', 'This may take several minutes.');?>
	</h2>
</div>

<div id="billing" class="order billing form">
	<form id="ipayment" method="post" action="https://ipayment.de/merchant/99999/processor.php">
	<?php echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Billing Information');?></h2>
	<div class="hint">
	<?php
		echo __d('user', 'Select your credit card and enter name and address of the card holder.') . '<br>';
		echo __d('user', 'Payment will be processed in a secure way by {0}.', 'ipayment.de.') . '<br>';
	?>
	</div>
    <input type="hidden" name="silent" value="1">
	<input type="hidden" name="ipayment_session_id" id="ipayment_session_id" value="">
	<input type="hidden" name="return_paymentdata_details", value="1">

	<?php 
		echo $this->Form->control('amount', array(
			'label' => __d('user', 'Amount'),
			'type' => 'text',
			'readonly' => 'readonly',
			'value' => $amount . ' ' . $shopSettings['currency'],
		));
	?>
	<?php 
		echo $this->Form->control('cardtype', array(
			'label' => __d('user', 'Credit Card'),
			'type' => 'select',
			'empty' => __d('user', 'Select credit card type'),
			// 'name' => 'cc_typ',
			'options' => array('VisaCard' => 'VisaCard', 'MasterCard' => 'MasterCard')
		));
	?>
	<?php
		echo $this->Form->control('cardno', array(
			'label' => __d('user', 'Card no.'),
			'name' => 'cc_number',
			'type' => 'text'
		));
	?>
	<?php 
		echo $this->Form->control('cvv', array(
			'label' => __d('user', 'CVV code'),
			'name' => 'cc_checkcode',
			'type' => 'text',
			'after' => '<a href="http://www.cvvnumber.com/cvv.html" target="_blank" style="font-size:11px">What is my CVV code?</a>'
		));
	?>
	<div class="input select">
	<label><?php echo __d('user', 'Card expires');?></label>
	<?php 
		echo $this->Form->month('month', array(
			'label' => false,
			'div' => false,
			'name' => 'cc_expdate_month',
			'monthNames' => false,
			'style' => 'width: 19%;'
		));
	?>
	<span style="width: 2%;">&nbsp;/&nbsp;</span>
	<?php
		echo $this->Form->year('year', date('Y'), date('Y') + 10, array(
			'label' => false,
			'div' => false,
			'name' => 'cc_expdate_year',
			'style' => 'width: 19%;',
			'orderYear' => 'asc'
		));
	?>
	</div>
	<?php 
		echo $this->Form->control('name', array(
			'label' => __d('user', 'Card holder'),
			'name' => 'addr_name',
			'type' => 'text'
		));
	?>
	<?php
		echo $this->Form->control('street', array(
			'label' => __d('user', 'Street'),
			'name' => 'addr_street'
		));
		echo $this->Form->control('zip', array(
			'label' => __d('user', 'Postal code'),
			'name' => 'addr_zip'
		));
		echo $this->Form->control('city', array(
			'label' => __d('user', 'City'),
			'name' => 'addr_city'
		));
		echo $this->Form->control('country', array(
			'label' => __d('user', 'Country'),
			'options' => $countries,
			'empty' => __d('user', 'Select Country'),
			'name' => 'addr_country'
		));
	?>
	
	<div class="submit">
		<?php
			echo $this->Form->submit(__d('user', 'Cancel'), array('name' => 'Cancel', 'div' => false, 'onclick' => 'onCancel(); return false;')); 
		?>
		<?php
			echo $this->Form->submit(__d('user', 'Previous'), array('name' => 'Previous', 'div' => false, 'onclick' => 'onPrevious(); return false;')); 
		?>
		<?php
			echo $this->Form->submit(__d('user', 'Confirm Registration'), array('name' => 'pay', 'div' => false, 'onclick' => 'onPay(); return false;'));
		?>
	</div>
	</form>
</div>
<?php 
	echo $this->Form->create(false, array('id' => 'cancel'));
		echo $this->Form->hidden('Cancel', array('value' => 'Cancel'));
	echo $this->Form->end();
	echo $this->Form->create(false, array('id' => 'previous'));
		echo $this->Form->hidden('Previous', array('value' => 'Previous'));
	echo $this->Form->end();
?>

