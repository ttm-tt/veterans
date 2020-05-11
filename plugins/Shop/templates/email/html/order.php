<?php
use Shop\Model\Table\OrderStatusTable;
?>

<?php
	ob_start();
?>

* {
	font-family: sans-serif;
	font-size: 16px;
}

ul {
  list-item-style: none;
}

table {
	width: 80%;
}

table th {
	text-align: left;
}

table tr th {
	border-bottom: 1px solid black;
}

table tr td {
	padding-top: 5px;
}

table tr.total td {
	border-top: 1px solid black;
	font-size: 110%;
	font-weight: bold;
}

table tr td.cancelled {
	text-decoration: line-through;
}

table tr .pos {
	width: 2em;
}	

table tr .currency {
	text-align: right;
	padding-right: 0.5em;
}

table tr .number {
	text-align: right;
	padding-right: 0.5em;
}

dl dt {
	display: none;
}

div#header {
	font-size: 80%;
}

div#footer {
  font-size: 80%;
}

div#footer span.add-footer {
	display: block;
	float: initial;
	width: 100%;
	text-align: center;
}

div#footer span.dl {
	display: inline-block;
	float:left;
	width: 45%;
	margin-right: 2em;
}

div#footer span.dl ~ span.dl {
    padding-left: 1em;
}
	
div#footer table {
  width: auto;
}

div#footer table td {
  font-size: 80%;
  padding-top: 0px;
  padding-right: 2em;
}

.dl {
	line-height: 1em;
}

.dt {
	font-weight: bold;
	padding-right: 1em;
	vertical-align: top;
}

.dt:after {
	vertical-align: top;
	white-space: nowrap;
	content: ':';
}

.dd {
}

<?php
	$css = ob_get_clean();
	$this->append('css', '<style type="text/css">' . $css . '</style>');
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
?>

<?php 
	if ($isWait) {
		echo __d('user', 'Thank you for your registration.');
		echo '<br>';
		echo __d('user', 'Please note that your registration is on a waiting list.');
		echo '<br>';
		echo __d('user', 'The waiting list is processed on a first come first served basis.');
		echo '<br>';
		echo __d('user', 'We will notify you by email when it is possible for you to register.');
		echo '<br>';
		echo __d('user', 'Please do not pay the registration fee unless you have been asked to do so.');
		echo '<br>';
		echo __d('user', 'If in the meantime you decide to cancel your registration please inform us so that we can remove you from the waiting list.');
	} else if ($processWaitingList) {
		echo __d('user', 'We want to inform you that your registration {0} can now be processed.', $order['invoice']);
		echo '<br>';
		echo __d('user', 'Please pay the amount due as soon as possible, but not later than {0}.', empty($until) ? date('Y-m-d', strtotime('+14 days')) : $until);
		echo '<br>';
		echo __d('user', 'If we haven\'t received your payment by then we will cancel your registration.');
		echo '<br>';
		echo __d('user', 'Please notify us if you want us to cancel your registration so that we can inform the next on the waiting list immediately.');
		if ($wantBanktransfer) {
			echo '<br>';
			echo '<strong>';
			echo __d('user', 'Don\'t forget to put your invoice number on the bank transfer so we can find your order.') . ' ';
			echo '</strong>';
		}
		if ($wantCreditcard) {
			echo '<br>';
			echo __d('user', 'To pay with credit card use this link:');
			echo '<br>';
			echo $this->Html->link(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'pay', '?' => ['ticket' => $order['ticket']], '_full' => true], null);
		}
	} else if ($reminder) {
		echo __d('user', 'This is a friendly reminder that you haven\'t paid your registration {0} yet.', $order['invoice']);
		echo '<br>';
		echo __d('user', 'If you believe this is an error please contact {0}.', $shopSettings['email']);
		echo '<br>';
		echo __d('user', 'If you have not paid yet please do so until {0}.', empty($until) ? date('Y-m-d', strtotime('+7 days')) : $until);
		echo '<br>';
		echo __d('user', 'If we haven\'t received your payment by then we reserve the right to cancel your registrations without further notice.');
		if ($wantBanktransfer) {
			echo '<br>';
			echo '<strong>';
			echo __d('user', 'Don\'t forget to put your invoice number on the bank transfer so we can find your order.') . ' ';
			echo '</strong>';
		}
		if ($wantCreditcard) {
			echo '<br>';
			echo __d('user', 'To pay with credit card use this link:');
			echo '<br>';
			echo $this->Html->link(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'pay', '?' => ['ticket' => $order['ticket']], '_full' => true], null);
		}
	} else {
		echo __d('user', 'Thank you for your registration.');
		echo ' ';
		echo __d('user', 'Your invoice number is {0}.', $order['invoice']); 
		
		// If not yet paid and not paid by a payment method (Dibs returns "Capture pending"), ask to pay by bank transfer
		if (!$isPaid && empty($order['payment_method'])) {
			echo '<br>';
			echo __d('user', 'Please pay the amount due as soon as possible, but not later than {0}.', empty($until) ? date('Y-m-d', strtotime('+14 days')) : $until);
			echo '<br>';
			echo __d('user', 'Note that your registration is not completed until we have received the full amount still due.');
			echo '<br>';
			echo __d('user', 'We reserve the right to cancel your order after that date if we haven\'t received the full amount still due until then.');
			if ($wantBanktransfer) {
				echo '<br>';
				echo '<strong>';
				echo __d('user', 'Don\'t forget to put your invoice number on the bank transfer so we can find your order.') . ' ';
				echo '</strong>';
			}
			if ($wantCreditcard) {
				echo '<br>';
				echo __d('user', 'To pay with credit card use this link:');
				echo '<br>';
				echo $this->Html->link(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'pay', '?' => ['ticket' => $order['ticket']], '_full' => true], null);
			}
		} else if (!$isPaid && $order['payment_method'] === 'Invoice') {
			// Special case for Invoice: let them pay with CC, too
			if ($wantCreditcard) {
				echo '<br>';
				echo __d('user', 'To pay with credit card use this link:');
				echo '<br>';
				echo $this->Html->link(['plugin' => 'shop', 'controller' => 'shops', 'action' => 'pay', '?' => ['ticket' => $order['ticket']], '_full' => true], null);
			}			
		}
	}
