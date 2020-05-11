<div class="allotments index">
	<h2><?php echo __('Allotments');?></h2>
	<table>
	<tr>
		<th><?php echo $this->Paginator->sort('article.name', __('Article'));?></th>
		<th><?php echo $this->Paginator->sort('user.name', __('User'));?></th>
		<th><?php echo $this->Paginator->sort('allotment', __('Allotment'));?></th>
		<th><?php echo $this->Paginator->sort('count', __('Used'));?></th>
		<th><?php echo $this->Paginator->sort('modified', __('Updated'));?></th>
		<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$mayView = $Acl->check($current_user, 'Allotments/view');
	$mayEdit = $Acl->check($current_user, 'Allotments/edit');
	$mayDelete = $Acl->check($current_user, 'Allotments/delete');

	$i = 0;
	foreach ($allotments as $allotment):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $allotment['article']['description']; ?></td>
		<td><?php echo $allotment['user']['username']; ?></td>
		<td><?php echo $allotment['allotment']; ?></td>		
		<td><?php echo $allotment['count']; ?></td>
		<td><?php echo $allotment['modified']; ?></td>
		<td class="actions">
			<?php if ($mayView) echo $this->Html->link(__('View'), array('action' => 'view', $allotment['id'])); ?>
			<?php if ($mayEdit) echo $this->Html->link(__('Edit'), array('action' => 'edit', $allotment['id'])); ?>
			<?php if ($mayDelete) echo $this->Html->link(__('Delete'), array('action' => 'delete', $allotment['id']), ['confirm' => sprintf(__('Are you sure you want to delete this allotment?'))]); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<?php echo $this->element('paginator'); ?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php 
			if ($Acl->check($current_user, 'Allotments/add')) 
				echo '<li>' . $this->Html->link(__('New Allotment'), array('action' => 'add')) . '</li>'; 
			
			if ($Acl->check($current_user, 'Articles/index'))
				echo '<li>' . $this->Html->link(__('List Articles'), array('controller' => 'articles', 'action' => 'index')) . '</li>';
			
			if ($Acl->check($current_user, 'Orders/index'))
				echo '<li>' . $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')) . '</li>';
		?>
	</ul>
<?php $this->end(); ?>

