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
				$('form#nestpay input[type="hidden"]').remove();
				
				$.each(data, function(key, val) {
					$('form#nestpay').append('<input type="hidden" name="' + key + '" value="' + val + '">');
				});
				
				$('form#nestpay').submit();
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
	<?php echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Billing Information');?></h2>
	<div class="hint">
	<?php
		echo __d('user', 'Payment will be processed in a secure way by {0}.', 'NestPay') . '<br>';
		echo __d('user', 'After the payment is completed you will be redirected to the registration.') . '<br>';
		
		if (isset($bankCurrency)) {
			echo '<p>';
			echo __d('user', 'Payment will be done in {0} using an exchange rate 1 {1} to {2} {3}', 
					 $bankCurrency, $shopSettings['currency'], number_format($bankExchange, 2), $bankCurrency);
			echo '</p>';
		}
	?>
	</div>
	
	<form id="nestpay" method="post" accept-charset="UTF-8" action="<?=$submitUrl?>" >
		<?php 
			if (isset($bankCurrency)) {
				echo $this->Form->control('amount', array(
					'label' => __d('user', 'Amount'),
					'type' => 'text',
					'readonly' => 'readonly',
					'value' => $amount . ' ' . $shopSettings['currency'] . ' (' . number_format($bankAmount, 0, '', '') . ' ' . $bankCurrency . ')',
					'name' => false,
				));
			} else {
				echo $this->Form->control('amount', array(
					'label' => __d('user', 'Amount'),
					'type' => 'text',
					'readonly' => 'readonly',
					'value' => $amount . ' ' . $shopSettings['currency'],
					'name' => false,
				));				
			}
		?>
		<div class="submit">
			<?php		
				echo $this->element('shop_footer', [
					'nextForce' => true,
					'next' => __d('user', 'Confirm Registration'), 
					'nextOptions' => array('name' => false, 'onclick' => 'onPay(); return false;')
				]);
			?>
		</div>
	</form>
	<div>
		<span>
			<?= $this->Html->image('Payment/nestpay/maestro.png'); ?>
		</span>
		<span>
			<?= $this->Html->image('Payment/nestpay/mastercard.png'); ?>
		</span>
		<span>
			<?= $this->Html->image('Payment/nestpay/dina_0.png'); ?>
		</span>
		<span>
			<?= $this->Html->image('Payment/nestpay/visa.png'); ?>
		</span>
		<span>
			<?= $this->Html->image('Payment/nestpay/americanexpress_0.png'); ?>
		</span>
		<span>
			<a href="https://www.bancaintesa.rs/" target="_blank">
				<?= $this->Html->image('Payment/nestpay/intesa.png'); ?>
			</a>
		</span>
		<span>
			<a href="https://mastercard.com" target="_blank">
				<?= $this->Html->image('Payment/nestpay/master_code.png'); ?>
			</a>
		</span>
		<span>
			<a href="https://visa.com" target="_blank">
				<?= $this->Html->image('Payment/nestpay/visa_secure.jpg'); ?>
			</a>
		</span>			
	</div>
 	<!-- 
		<?= $this->Html->image('Payment/nestpay/payment-large.png', ['style' => 'width: 100%; height: auto;']); ?>
	-->
</div>
<?php 
	echo $this->Form->create(null, array('id' => 'cancel'));
		echo $this->Form->hidden('Cancel', array('value' => 'Cancel'));
	echo $this->Form->end();
	echo $this->Form->create(null, array('id' => 'previous'));
		echo $this->Form->hidden('Previous', array('value' => 'Previous'));
	echo $this->Form->end();
?>

