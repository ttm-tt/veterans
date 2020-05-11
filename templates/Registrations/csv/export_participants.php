<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	// Echo registration (?) export
	$headers = array(
		'#Last Name',
		'First Name',
		'Sex',
		'Association',
		'Function',
		'Date of Birth',
		'Single',
		'Double',
		'Double Partner',
		'Double Partner Association',
		'Double Partner Born',
	);
	
	foreach ($articles as $a) {
		if (!$a['visible'])
			continue;
		
		$headers[] = $a['description'];
	}
	
	$headers[] = 'Date Added';
	$headers[] = 'Cancelled';


	echo implode("\t", $headers);
	
	echo "\n";

	foreach ($registrations as $row) {
		$tmp = array(
			$row['person']['last_name'],
			$row['person']['first_name'],
			$row['person']['sex'],	
			$nations[$row['person']['nation_id']],
			$types[$row['type_id']]
		);
		
		if (!empty($row['participant'])) 
			$tmp[] = $row['person']['dob'];
		else
			$tmp[] = "";
		
		if (!empty($row['participant']['single_id']))
			$tmp[] = $competitions[$row['participant']['single_id']];
		else
			$tmp[] = "";
		if (!empty($row['participant']['double_id']))
			$tmp[] = $competitions[$row['participant']['double_id']];
		else
			$tmp[] = "";
		if (!empty($row['participant']['double_partner_id'])) {
			$tmp[] = $row['participant']['double_partner']['person']['display_name'];
			$tmp[] = $nations[$row['participant']['double_partner']['person']['nation_id']];
			$tmp[] = date('Y', strtotime($row['participant']['double_partner']['person']['dob']));
		} else {
			$tmp[] = "";
			$tmp[] = "";
			$tmp[] = "";
		}
		
		$items = array();
		foreach ($row['OrderArticle'] as $item) {
			if (!empty($item['cancelled']))
				continue;
			
			$items[$item['article_id']] = $item['quantity'];
		}
		
		foreach ($articles as $a) {
			if (!$a['visible'])
				continue;
			
			if (empty($items[$a['id']]))
				$tmp[] = 0;
			else
				$tmp[] = $items[$a['id']];
		}

		if (count($row['OrderArticle']) > 0)
			$tmp[] = $row['OrderArticle'][0]['created']->format('Y-m-d');
		else
			$tmp[] = '';
		$tmp[] = $row['cancelled'];
		
		echo implode("\t", $tmp) . "\n";
	}
?>
