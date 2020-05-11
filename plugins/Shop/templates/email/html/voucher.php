<?php 
	if ($order['invoice_address']['title'] === 'Mr')
		echo __d('user', 'Dear Mr. {0} {1},', $order['invoice_address']['first_name'], $order['invoice_address']['last_name']);
	else if ($order['InvoiceAddress']['title'] === 'Mrs')
		echo __d('user', 'Dear Mrs. {0} {1},', $order['invoice_address']['first_name'], $order['invoice_address']['last_name']);
	else
		echo __d('user', 'Dear Sir or Madam,');
	echo '<p>';
	echo __d('user', 'Thank you for your registration to the {0}.', $tournament['description']);
	echo '<p>';
	echo __d('user', 'A voucher is attached to this mail.');
	echo ' ';
	echo __d('user', 'Please print this voucher and present it at the accreditation desk.');
	echo '<p>';
	echo '<p>';
	echo __d('user', 'Best regards');
?>
