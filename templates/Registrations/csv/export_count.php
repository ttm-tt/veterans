<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	// Caption
	echo __d('user', 'Association') . "\t";
	foreach ($competitions as $c) {
		echo $c['name'] . "\t";
	}
	
	echo __d('user', 'Total');

	echo "\n";

	$total = array();

	foreach ($nationCounts as $count) {
		if ($count['Nation']['id'] == 0) {
			$total = $count;
			continue;
		}
			
		echo $count['Nation']['name'] . "\t";

		foreach ($competitions as $c) {
			echo $count['Count'][$c['id']] . "\t";
		}

		echo $count['Count']['total']; 
	
		echo "\n";
	}

	echo $total['Nation']['name'] . "\t";

	foreach ($competitions as $c) {
		echo $total['Count'][$c['id']] . "\t";
	}

	echo $total['Count']['total']; 

	echo "\n";
?>
