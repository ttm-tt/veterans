<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use App\Model\Table\GroupsTable;
?>

<div class="registrations index">
<h2><?php echo __('Count Participants');?></h2>
	<?php
		echo '<div class="filter">';
		echo '<fieldset>';
		echo '<legend>' . __d('user', 'Filter') . '</legend>';
		echo '<table>';

		if (false && empty($current_user['nation_id'])) {
			echo $this->element('filter', [
				'label'=> __d('user', 'Association'),
				'id' => 'nation_id',
				'options' => $nations
			]);
		}

		echo $this->element('filter', [
			'label'=> __d('user', 'Sex'),
			'id' => 'sex',
			'options' => [
				'F' => __('Women'),
				'M' => __('Men'),
				'X' => __('Mixed')
			 ]
		]);

		echo $this->element('filter', [
			'label'=> __d('user', 'Type'),
			'id' => 'type_of',
			'options' => [
				'S' => __('Singles'),
				'D' => __('Doubles'),
				'X' => __('Mixed'),
				'T' => __('Teams')				
			]
		]);
		
		if ($hasRootPrivileges || $isCompetitionManager || $isOrganizer) {
			echo $this->element('filter', [
				'label'=> __d('user', 'Partner'),
				'id' => 'partner',
				'options' => [
					'wanted' => __d('user', 'Wanted'),
					'requested' => __d('user', 'Requested As'),
					'multiple' => __d('user', 'Multiple'),
					'unconfirmed' => __d('user', 'Not Confirmed'),
					'confirmed' => __d('user', 'Confirmed')
				]
			]);
		}

		echo '</table>' . "\n";
		echo '</fieldset></div>' . "\n";
	?>

	<div class="hint">
		<?= __d('user', 'Only registered and paid for players are counted but not reserved positions.'); ?>
	</div>

	<table>
		<tr>
			<th><?php echo __('Association'); ?></th>

			<?php foreach ($competitions as $c) {
				echo '<th>' . $c['name'] . '</th>';
			} ?>

			<th><?php echo __('Total'); ?></th>
		</tr>
		<?php
			$i = 0;
			$total = array();

			foreach ($nationCounts as $count):
				if ($count['Nation']['id'] == 0) {
					$total = $count;
					continue;
				}
				$class = null;
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $count['Nation']['description']; ?></td>

			<?php foreach ($competitions as $c) {
				echo '<td>' . $count['Count'][$c['id']] . '</td>';
			} ?>

			<td><?php echo $count['Count']['total']; ?></td>
		</tr>
		<?php endforeach; ?>
		<?php
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $total['Nation']['description']; ?></td>

			<?php foreach ($competitions as $c) {
				echo '<td>' . $total['Count'][$c['id']] . '</td>';
			} ?>
			<td> <?php echo $total['Count']['total']; ?> </td>
		</tr>
	</table>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php
			if ($Acl->check($current_user, 'Registrations/add'))
				echo '<li>' . $this->Html->link(__('New Registration'), array('action' => 'add')) . '</li>';
			if ($Acl->check($current_user, 'Registrations/index'))
				echo '<li>' . $this->Html->link(__('List Registrations'), array('action' => 'index')) . '</li>';
			if ($Acl->check($current_user, 'Registrations/list_partner_wanted'))
				echo '<li>'. $this->Html->link(__('List Partner Wanted'), array('action' => 'list_partner_wanted')) . '</li>';
			if ($Acl->check($current_user, 'Registrations/export_count'))
				echo '<li>'. $this->Html->link(__('Export'), array('action' => 'export_count.csv')) . '</li>';
		?>
	</ul>
	
<?php $this->end(); ?>
