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
	
	$.ajax({
		'type' : 'POST',
		'dataType' : 'json',
		'url'  : '<?php echo Router::url(array('action' => 'onPrepareCreditcard')); ?>',
		'success' : function(data) {
			if ($.isPlainObject(data)) {
				$.each(data, function(key, value) {
					$('input[name="' + key + '"]').val(value);
				});
				$('form#cardpointe').submit();
			} else {
				$('div#processing h2').html("<?php echo __d('user', 'The payment could not be initiated. Please try again later.');?>");
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
	<form id="cardpointe" method="post" accept-charset="UTF-8" action="https://wvc2018.securepayments.cardpointe.com/pay" >
	<?php echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Billing Information');?></h2>
	<div class="hint">
	<?php
		echo __d('user', 'Payment will be processed in a secure way by {0}.', 'CardPointe') . '<br>';
		echo __d('user', 'After the payment is completed you will be redirected to the registration.') . '<br>';
	?>
	</div>
	<input type="hidden" name="orderid">
	<input type="hidden" name="ticket">
	<input type="hidden" name="total">
	<input type="hidden" name="invoice">
	<input type="hidden" name="customerId">
	<input type="hidden" name="billCompany">
	<input type="hidden" name="billFName">
	<input type="hidden" name="billLName">
	<input type="hidden" name="billAddress1">
	<input type="hidden" name="billAddress2">
	<input type="hidden" name="billCity">
	<input type="hidden" name="billZip">
	<input type="hidden" name="billCountry">
	<input type="hidden" name="email">
	<input type="hidden" name="phone">
	
	<?php 
		echo $this->Form->control(null, array(
			'label' => __d('user', 'Amount'),
			'type' => 'text',
			'readonly' => 'readonly',
			'value' => $amount . ' ' . $shopSettings['currency'],
			'name' => false,
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

