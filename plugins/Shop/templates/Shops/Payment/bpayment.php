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
				$.each(data.parameters, function(key, val) {
					$('#' + key).val(val);
				});
				
				$.each(data.cart, function(idx, item) {
					var desc = item.description;
					var count = item.quantity;
					var price = item.price;
					var total = item.total;
					
					$('form').append(
						'<input type="hidden" name="itemdescription_' + (idx+1) + '" value=' + desc + '>'
					);
					$('form').append(
						'<input type="hidden" name="itemcount_' + (idx+1) + '" value=' + count + '>'
					);
					$('form').append(
						'<input type="hidden" name="itemunitamount_' + (idx+1) + '" value=' + price + '>'
					);
					$('form').append(
						'<input type="hidden" name="itemamount_' + (idx+1) + '" value=' + total + '>'
					);
				});
				
				$('form#bpayment').submit();
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

<?php
	$this->Form->templater()->push();
	$this->Form->templater()->add(['submitContainer' => '{{content}}']);	
?>

<div id="processing" class="order billing form" style="display:none">
	<h2>
		<?php echo __d('user', 'Please wait while your payment is being processed.');?>
		<br>
		<?php echo __d('user', 'This may take several minutes.');?>
	</h2>
</div>

<div id="billing" class="order billing form">
	<form id="bpayment" method="post" accept-charset="UTF-8" action="<?php echo $bpaymentUrl;?>">
	<?php echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Billing Information');?></h2>
	<div class="hint">
	<?php
		echo __d('user', 'Payment will be processed in a secure way by {0}.', 'b-payment.hu') . '<br>';
		echo __d('user', 'After the payment is completed you will be redirected to the registration.') . '<br>';
	?>
	</div>
	
	<input type="hidden" name="Merchantid" value="" id="merchantid">
	<input type="hidden" name="paymentgatewayid" value="" id="paymentgatewayid">
	<input type="hidden" name="Orderid" value="" id="orderid">
	<input type="hidden" name="checkhash" value="" id="checkhash">
	<input type="hidden" name="amount" value="" id="amount">
	<input type="hidden" name="currency" value="" id="currency">
	<input type="hidden" name="language" value="" id="language">
	<input type="hidden" name="returnurlsuccess" value="" id="returnurlsuccess">
	<input type="hidden" name="returnurlsuccessserver" value="" id="returnurlsuccessserver">
	<input type="hidden" name="returnurlcancel" value="" id="returnurlcancel">
	<input type="hidden" name="returnurlerror" value="" id="returnurlerror">
	<input type="hidden" name="skipreceiptpage" value="" id="skipreceiptpage">
		   
	<?php 
		echo $this->Form->control('amount', array(
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
	echo $this->Form->create(null, array('id' => 'cancel'));
		echo $this->Form->hidden('Cancel', array('value' => 'Cancel'));
	echo $this->Form->end();
	echo $this->Form->create(null, array('id' => 'previous'));
		echo $this->Form->hidden('Previous', array('value' => 'Previous'));
	echo $this->Form->end();
?>

<?php
	$this->Form->templater()->pop();
?>
