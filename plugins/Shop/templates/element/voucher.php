<?php
use App\Model\Table\TypesTable;
?>

<div id="header">
	<div id="logo">
	</div>
	<div id="transaction">
		<table class="dl">
			<tr>
				<td class="dt"><?php echo __d('user', 'Invoice date');?></td>
				<td class="dd"><?php echo date('d.m.Y', strtotime($order['created']));?></td>
			</tr>
			<tr>
				<td class="dt"><?php echo __d('user', 'Invoice no');?></td>
				<td class="dd"><?php echo $order['invoice'];?></td>
			</tr>
		</table>
	</div>
	<br style="clear:both;">
</div>
<div id="caption">
	<div id="title">
		<h1><?php echo __d('user', 'Voucher');?></h1>
		<h2><?php echo __d('user', 'Please present this voucher at the accreditation.'); ?></h4>
	</div>
</div>	
<div id="content">
	<h3><?php echo __d('user', 'People');?></h3>
	
	<?php 
		if (!empty($people)) {
	?>
	
	<table>
	<tr>
		<th><?php echo __d('user', 'Reg. ID');?></th>
		<th><?php echo __d('user', 'Family name');?></th>
		<th><?php echo __d('user', 'Given name');?></th>
		<th><?php echo __d('user', 'Association');?></th>
		<th><?php echo __d('user', 'Type');?></th>
	</tr>
	
	<?php
		$i = 0;
		foreach ($registrations as $registration) {
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
			
			if ( $registration['type_id'] != TypesTable::getPlayerId() &&
				 $registration['type_id'] != TypesTable::getAccId() )
				continue;

			$cancelled = null;
			if (!empty($registration['cancelled']))
				$cancelled = ' class="cancelled"';

			echo '<tr ' . $class . '>';
				echo '<td ' . $cancelled . '>' . $registration['person']['extern_id'] . '</td>';
				echo '<td ' . $cancelled . '>' . $registration['person']['last_name'] . '</td>';
				echo '<td ' . $cancelled . '>' . $registration['person']['first_name'] . '</td>';
				echo '<td ' . $cancelled . '>' . $nations[$registration['person']['nation_id']] . '</td>';
				echo '<td ' . $cancelled . '>';
				if ($registration['type_id'] == TypesTable::getPlayerId())
					echo 'PLA';
				else
					echo 'ACC';				
				echo '</td>';
			echo '</tr>';
		}
	
	?>

	<?php
		}
	?>
	</table>
	<p></p>
	<br>
	<p></p>
	
	<h3><?php echo __d('user', 'Additional Items');?></h3>
	
	<table>
		<tr>
			<th class="pos"><?php echo __d('user', 'Pos');?></th>
			<th class="number"><?php echo __d('user', 'Qty')?></th>
			<th><?php echo __d('user', 'Description')?></th>
		</tr>

		<?php
			$total = 0.;
			$fee = 0.;
			$i = 0;
		
			foreach ($items as $item) {
				if ($item['quantity'] == 0)
					continue;

				// If not listing cancelled items, skip those which are cancelled.
				// If listing cancelled items, skip those which are not cancelled.
				if (empty($storno) != empty($item['cancelled']))
					continue;

				// Skip items which are not visible
				if ($item['article']['visible'] == false)
					continue;

				$class = null;
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}

				echo '<tr ' . $class . '>';
				echo '<td>' . $i . '</td>';
				echo '<td>' . $item['quantity'] . '</td>';
				echo '<td>' . $item['description'] . '</td>';
				echo '</tr>';	
			}
		?>
	</table>	
		
</div>
<div id="footer">
</div>
