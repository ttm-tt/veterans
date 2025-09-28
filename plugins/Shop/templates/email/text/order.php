<?php
use Shop\Model\Table\OrderStatusTable;
?>

<?php
	$LF = "\r\n";
?>
<?php 
	$isWait = $order['order_status_id'] === OrderStatusTable::getWaitingListId();
	$isPaid = $order['order_status_id'] === OrderStatusTable::getPaidId();
	
	$wantReceipt = 
			$order['order_status_id'] === OrderStatusTable::getPendingId() ||
			$order['order_status_id'] === OrderStatusTable::getPaidId() ||
			$order['order_status_id'] === OrderStatusTable::getInvoiceId()
	;
				
	$wantBanktransfer = $shopSettings['banktransfer'] || empty($order['ticket']);
	$wantCreditcard = $shopSettings['creditcard'] && !empty($order['ticket']);
	$wantPaypal = $shopSettings['paypal'] && !empty($order['ticket']);	
?>

<?php 
	if ($isWait) {
		echo __d('user', 'Thank you for your registration.');
		echo $LF;
		echo __d('user', 'Please note that your registration is on a waiting list.');
		echo $LF;
		echo __d('user', 'The waiting list is processed on a first come first served basis.');
		echo $LF;
		echo __d('user', 'We will notify you by email when it is possible for you to register.');
		echo $LF;
		echo __d('user', 'Please do not pay the registration fee unless you have been asked to do so.');
		echo $LF;
		echo __d('user', 'If in the meantime you decide to cancel your registration please inform us so that we can remove you from the waiting list.');
	} else if ($processWaitingList) {
		echo __d('user', 'We want to inform you that your registration {0} can now be processed.', $order['invoice']);
		echo $LF;
		echo __d('user', 'Please pay the amount due as soon as possible, but not later than {0}.', empty($until) ? date('Y-m-d', strtotime('+14 days')) : $until);
		echo $LF;
		echo __d('user', 'If we haven\'t received your payment by then we will cancel your registration.');
		echo $LF;
		echo __d('user', 'Please notify us if you want us to cancel your registration so that we can inform the next on the waiting list immediately.');
		if ($wantBanktransfer) {
			echo $LF;
			echo '*';
			echo __d('user', 'Don\'t forget to put your invoice number on the bank transfer so we can find your order.') . ' ';
			echo '*';
			echo $LF;
			echo __d('user', 'Please note that it may take several days until the payment is credited to our account and is processed.');
		}
		if ($wantCreditcard) {
			echo $LF;
			echo __d('user', 'To pay with credit card use this link:');
			echo $LF;
			echo $this->Html->link(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'pay', '?' => ['ticket' => $order['ticket']], '_full' => true], null);
			
			// Add note if only cc is allowed
			if (!($wantBanktransfer ?? false)) {
				echo $LF;
				echo __d('user', 'Only payment with credit card is possible!');
			}
		}
		if ($wantPaypal) {
			echo $LF;
			echo __d('user', 'To pay with Paypal or credit card use this link:');
			echo $LF;
			echo $this->Html->link(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'pay', '?' => ['ticket' => $order['ticket']], '_full' => true], null);
			
			// Add note if only cc is allowed
			if (!($wantBanktransfer ?? false)) {
				echo $LF;
				echo __d('user', 'Only payment with Paaypal or credit card is possible!');
			}
		}
	} else if ($reminder) {
		echo __d('user', 'This is a friendly reminder that you haven\'t paid your registration {0} yet.', $order['invoice']);
		echo $LF;
		echo __d('user', 'If you believe this is an error please contact {0}.', $shopSettings['email']);
		echo $LF;
		echo __d('user', 'If you have not paid yet please do so until {0}.', empty($until) ? date('Y-m-d', strtotime('+7 days')) : $until);
		echo $LF;
		echo __d('user', 'If we haven\'t received your payment by then we reserve the right to cancel your registrations without further notice.');
		if ($wantBanktransfer) {
		echo $LF;
			echo '*';
			echo __d('user', 'Don\'t forget to put your invoice number on the bank transfer so we can find your order.') . ' ';
			echo '*';
			echo $LF;
			echo __d('user', 'Please note that it may take several days until the payment is credited to our account and is processed.');
		}
		if ($wantCreditcard) {
			echo $LF;
			echo __d('user', 'To pay with credit card use this link:');
			echo $LF;
			echo $this->Html->link(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'pay', '?' => ['ticket' => $order['ticket']], '_full' => true], null);
			
			// Add note if only cc is allowed
			if (!($wantBanktransfer ?? false)) {
				echo '<br>';
				echo __d('user', 'Only payment with credit card is possible!');
			}
		}
		if ($wantPaypal) {
			echo $LF;
			echo __d('user', 'To pay with paypal or credit card use this link:');
			echo $LF;
			echo $this->Html->link(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'pay', '?' => ['ticket' => $order['ticket']], '_full' => true], null);
			
			// Add note if only cc is allowed
			if (!($wantBanktransfer ?? false)) {
				echo '<br>';
				echo __d('user', 'Only payment with paypal or credit card is possible!');
			}
		}
	} else {
		echo __d('user', 'Thank you for your registration.');
		echo ' ';
		echo __d('user', 'Your invoice number is {0}.', $order['invoice']); 
		
		// If not yet paid and not paid by a payment method (Dibs returns "Capture pending"), ask to pay by bank transfer
		if (!$isPaid && empty($order['payment_method'])) {
			echo $LF;
			echo __d('user', 'Please pay the amount due as soon as possible, but not later than {0}.', empty($until) ? date('Y-m-d', strtotime('+14 days')) : $until);
			echo $LF;
			echo __d('user', 'Note that your registration is not completed until we have received the full amount still due.');
			echo $LF;
			echo __d('user', 'We reserve the right to cancel your order after that date if we haven\'t received the full amount still due until then.');
			if ($wantBanktransfer) {
				echo $LF;
				echo '*';
				echo __d('user', 'Don\'t forget to put your invoice number on the bank transfer so we can find your order.') . ' ';
				echo '*';
				echo $LF;
				echo __d('user', 'Please note that it may take several days until the payment is credited to our account and is processed.');
			}
			if ($wantCreditcard) {
				echo $LF;
				echo __d('user', 'To pay with credit card use this link:');
				echo $LF;
				echo $this->Html->link(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'pay', '?' => ['ticket' => $order['ticket']], '_full' => true], null);

				// Add note if only cc is allowed
				if (!($wantBanktransfer ?? false)) {
					echo $LF;
					echo __d('user', 'Only payment with credit card is possible!');
				}
			}
			if ($wantPaypal) {
				echo $LF;
				echo __d('user', 'To pay with Paypal or credit card use this link:');
				echo $LF;
				echo $this->Html->link(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'pay', '?' => ['ticket' => $order['ticket']], '_full' => true], null);

				// Add note if only cc is allowed
				if (!($wantBanktransfer ?? false)) {
					echo $LF;
					echo __d('user', 'Only payment with Paypal or credit card is possible!');
				}
			}
		} else if (!$isPaid && $order['payment_method'] === 'Invoice') {
			// Special case for Invoice: let them pay with CC, too
			if ($wantCreditcard) {
				echo $LF;
				echo __d('user', 'To pay with credit card use this link:');
				echo $LF;
				echo $this->Html->link(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'pay', '?' => ['ticket' => $order['ticket']], '_full' => true], null);
			}			
			if ($wantPaypal) {
				echo $LF;
				echo __d('user', 'To pay with paypal or credit card use this link:');
				echo $LF;
				echo $this->Html->link(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'pay', '?' => ['ticket' => $order['ticket']], '_full' => true], null);
			}			
		}
	}
?>

<?php
	if ($wantReceipt)  
		echo __d('user', 'Please find your receipt attached.'); 
?>

	
<?php echo __d('user', 'Best regards'); ?>

