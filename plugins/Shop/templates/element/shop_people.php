<?php
	$events = array('S' => false, 'D' => false, 'X' => false, 'T' => false);

	if (!empty($competitions)) {
		foreach ($competitions as $c) {
			$events[$c['type_of']] = true;
		}

		$competitions[null] = array('name' => null);
	}
?>

<?php
	$players = array();
	$accs = array();
	$wantClassification = array();

	foreach ($people as $person) {
		if (!empty($person['type'])) {
			if ($person['type'] === 'PLA') {
				$players[] = $person;
				if (($person['ptt_class'] ?? 0) < 0)
					$wantClassification[] = $person;
			}
			if ($person['type'] === 'ACC')
				$accs[] = $person;
		} else {
			if ($types[$person['type_id']] === 'PLA') {
				$players[] = $person;
				if (($person['ptt_class'] ?? 0) < 0)
					$wantClassification[] = $person;
			}
			if ($types[$person['type_id']] === 'ACC')
				$accs[] = $person;
		}
	}

	if (!function_exists('cmp_shop_people')) {
		function cmp_shop_people($p1, $p2) {
			if (empty($p1['cancelled']) && !empty($p2['cancelled']))
					return -1;

			if (!empty($p1['cancelled']) && empty($p2['cancelled']))
					return +1;

			$ret = strcmp($p1['last_name'], $p2['last_name']);
			if ($ret == 0)
				$ret = strcmp($p1['first_name'], $p2['first_name']);

			return $ret;
		}
	}

	uasort($people, 'cmp_shop_people');
	uasort($accs, 'cmp_shop_people');
?>

<?php
	if (count($players) > 0) {
?>
	<h3><?php echo __d('user', 'Players');?></h3>
	<table class="ttm-table">
		<thead>
			<tr>
				<th><?php echo __d('user', 'Pos');?></th>
				<th><?php echo __d('user', 'Family name');?></th>
				<th><?php echo __d('user', 'Given name');?></th>
				<th><?php echo __d('user', 'Association');?></th>
				<th><?php echo __d('user', 'Sex');?></th>
				<th><?php echo __d('user', 'Born');?></th>
				<th><?php echo __d('user', 'Para'); ?></th>
				<?php if ($events['S']) echo '<th>' . __d('user', 'Singles') . '</th>';?>
				<?php if ($events['D']) echo '<th>' . __d('user', 'Doubles') . '</th>';?>
				<?php if ($events['X']) echo '<th>' . __d('user', 'Mixed') . '</th>';?>
				<?php if ($edit) echo '<th class="actions">' . __d('user', 'Actions') . '</th>';?>
			</tr>
		</thead>
		<tbody>
		<?php
			$i = 0;
			$idx = 0;
			foreach ($players as $person) {
				++$idx;
				$class = null;
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}

				$cancelled = null;
				if (!empty($person['cancelled']))
					$cancelled = ' class="cancelled"';

				$dob = $person['dob'];
				if (empty($dob))
					$year = '';
				else if (strlen($dob) === 4)
					$year = $dob;
				else if (strpos($dob, '-00-00') !== false)
					$year = substr ($dob, 0, 4);
				else
					$year = date('Y', strtotime($person['dob']));
				
				$ptt_class = $person['ptt_class'] ?? 0;
				if ($ptt_class < 0)
					$ptt_class = 't.b.c.';
				else if ($ptt_class == 0)
					$ptt_class = '';

				echo '<tr ' . $class . '>';
					echo '<td ' . $cancelled . '>' . $idx . '</td>';
					echo '<td ' . $cancelled . '>' . $person['last_name'] . '</td>';
					echo '<td ' . $cancelled . '>' . $person['first_name'] . '</td>';
					echo '<td ' . $cancelled . '>' . $nations[$person['nation_id']] . '</td>';
					echo '<td ' . $cancelled . '>' . $person['sex'] . '</td>';
					echo '<td ' . $cancelled . '>' . $year . '</td>';
					echo '<td ' . $cancelled . '>' . $ptt_class . '</td>';
					if ($events['S']) echo '<td ' . $cancelled . '>' . $competitions[$person['single_id']]['name'] . '</td>';
					if ($events['D']) echo '<td ' . $cancelled . '>' . $competitions[$person['double_id']]['name'] . '</td>';
					if ($events['X']) echo '<td ' . $cancelled . '>' . $competitions[$person['mixed_id']]['name'] . '</td>';
					if ($events['T']) echo '<td ' . $cancelled . '>' . $competitions[$person['team_id']]['name'] . '</td>';
					if ($edit)
						echo '<td class="actions">' . $this->Html->link(__d('user', 'Remove'), array('action' => 'remove_person', $idx - 1))  . '</td>';
				echo '</tr>';
			}
		?>
		</tbody>
	</table>
	<?php 
		if (count($wantClassification) > 0) {
			echo '<br>';
			echo __d('user', 'Players with para class \'t,b,c,\' (to be classified) need to undergo classification before they can participate in para events.');
			echo __d('user', 'Classification will take place on the free day Wednesday, July 10th 2024.');
			echo __d('user', 'To prepare follow the steps below:');
			echo '<ul>';
			echo '<li>';
			echo __d('user', 'Please send their passports for registration using this form: {0}', 'https://forms.worldtabletennis.com/form/para_table_tennis_new_player_registration_en');
			echo '</li>';
			echo '<li>';
			echo __d('user', 'Please send the medical documentation using this form: {0}', 'https://forms.worldtabletennis.com/form/para_table_tennis_new_player_registration_en');
			echo '</li>';
			echo '</ul>';
		} 
	?>
	<br>

<?php
	}
?>

<?php
	if (count($accs) > 0) {
?>
	<h3><?php echo __d('user', 'Accompanying People');?></h3>
	<table>
	<tr>
		<th><?php echo __d('user', 'Pos');?></th>
		<th><?php echo __d('user', 'Family name');?></th>
		<th><?php echo __d('user', 'Given name');?></th>
		<th><?php echo __d('user', 'Country');?></th>
		<th><?php echo __d('user', 'Sex');?></th>
		<?php if ($edit) echo '<th>' . __d('user', 'Actions') . '</th>';?>
	</tr>

	<?php
		$i = 0;
		$idx = 0;
		foreach ($accs as $person) {
			++$idx;
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}

			$cancelled = null;
			if (!empty($person['cancelled']))
				$cancelled = ' class="cancelled"';

			echo '<tr ' . $class . '>';
				echo '<td ' . $cancelled . '>' . $idx . '</td>';
				echo '<td ' . $cancelled . '>' . $person['last_name'] . '</td>';
				echo '<td ' . $cancelled . '>' . $person['first_name'] . '</td>';
				echo '<td ' . $cancelled . '>' . $nations[$person['nation_id']] . '</td>';
				echo '<td ' . $cancelled . '>' . $person['sex'] . '</td>';
				if ($edit)
					echo '<td class="actions">' . $this->Html->link(__d('user', 'Remove'), array('action' => 'remove_person', $idx - 1))  . '</td>';
			echo '</tr>';
		}
	?>
	</table>

	<br>

<?php
	}
?>


