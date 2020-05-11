<?php
	$LF = "\r\n";
?>
<?php 
	if ($order['invoice_address']['title'] === 'Mr') {
		echo __d('user', 'Dear Mr. {0} {1},', $order['invoice_address']['first_name'], $order['invoice_address']['last_name']);
		echo $LF;
	} else if ($order['invoice_address']['title'] === 'Mrs') {
		echo __d('user', 'Dear Mrs. {0} {1},', $order['invoice_address']['first_name'], $order['invoice_address']['last_name']);		
		echo $LF;
	} 
	if ($onRequest) {
		echo __d('user', 'We have cancelled your registration {0} on your request.', $order['invoice']);
	} else {
		echo __d('user', 'We have cancelled your registration {0} because we have not received your payment in due time.', $order['invoice']);
		echo $LF;
		echo __d('user', 'If you think this is an error please contact {0} immediately!', $shopSettings['email']);
	}
?>

	

