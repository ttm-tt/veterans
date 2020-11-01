<?php
use Cake\Routing\Router;
?>

<?php
	$this->Form->templater()->push();
	$this->Form->templater()->add(['submitContainer' => '{{content}}']);	
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
			if ($.isArray(data)) {
				$('#orderid').val(data[0]);
				fakeSubmit(data[0]);
				// $('form#dummy').submit();
			} else {
				$('div#processing h2').html("<?php echo __d('user', 'The payment could not be initiated. Please try again later.');?>");
			}
		},
		'error' : function(xhr, status, error) {
			$('div#processing h2').html("<?php echo __d('user', 'The payment could not be initiated. Please try again later.');?>");
		}
	});
}

// Data would be submitted to PSP, which would do the next steps.
// Instead we fake what the PSP would do
function fakeSubmit(orderId) {
	$.post(
		'<?php echo Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_complete'));?>', 
		{ id : orderId, error : $('#error').is(':checked') }, 
		function() {
			if ($('#error').is(':checked')) {
				window.location.replace(
					'<?php echo Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_error'));?>?id=' + orderId);			
			} else {
				window.location.replace(
					'<?php echo Router::url(array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'payment_success'));?>?id=' + orderId);
			}
		}
	);
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
	<form id="dummy" method="post" accept-charset="UTF-8" action="<?php echo Router::url(array('action' => 'payment_success'));?>" >
	<?php 
		// echo $this->element('shop_header'); 
	?>
	<h2><?php echo __d('user', 'Billing Information');?></h2>
	<input type="hidden" name="orderid">
	<?php 
		echo $this->Form->control('amount', array(
			'label' => __d('user', 'Amount'),
			'type' => 'text',
			'readonly' => 'readonly',
			'value' => $amount . ' ' . $shopSettings['currency'],
			'name' => false,
		));
		
		echo $this->Form->control('error', array(
			'label' => __d('user', 'Return Error'),
			'type' => 'checkbox',
			'name' => 'error',
			'id' => 'error'
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

