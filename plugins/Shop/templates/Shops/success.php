<div class="shops index">
	<h2><?php echo __d('user', 'Your registration has been completed');?></h2>
	<br>
	<div class="hint">
	<?php
		if ($order['order_status']['name'] === 'WAIT') {
			echo __d('user', 'Your registration has been placed on the waiting list.');
			echo '<br>';
			echo __d('user', 'The waiting list is processed on a first come first served basis.');
			echo '<br>';
			echo __d('user', 'We will notify you by email when it is possible for you to register.');
			echo '<br>';
			echo __d('user', 'Please do not pay the registration fee unless you have been asked to do so.');
		} else if ($order['order_status']['name'] === 'PAID') {
			echo __d('user', 'An email with your invoice and instructions how to proceed has been sent to you.');
		} else if ($order['order_status']['name'] === 'PEND') {
			echo __d('user', 'An email with your invoice has been sent to you.');
			echo '<br>';
			echo __d('user', 'Once we have received your payment instructions on how to proceed will be sent to you.');
		}
	?>
	</div>
	<br>
	<h3>
		<?php 	
			$wantInvoice = 
					$order['order_status']['name'] === 'PEND' ||
					$order['order_status']['name'] === 'PAID' ||
					$order['order_status']['name'] === 'INVO'
			;
			
			if ($wantInvoice && !empty($order['ticket']))
				echo $this->Html->link(__d('user', 'Download invoice'), array('action' => 'receipt', '?' => ['ticket' => $order['ticket']]));
		?>
	</h3>
	<br>
	<h3>
		<?php 
			if ($wantInvoice) 
				echo __d('user', 'Invoice number') . ': ' . $order['invoice'];
		?>
	</h3>
	<br>
	<h3><?php echo __d('user', 'Registration');?></h3>
	<?php echo $this->element('shop_order');?>
	<br>
	<?php 
		if (!empty($people)) {
			echo $this->element('shop_people', array('edit' => false));
			echo '<br>';
		}
	?>
	<?php 
	if (!empty($address) && !empty($address['id'])) { ?>
		<h3><?php echo __d('user', 'Billing Address');?></h3>
		<?php echo $this->element('shop_address');?></h3>
	<?php } ?>
	<br>
	<?php 
		if (!empty($shopSettings['footer'])) {
			echo $shopSettings['footer'];
		} else {
			echo '<h3>' . __d('user', 'Banking Account') . '</h3>';
			echo '<dl>';
				if (!empty($shopSettings['vat'])) {
					echo '<dt>' . __d('user', 'VAT Reg Id') . '</dt>';
					echo '<dd>' . $shopSettings['vat'] . '</dd>';
				}
				if (!empty($shopSettings['bank_name'])) {
					echo '<dt>' . __d('user', 'Bank') . '</dt>';
					echo '<dd>' . $shopSettings['bank_name'] . '</dd>';
				}
				if (!empty($shopSettings['account_holder'])) {
					echo '<dt>' . __d('user', 'Account Holder') . '</dt>';
					echo '<dd>' . $shopSettings['account_holder'] . '</dd>';
				}
				if (!empty($shopSettings['iban'])) {
					echo '<dt>' . __d('user', 'IBAN') . '</dt>';
					echo '<dd>' . $shopSettings['iban'] . '</dd>';
				}
				if (!empty($shopSettings['bic'])) {
					echo '<dt>' . __d('user', 'BIC') . '</dt>';
					echo '<dd>' . $shopSettings['bic'] . '</dd>';
				}
				if (!empty($shopSettings['swift'])) {
					echo '<dt>' . __d('user', 'SWIFT') . '</dt>';
					echo '<dd>' . $shopSettings['swift'] . '</dd>';
				}
				if (!empty($shopSettings['aba'])) {
					echo 'dt>' . __d('user', 'ABA') . '</dt>';
					echo '<dd>' . $shopSettings['aba'] . '<dd>';
				}
				if (!empty($shopSettings['correspondent_bank'])) {
					echo '<dt>' . __d('user', 'Correspondent Bank') . '</dt>';
					echo '<dd>' . implode('<br>', explode("\n", $shopSettings['correspondent_bank'])) . '</dd>';
				}
				if (!empty($shopSettings['phone'])) {
					echo '<dt>' . __d('user', 'Phone') . '</dt>';
					echo '<dd>' . $shopSettings['phone'] . '</dd>';
				}
				if (!empty($shopSettings['fax'])) {
					echo '<dt>' . __d('user', 'Fax') . '</dt>';
					echo '<dd>' . $shopSettings['fax'] . '</dd>';
				}
			echo '</dl>';
		}
	?>
</div>
