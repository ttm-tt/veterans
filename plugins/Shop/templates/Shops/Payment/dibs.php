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
			if ($.isPlainObject(data)) {
				$('#accepturl').val(data.accepturl);
				$('#callbackurl').val(data.callbackurl);
				$('#cancelurl').val(data.cancelurl);
				$('#declineurl').val(data.declineurl);
				$('#invoice').val(data.invoice);
				$('#md5key').val(data.md5key);
				$('#order_id').val(data.order_id);
				
				$('#ordertext').val(
					"<?php echo $tournament['name'];?> " + data.invoice
				);

				$('form#dibs').submit();
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
	<form id="dibs" method="post" accept-charset="UTF-8" action="<?php echo $dibsUrl;?>">
	<?php echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Billing Information');?></h2>
	<div class="hint">
	<?php
		echo __d('user', 'Payment will be processed in a secure way by {0}.', 'DIBS Payment Services') . '<br>';
		echo __d('user', 'After the payment is completed you will be redirected to the registration.') . '<br>';
	?>
	</div>
	
	<input type="hidden" name="accepturl" value="" id="accepturl">
	<input type="hidden" name="callbackurl" value="" id="callbackurl">
	<input type="hidden" name="cancelurl" value="" id="cancelurl">
	<input type="hidden" name="declineurl" value="" id="declineurl">
	<input type="hidden" name="amount" value="<?php echo $amount;?>">
	<input type="hidden" name="currency" value="<?php echo $currency;?>">
	<input type="hidden" name="merchant" value="<?php echo $merchantId;?>">
	<input type="hidden" name="orderid" value="" id="invoice">
	<input type="hidden" name="uniqueorderid" value="1">
	<input type="hidden" name="capturenow" value="1">
	<input type="hidden" name="lang" value="<?php echo $lang;?>">
	<input type="hidden" name="md5key" value="" id="md5key">
	<input type="hidden" name="order_id" value="" id="order_id">
	<?php if (isset($test) && $test) { ?>
	<input type="hidden" name="test" value="1">
	<?php } ?>
	
	<input type="hidden" name="ordertext" id="ordertext" value="">
	<?php 
		echo $this->Form->control(null, array(
			'label' => __d('user', 'Amount'),
			'type' => 'text',
			'readonly' => 'readonly',
			'value' => ($amount / 100.) . ' ' . $shopSettings['currency'],
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


