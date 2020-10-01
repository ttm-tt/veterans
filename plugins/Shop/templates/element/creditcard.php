<?php
/*
 * General element for creditcard (and Paypal etc.) payments
 */
?>
 
<?php
use Cake\Routing\Router;
?>

<?php
	$this->Html->scriptStart(array('block' => true));
?>

// Ajax call to get the parameters
function onPay() {
	if ($('form#submit input#__temp__').attr('name') === 'cancel') {
		$('form#submit').submit();
		
		return;
	}
	
	$('div#processing').show();
	$('div#billing').hide();
	
	$.ajax({
		'type' : 'POST',
		'dataType' : 'json',
		'data' : $('form#submit').serialize(),
		'url'  : '<?php echo Router::url(array('action' => 'onPrepareCreditcard')); ?>',
		'success' : function(data) {
			if ($.isPlainObject(data)) {
				$.each(data, function(key, value) {
					$('form#payment').append('<input type="hidden" name="' + key + '" value="' + value + '">');
				});
				$('form#payment').submit();
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

<form id="payment" method="post" accept-charset="UTF-8" action="<?=$submitUrl?>" >
	<fieldset> 
	<?php 
		echo $this->Form->control('amount', array(
			'label' => __d('user', 'Amount'),
			'type' => 'text',
			'readonly' => 'readonly',
			'value' => $amount . ' ' . $shopSettings['currency'],
			'name' => false,
		));
	?>
	</fieldset>
</form>