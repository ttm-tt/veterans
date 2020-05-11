<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	// Echo registration (?) export
	echo implode("\t", array(
		'#Last Name',
		'First Name',
		'Sex',
		'Association',
		'Function',
		'Extern ID',
		'Date of Birth',
		'Start No.',
		'Single',
		'Double',
		'Double Partner Extern ID',
		'Double Partner Start No.',
		'Double Partner Last Name',
		'Double Partner First Name',
		'Double Partner Association',
		'Double Partner Confirmed',
		'Mixed',
		'Mixed Partner Extern ID',
		'Mixed Partner Start No.',
		'Mixed Partner Last Name',
		'Mixed Partner First Name',
		'Mixed Partner Association',
		'Mixed Partner Confirmed',
		'Team',
		'Email',
		'Phone',
		'Invoice',
		'Cancellation Date'
	));

	echo "\n";

	foreach ($data as $row) {
		$tmp = array(
			$row['person']['last_name'],
			$row['person']['first_name'],
			$row['person']['sex'],	
			$nations[$row['person']['nation_id']],
			$types[$row['type_id']]
		);
		
		if (isset($row['person']['extern_id']))
			$tmp[] = $row['person']['extern_id'];
		else
			$tmp[] = '';

		if (isset($row['participant'])) {
			$tmp[] = $row['person']['dob'];
			$tmp[] = $row['participant']['start_no'];
			$tmp[] = $row['Single'];
			$tmp[] = $row['Double'];
			$tmp[] = $row['DoublePartner']['person']['extern_id'];
			$tmp[] = $row['DoublePartner']['participant']['start_no'];
			$tmp[] = $row['DoublePartner']['person']['last_name'];
			$tmp[] = $row['DoublePartner']['person']['first_name'];
			$tmp[] = $nations[$row['DoublePartner']['person']['nation_id']];
			$tmp[] = $row['DoublePartner']['confirmed'];
			$tmp[] = $row['Mixed'];
			$tmp[] = $row['MixedPartner']['person']['extern_id'];
			$tmp[] = $row['MixedPartner']['participant']['start_no'];
			$tmp[] = $row['MixedPartner']['person']['last_name'];
			$tmp[] = $row['MixedPartner']['person']['first_name'];
			$tmp[] = $nations[$row['MixedPartner']['person']['nation_id']];
			$tmp[] = $row['MixedPartner']['confirmed'];
			$tmp[] = $row['Team'];
		}
		
		// For non-players
		$tmp = array_pad($tmp, 24, '');
			
		$tmp[] = $row['person']['email'];
		$tmp[] = $row['person']['phone'];

		if (!empty($invoices[$row['person_id']])) {
			$tmp[] = $invoices[$row['person_id']];
		} else {
			$tmp[] = '';
		}
		
		$tmp[] = $row['cancelled'];

		echo implode("\t", $tmp) . "\n";
	}
?>
