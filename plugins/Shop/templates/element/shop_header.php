<div class="wizardnav">
<?php
	$titles = array(
		'people' => __d('user', 'Register'),
		'buy' => __d('user', 'Buy'),
		'address' => __d('user', 'Address'),
		'review' => __d('user', 'Review'),
		'payment_selection' => __d('user', 'Payment'),
		'creditcard' => __d('user', 'Billing'),
		'banktransfer' => __d('user', 'Billing'),
		'success' => __d('user', 'Completed'),
		'waiting_list' => __d('user', 'Waiting List')
	);
	
	if ($isTest ?? 0) {
		echo '<h2>TEST DO NOT USE</h2>';
	}

	if (($this->Wizard->config('expectedStep')) !== null) {
		$this->set('expectedStep', $this->Wizard->config('expectedState'));
		echo $this->Wizard->progressMenu($titles);
	}
?>
</div>
