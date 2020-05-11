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
	$('div#processing').show();
	$('div#billing').hide();
	
	var expdate = '';
	expdate += $('form #expdate_month').val() + '/' + $('form #expdate_year').val();
	
	$('form #x_exp_date').val(expdate);


	$.ajax({
		'type' : 'POST',
		'dataType' : 'json',
		'url'  : '<?php echo Router::url(array('action' => 'onPrepareCreditcard')); ?>',
		'success' : function(data) {
			if (typeof(data) !== 'string' || data === '') {
				$('div#processing h2').html("<?php echo __d('user', 'The payment could not be initiated. Please try again later.');?>");
			} else {
				$('#x_invoice_num').val(data);

				$('form#authorizenet').submit();
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
	<form id="authorizenet" method="post" action="https://test.authorize.net/gateway/transact.dll">
	<?php echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Billing Information');?></h2>
	<div class="hint">
	<?php
		echo __d('user', 'Select your credit card and enter name and address of the card holder.') . '<br>';
		echo __d('user', 'Payment will be processed in a secure way by {0}.', 'authorize.net.') . '<br>';
	?>
	</div>
	
	<input type="hidden" name="x_exp_date" value="" id="x_exp_date">
	<input type="hidden" name="x_amount" value="<?php echo $amount?>">
	<input type="hidden" name="x_currency_code" value="<?php echo $currency?>">
	<input type="hidden" name="x_fp_sequence" value="<?php echo $fp_sequence?>">
	<input type="hidden" name="x_fp_hash" value="<?php echo $fp_hash?>">
	<input type="hidden" name="x_fp_timestamp" value="<?php echo $fp_timestamp?>">
	<input type="hidden" name="x_relay_response" value="TRUE">
	<input type="hidden" name="x_relay_url" value="<?php echo $relay_response_url?>">
	<input type="hidden" name="x_login" value="<?php echo $login_id?>">
	<input type="hidden" name="x_invoice_num" value="" id="x_invoice_num">
	
	<?php 
		echo $this->Form->control(null, array(
			'label' => __d('user', 'Amount'),
			'type' => 'text',
			'readonly' => 'readonly',
			'value' => $amount . ' ' . $shopSettings['currency'],
			'name' => false,
		));
	?>
	<?php 
		echo $this->Form->control(null, array(
			'label' => __d('user', 'Credit Card'),
			'type' => 'select',
			'empty' => __d('user', 'Select credit card type'),
			// 'name' => 'cc_typ',
			'options' => array('VisaCard' => 'VisaCard', 'MasterCard' => 'MasterCard'),
			'name' => false,
		));
	?>
	<?php
		echo $this->Form->control(null, array(
			'label' => __d('user', 'Card no.'),
			'name' => 'x_card_num',
			'type' => 'text'
		));
	?>
	<?php 
		echo $this->Form->control(null, array(
			'label' => __d('user', 'CVV code'),
			'name' => 'x_card_code',
			'type' => 'text',
			'after' => '<a href="http://www.cvvnumber.com/cvv.html" target="_blank" style="font-size:11px">What is my CVV code?</a>'
		));
	?>
	<div class="input select">
	<label><?php echo __d('user', 'Card expires');?></label>
	<?php 
		echo $this->Form->month(null, array(
			'label' => false,
			'div' => false,
			'id' => 'expdate_month',
			'name' => false,
			'monthNames' => false,			
			'style' => 'width: 19%;'
		));
	?>
	<span style="width: 2%;">&nbsp;/&nbsp;</span>
	<?php
		echo $this->Form->year(null, date('Y'), date('Y') + 10, array(
			'label' => false,
			'div' => false,
			'id' => 'expdate_year',
			'name' => false,
			'style' => 'width: 19%;',
			'orderYear' => 'asc'
		));
	?>
	</div>
	<?php 
		echo $this->Form->control(null, array(
			'label' => __d('user', 'Card holder given name'),
			'name' => 'x_first_name',
			'type' => 'text'
		));
	?>
	<?php 
		echo $this->Form->control(null, array(
			'label' => __d('user', 'Card holder family name'),
			'name' => 'x_last_name',
			'type' => 'text'
		));
	?>
	<?php
		echo $this->Form->control(null, array(
			'label' => __d('user', 'Street'),
			'name' => 'x_address'
		));
		echo $this->Form->control(null, array(
			'label' => __d('user', 'Postal code'),
			'name' => 'x_zip'
		));
		echo $this->Form->control(null, array(
			'label' => __d('user', 'City'),
			'name' => 'x_city'
		));
		echo $this->Form->control(null, array(
			'label' => __d('user', 'Country'),
			'options' => $countries,
			'empty' => __d('user', 'Select Country'),
			'name' => 'x_country'
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
			echo $this->Form->submit(__d('user', 'Confirm Registration'), array('name' => false, 'div' => false, 'onclick' => 'onPay(); return false;'));
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

