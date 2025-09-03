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
		'url'  : '<?php echo Router::url(array('action' => 'onPreparePaypal')); ?>',
		'success' : function(data) {
			if ($.isPlainObject(data)) {
				$.each(data, function(key, val) {
					$('#processing').append('<input type="hidden" name="' + key + '" value="' + val + '">');
				});
				$('#processing').show();
				$('#billing').hide();
				callPaypal();
			} else {
				$('div#processing h2').html("<?php echo __d('user', 'The payment could not be initiated. Please try again later.');?>");
			}
		},
		'error' : function(xhr, status, error) {
			$('div#processing h2').html("<?php echo __d('user', 'The payment could not be initiated. Please try again later.');?>");
		}
	});
}

function callPaypal() {
	var oid = $('#processing input[name="order_id"]').val();
	var dsc = $('#processing input[name="description"]').val();
	var cur = $('#processing input[name="currency"]').val();
	var amt = $('#processing input[name="amount"]').val();
	
	paypal.Buttons({
		// Sets up the transaction when a payment button is clicked
		createOrder: (data, actions) => {
			return actions.order.create({
				"purchase_units": [{
					"custom_id":oid ,
					"description": dsc,
					"amount": {
						"currency_code": cur,
						"value": amt,
						"breakdown": {
							"item_total": {  /* Required when including the items array */
								"currency_code": cur,
								"value": amt,
							}
						}
					},
					"items": [
						{
							"name": "'" + dsc + "'", /* Shows within upper-right dropdown during payment approval */
							"description": "'" + dsc + "'", /* Item details will also be in the completed paypal.com transaction view */
							"unit_amount": {
								"currency_code": cur,
								"value": amt,
							},
							"quantity": "1",
							"category": "DIGITAL_GOODS"
						},
					]
				}]
			});
		},
		// Finalize the transaction after payer approval
		onApprove: (data, actions) => {
			return actions.order.capture().then(function(orderData) { //console.log(orderData);
				// Successful capture! For dev/demo purposes:
				//console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));
				//const transaction = orderData.purchase_units[0].payments.captures[0]; //console.log(transaction);
				//alert(`Transaction ${transaction.status}: ${transaction.id}\n\nSee console for all available details`);
				// When ready to go live, remove the alert and show a success message within this page. For example:
				// const element = document.getElementById('paypal-button-container');
				// element.innerHTML = '<h3>Thank you for your payment!</h3>';
				// Or go to another URL:  actions.redirect('thank_you.html');

				setProcessing(true);

				var postData = {
					paypal_order_check: 1, 
					paypal_id: orderData.id, 
					order_id: orderData.purchase_units[0].custom_id
				};
				fetch('<?= Router::url(['action' => 'payment_complete']); ?>', {
					method: 'POST',
					headers: {'Accept': 'application/json'},
					body: encodeFormData(postData)
				})
				.then((response) => response.json())
				.then((result) => {
					if(result.code == 'PAID') {
						// Show details
						$('#processing #response').append(
							'<h2>Payment successful</h2>'
						);
						setTimeout(function() {
							location.href=
								'<?= Router::url(['action' => 'payment_success']); ?>' +
								'?order=' + result.order_id;
						}, 1000);
					} else {
						$('#processing #response').append(
							'<h2>Payment not successful</h2>' +
							'<br>' +
							'<h3>Error: ' + result.msg + '</h3>' +
							'<br>' +
							'<h4>' + result.reason + '<h4>'
						);
					
						setTimeout(function() {
							location.href=
								'<?= Router::url(['action' => 'payment_error']); ?>' +
								'?order=' + result.order_id;
						}, 1000);
					}
					setProcessing(false);
				})
				.catch(error => console.log(error));
			});
		}
	}).render('#paypal-button-container');
}

const encodeFormData = (data) => {
  var form_data = new FormData();

  for ( var key in data ) {
    form_data.append(key, data[key]);
  }
  return form_data;   
}

// Show a loader on payment form processing
const setProcessing = (isProcessing) => {
	$('#paypal-button-container').hide();
	if (isProcessing) {
		$('#waiting').show();
		$('#response').hide();
	} else {
		$('#waiting').hide();
		$('#response').show();
	}
}

<?php
	$this->Html->scriptEnd();
?>

<script src="<?=$paypalUrl?>"></script>

<div id="processing" class="order billing form" style="display:none">
	<!-- Display status message -->
	<div id="response" style="display:none">
		
	</div>

	<div id="waiting" style="display:none">
		<h2>
			<?php echo __d('user', 'Please wait while your payment is being processed.');?>
			<br>
			<?php echo __d('user', 'This may take a few moments .');?>
		</h2>
	</div>
	<!-- Set up a container element for the button -->
	<div id="paypal-button-container">
		
	</div>	
</div>

<div id="billing" class="order billing form">
	<form id="paypal" method="post" accept-charset="UTF-8" action="<?php echo $paypalUrl;?>">
	<?php if (isset($activeStep)) echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Billing Information');?></h2>
	<div class="hint">
	<?php
		echo __d('user', 'Payment will be processed in a secure way by {0}.', 'Paypal') . '<br>';
		echo __d('user', 'After the payment is completed you will be redirected to the registration.') . '<br>';
	?>
	</div>
	
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
			echo $this->element('shop_footer', [
				'nextForce' => true,
				'next' => __d('user', 'Confirm Registration'), 
				'nextOptions' => array('name' => false, 'onclick' => 'onPay(); return false;')
			]);
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