?>
<p></p>
<?php echo __d('user', 'Details of your registration:'); ?>
<br>
<?php echo $this->element('shop_order');?>
<?php if (!empty($people)) { ?>
<p></p>
<?php echo __d('user', 'You have entered the following players and accompanying persons:'); ?>
<?php echo $this->element('shop_people', array('edit' => false)); ?>
<?php } ?>
<p></p>
<?php 
	if ($wantReceipt)  
		echo __d('user', 'Please find your receipt attached.'); 
?>
<p></p>
<p></p>
<?php echo __d('user', 'Best regards'); ?>
<p></p>	

<div id="footer">
	<?php 
		if ($isWait) {
			// Nothing
		} else if (!empty($shopSettings['footer'])) {
			echo $shopSettings['footer'];
		} else {
			echo '<span class="dl"><table class="dl">';
				if (!empty($shopSettings['bank_name'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Bank') . '</td>';
					echo '<td class="dd">' . $shopSettings['bank_name'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['bank_address'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Bank Address') . '</td>';
					echo '<td class="dd">' . $shopSettings['bank_address'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['account_no'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Account No') . '</td>';
					echo '<td class="dd">' . $shopSettings['account_no'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['iban'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'IBAN') . '</td>';
					echo '<td class="dd">' . $shopSettings['iban'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['bic'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'BIC (SWIFT)') . '</td>';
					echo '<td class="dd">' . $shopSettings['bic'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['aba'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'ABA') . '</td>';
					echo '<td class="dd">' . $shopSettings['aba'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['correspondent_bank'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Correspondent Bank') . '</td>';
					echo '<td class="dd">' . implode('<br>', explode("\n", $shopSettings['correspondent_bank'])) . '</td>';
					echo '</tr>';
				}
			echo '</table></span>';
			echo '<span class="dl"><table class="dl">';
				if (!empty($shopSettings['account_holder'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Account Holder') . '</td>';
					echo '<td class="dd">' . $shopSettings['account_holder'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['email'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Email') . '</td>';
					echo '<td class="dd">' . $shopSettings['email'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['phone'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Phone') . '</td>';
					echo '<td class="dd">' . $shopSettings['phone'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['fax'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Fax') . '</td>';
					echo '<td class="dd">' . $shopSettings['fax'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['vat'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'VAT Reg Id') . '</td>';
					echo '<td class="dd">' . $shopSettings['vat'] . '</td>';
					echo '</tr>';
				}
			echo '</table></span>';
			
			if (!empty($shopSettings['add_footer'])) {
				echo '<span class="add-footer">';
				echo $shopSettings['add_footer'];
				echo '</span>';
			}
		}
	?>
</div>
