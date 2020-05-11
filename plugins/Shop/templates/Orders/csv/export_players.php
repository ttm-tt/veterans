<?php
	// Format dob
	// dob should be Y-m-d, but could be an array or object, too
	function formatDate($dob) {
		if (is_string($dob))
			return $dob;
		else if ($dob instanceof \Cake\I18n\Date)
			return $dob->format('Y-m-d');
		else if ($dob instanceof \Cake\I18n\FrozenDate)
			return $dob->format('Y-m-d');
		else if (is_array($dob))
			return $dob['year'] . '-' . $dob['month'] . '-' . $dob['day'];
		else
			return '';
	}
	
	
	// Competitions may not have all events
	foreach (['S', 'D', 'X', 'T'] as $type) {
		$competitions['F'] += array($type => []);
		$competitions['M'] += array($type => []);
	}
	
	// Types
	
	
	$headers = array(
		'#Last Name',
		'First Name',
		'Sex',
		'Association',
		'Function',
		'Date of Birth',
		'Single',
		'Double',
		'Mixed',
		'Team',
		'Email',
		'Phone',
		'Invoice'
	);
	
	echo implode("\t", $headers);
	echo "\n";
	
	foreach ($articles as $article) {
		if (empty($article['detail']))
			continue;
		
		$person = unserialize($article['detail']);
		$person += ['dob' => '', 'email' => '', 'phone' => ''];
		
		if (!isset($person['type']))
			$person['type'] = $article['article']['name'];
		
		$columns = array(
			$person['last_name'],
			$person['first_name'],
			$person['sex'],
			$nations[$person['nation_id']],
			$person['type'],
			formatDate($person['dob'])
		);
		
		// Fake a year so non-players don't play
		$year = $person['type'] === 'PLA' ? date('Y', strtotime(formatDate($person['dob']))) : 9999;
		$singles = '';
		$doubles = '';
		$mixed = '';
		$teams = '';
		
		foreach ($competitions[$person['sex']]['S'] as $born => $name) {
			if ($born < $year)
				break;
			$singles = $name;
		}
		foreach ($competitions[$person['sex']]['D'] as $born => $name) {
			if ($born < $year)
				break;
			$doubles = $name;
		}
		foreach ($competitions[$person['sex']]['X'] as $born => $name) {
			if ($born < $year)
				break;
			$mixed = $name;
		}
		foreach ($competitions[$person['sex']]['T'] as $born => $name) {
			if ($born < $year)
				break;
			$teams = $name;
		}
		
		$columns[] = $singles;
		$columns[] = $doubles;
		$columns[] = $mixed;
		$columns[] = $teams;
		$columns[] = $person['email'];
		$columns[] = $person['phone'];
		$columns[] = $article['order']['invoice'];
		
		echo implode("\t", $columns);
		echo "\n";
	}
?>