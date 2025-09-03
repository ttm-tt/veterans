<div class="order review form">
	<?php echo $this->Wizard->create(null);?>
	<?php echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Select Payment Method');?></h2>
	<?php
		$options = array();
		echo '<div class="hint">';
		if ($shopSettings['banktransfer']) {
			echo '<h4>' . __d('user', 'Bank Transfer') . '</h4>';
			echo __d('user', 'You may pay by bank transfer');
			echo '<p></p>';
			
			$options['bt'] = 
				'<span style="width: 110px; text-align: left; float: left; margin-bottom: 0.9375rem;">' . __d('user', 'Bank Transfer') . '</span>' .
				$this->Html->image('Payment/' . $paymentLogos['bt'], array(
					'alt' => __d('user', 'Bank Transfer'),
					'style' => 'float: left; padding-left: 10px; height: 2rem;'
				));
		}
		if ($shopSettings['creditcard']) {
			echo '<h4>' . __d('user', 'Credit Card') . '</h4>';
			echo __d('user', 'We accept MasterCard and Visa');
			echo '<p></p>';
			
			$options['cc'] = 
				'<span style="width: 110px; text-align: left; float: left; margin-bottom: 0.9375rem;">' . __d('user', 'Credit Card') . '</span>' .
				$this->Html->image('Payment/' . $paymentLogos['cc'], array(
					'alt' => __d('user', 'Credit Card'),
					'style' => 'float: left; padding-left: 10px; height: 2rem;'
				));
		}
		if ($shopSettings['paypal']) {
			echo '<h4>' . __d('user', 'PayPal') . '</h4>';
			if ($shopSettings['creditcard'])
				echo __d('user', 'You may pay with PayPal');
			else
				echo __d('user', 'You may pay with PayPal or credit card');
			echo '<p></p>';
			
			$options['pp'] = 
				'<span style="width: 110px; text-align: left; float: left; margin-bottom: 0.9375rem;">' . __d('user', 'PayPal') . '</span>' .
				$this->Html->image('Payment/' . $paymentLogos['pp'], array(
					'alt' => __d('user', 'PayPal'),
					'style' => 'float: left; padding-left: 10px; height: 2rem;'
				));
		}
		echo '</div>';
	?>
	
	<fieldset>
		<legend><?php echo __d('user', 'Payment Method');?></legend>
		
	<?php
		echo $this->Form->control('PaymentSelection.payment_method', array(
			'type' => 'radio',
			'options' => $options,
			'escape' => false,
			'label' => false,
			'value' => empty($payment_method) ? array_keys($options)[0] : $payment_method,
			'templates' => [
	            'nestingLabel' => '<div class="grid-x small-12">{{hidden}}{{input}}<p><label {{attrs}}>{{text}}</label></p></div>',
			]
		));
	?>
	</fieldset>
	<?php echo $this->element('shop_footer'); ?>
	<?php echo $this->Form->end(); ?>
</div>
