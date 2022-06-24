<?php
	$headers = array(
		'#Invoice',
		'Email',
		'Given Name',
		'Family Name',
		'Street',
		'ZIP',
		'City',
		'Country',
		'Country ISO-3',
		'Date',
		'Date Paid',
		'Status',
		'Amount'
	);
	
	$names = array();
	foreach ($articles as $article) {
		while (count($names) < $article['sort_order'])
			$names[] = '';
		$names[$article['sort_order'] - 1] = $article['name'];
	}
	
	$headers = array_merge($headers, $names);
	$headers[] = 'Order ID';
	
	echo implode("\t", $headers);
	echo "\n";

	$locale = Locale::acceptFromHttp(filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE'));
	if ($locale === null)
		$locale = 'en_US';
	
	$nf = NumberFormatter::create($locale, NumberFormatter::DECIMAL);
	if (!$nf)
		$nf = NumberFormatter::create('en_US', NumberFormatter::DECIMAL);
	
	$nf->setAttribute(NumberFormatter::DECIMAL_ALWAYS_SHOWN, 1);
	$nf->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
	$nf->setAttribute(NumberFormatter::GROUPING_USED, 0);
	
	foreach ($orders as $order) {
		$columns = array(
			$order['invoice'],
			$order['email'],
			$order['invoice_address']['last_name'] ?? '',
			$order['invoice_address']['first_name'] ?? '',
			$order['invoice_address']['street'] ?? '',
			$order['invoice_address']['zip_code'] ?? '',
			$order['invoice_address']['city'] ?? '',
			empty($order['invoice_address']['country_id']) ? '' : $countries[$order['invoice_address']['country_id']],
			date('Y-m-d', strtotime($order['created'])),
			date('Y-m-d', strtotime($order['invoice_paid'])),
			$stati[$order['order_status_id']],			
			$nf->format((double) $order['total'])
		);
		
		$values = array();
		$people = array();
		foreach ($order['order_articles'] as $item) {
			if (!empty($item['cancelled']))
				continue;
			
			while (count($values) < $sort_order[$item['article_id']])
				$values[] = 0;
			
			$values[$sort_order[$item['article_id']] - 1] += $item['quantity'];
			
			if (!empty($item['detail'])) {
				$person = unserialize($item['detail']);
				$people[] = $person['last_name'] . ', ' . $person['first_name'];
			}
		}
		
		while (count($values) < count($names))
			$values[] = 0;
		
		$columns = array_merge($columns, $values);
		
		$columns[] = $order['id'];
		
		// echo implode("\t", array_merge($columns, $values, $people));
		echo implode("\t", $columns);
		echo "\n";
	}
?>