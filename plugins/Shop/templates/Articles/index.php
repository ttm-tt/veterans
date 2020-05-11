<?php
	$wantFrom  = false;
	$wantUntil = false;
	$wantWait  = false;
	$wantAllocated = false;
	foreach ($articles as $article) {
		$wantFrom |= !empty($article['available_from']);
		$wantUntil |= !empty($article['available_until']);
		$wantWait  |= !empty($wait[$article['id']]);
		$wantAllocated |= !empty($allocated[$article['id']]);
	}
?>

<div class="articles index">
	<h2><?php echo __('Articles');?></h2>
	<table>
	<tr>
		<th><?php echo $this->Paginator->sort('description');?></th>
		<th><?php echo $this->Paginator->sort('price', __('Price'));?></th>
		<th><?php echo $this->Paginator->sort('available');?></th>
		<?php 
			if ($wantFrom) {
				echo '<th>';  
				echo $this->Paginator->sort('available_from,', __('From'));
				echo '</th>';
			}
		?>
		<?php 
			if ($wantUntil) {
				echo '<th>';  
				echo $this->Paginator->sort('available_until', __('Until'));
				echo '</th>';
			}
		?>
		<th><?php echo __('Sold');?></th>
		<th><?php echo __('Pending');?></th>
		<?php 
			if ($wantAllocated) { 
				echo '<th>';  
				echo __('Allotted');
				echo '</th>';
			}
		?>
		
		<?php 
			if ($wantWait)
				echo '<th>' . __('Wait') . '</th>';
		?>
		<th><?php echo $this->Paginator->sort('visible');?></th>
		<th><?php echo $this->Paginator->sort('modified', __('Updated'));?></th>
		<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$mayChart = $Acl->check($current_user, 'Articles/chart');
	$mayView = $Acl->check($current_user, 'Articles/view');
	$mayEdit = $Acl->check($current_user, 'Articles/edit');
	$mayDelete = $Acl->check($current_user, 'Articles/delete');

	$i = 0;
	foreach ($articles as $article):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $article['description']; ?></td>
		<td class="currency"><?php echo $article['price'] . '&nbsp;' . $shopSettings['currency']; ?></td>
		<td class="number"><?php echo $article['available']; ?></td>		
		<?php
			if ($wantFrom)
				echo '<td>' . $article['available_from'] . '</th>'; 
		?>
		<?php
			if ($wantUntil)
				echo '<td>' . $article['available_until'] . '</th>'; 
		?>
		<td class="number"><?php if (empty($sold[$article['id']])) echo ''; else echo $sold[$article['id']];?></td>
		<td class="number"><?php if (empty($pend[$article['id']])) echo ''; else echo $pend[$article['id']];?></td>
		<?php
			if ($wantAllocated) {
				echo '<td class="number">';				
				echo $allocated[$article['id']] ?? '';				
				echo '</td>';
			}
		?>
		<?php
			if ($wantWait) {
				echo '<td class="number">';
			
				if (empty($wait[$article['id']])) 
					echo ''; 
				else 
					echo $wait[$article['id']];
				
				echo '</td>';
			}
		?>
		<td class="boolean"><?php echo $article['visible'] ? '&#x2713;' : ''; ?></td>
		<td><?php echo $article['modified']; ?></td>
		<td class="actions">
			<?php if ($mayChart) echo $this->Html->link(__('Charts'), array('action' => 'chart', $article['id'])); ?>
			<?php if ($mayView) echo $this->Html->link(__('View'), array('action' => 'view', $article['id'])); ?>
			<?php if ($mayEdit) echo $this->Html->link(__('Edit'), array('action' => 'edit', $article['id'])); ?>
			<?php if ($mayDelete) echo $this->Html->link(__('Delete'), array('action' => 'delete', $article['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $article['description'])]); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<?php echo $this->element('paginator'); ?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php 
			if ($Acl->check($current_user, 'Articles/add')) 
				echo '<li>' . $this->Html->link(__('New Article'), array('action' => 'add')) . '</li>'; 
			
			if ($Acl->check($current_user, 'Allotments/index'))
				echo '<li>' . $this->Html->link(__('List Allotments'), array('controller' => 'allotments', 'action' => 'index')) . '</li>';

			if ($Acl->check($current_user, 'Orders/index'))
				echo '<li>' . $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')) . '</li>';
			
			if ($Acl->check($current_user, 'Orders/settings'))
				echo '<li>' . $this->Html->link(__('Settings'), array('controller' => 'orders', 'action' => 'settings')) . '</li>';
		?>
	</ul>
<?php $this->end(); ?>
