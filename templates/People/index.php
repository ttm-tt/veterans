<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="people index">
	<h2><?php echo __('People');?></h2>
	<?php
			echo '<div class="filter">';
			echo '<fieldset>';
			echo '<legend>' . __d('user', 'Filter') . '</legend>';
			echo '<table>';

		if (empty($current_user['nation_id'])) {
			echo $this->element('filter', [
				'label'=> __d('user', 'Association'),
				'id' => 'nation_id',
				'options' => $nations
			]);
		}

		echo $this->element('filter', [
			'label' => __('Sex'),
			'id' => 'sex',
			'options' => [
				'F' => __('Women'),
				'M' => __('Men')
			]
		]);		
		
		echo $this->element('filter', [
			'label' => __d('user', 'Paralympic'),
			'id' => 'para',
			'options' => ['no' => __d('user', 'No'), 'yes' => __d('user', 'Yes')],
		]);

		echo '<tr><td><label class="filter">' . __('Last&nbsp;name') .	'</label></td><td>';

		foreach ($allchars as $idx => $chars) {
			if (count($chars) == 0)
				continue;

			if ($idx > 0)
				echo '<br>';

			if ($idx == 0) {
				if (isset($last_name))
					echo $this->Html->link(__('all'), ['?' => ['last_name' => '*']]);
				else
					echo __('all');
			} else {
				$name = str_replace(' ', '_', mb_convert_case(mb_strtolower(mb_substr($chars[0], 0, mb_strlen($chars[0]) - 1)), MB_CASE_TITLE));

				if (mb_strlen($last_name) >= mb_strlen($chars[0]))
					echo $this->Html->link($name, ['?' => ['last_name' => urlencode(str_replace(' ', '_', mb_substr($chars[0], 0, mb_strlen($chars[0]) - 1)))]]);
				else
					echo $name;
			}

			foreach ($chars as $char) {
				$name = str_replace(' ', '_', mb_convert_case(mb_strtolower($char), MB_CASE_TITLE));

				if (mb_substr($last_name ?? '', 0, mb_strlen($char)) == $char)
					echo ' ' . $name;
				else
					echo ' ' . $this->Html->link($name, ['?' => ['last_name' => urlencode(str_replace(' ', '_', $char))]]);
			}
		}
		
		echo '</td></tr>';
		
		if (!empty($user_id)) {
			echo '<tr><td><label class="filter">' . __('Username') . '</td><td>';
			echo $this->Html->link(__('all'), array('user_id' => 0));
			echo ' ' . $username;
		}

		echo '</table>' . "\n";
		echo '</fieldset></div>' . "\n";
	?>
	<table>
	<tr>
			<th><?php echo $this->Paginator->sort('People.display_name', __('Name'));?></th>
			<?php if (empty($current_user['nation_id'])) { ?>
				<th><?php echo $this->Paginator->sort('Nations.name', __('Association'));?></th> 
			<?php } ?>
			<th><?php echo $this->Paginator->sort('People.sex', __('Sex'));?></th>
			<th><?php echo $this->Paginator->sort('People.dob', __('Born'));?></th>
			<?php if (($para ?? 'yes') === 'yes') { ?>
				<th><?php echo $this->Paginator->sort('ptt_class', __('Para TT Class'));?></th>
			<?php } ?>
			<?php $wrid = __('Reg. ID'); ?>
			<?php if ($hasRootPrivileges) { ?>
				<th><?php echo $this->Paginator->sort('People.extern_id', $wrid);?></th>
			<?php } ?>
			<?php if ($hasRootPrivileges) { ?>
				<th><?php echo $this->Paginator->sort('Users.username', __('User'));?></th>
			<?php } ?>
			<th><?php echo $this->Paginator->sort('People.modified', __('Updated'));?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$mayView = $Acl->check($current_user, 'People/view');
	$mayEdit = $Acl->check($current_user, 'People/edit');
	$mayDelete = $Acl->check($current_user, 'People/delete');

	$i = 0;
	foreach ($people as $person):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $person['display_name']; ?>&nbsp;</td>
		<?php if (empty($current_user['nation_id'])) { ?>
			<td><?php echo $person['nation']['name'];?>&nbsp;</td>
		<?php } ?>
		<td><?php echo $person['sex']; ?>&nbsp;</td>
		<td><?php echo ($person['born'] ? $person['born'] : ''); ?>&nbsp;</td>
		<?php if (($para ?? 'yes') === 'yes') { ?>
			<td>
				<?php
					if (($person->ptt_class ?? 0) == 0)
						echo __('None');
					else if ($person->ptt_class == -1)
						echo __('t.b.c');
					else
						echo $person['ptt_class']; 
				?>&nbsp;
			</td>
		<?php } ?>
		<?php if ($hasRootPrivileges) { ?>
			<td><?php echo $person['extern_id']; ?>&nbsp;</td>
		<?php } ?>
		<?php if ($hasRootPrivileges) { ?>
			<td><?php 
				if (!empty($person['user']['username'])) 
					echo $this->Html->link($person['user']['username'], array('controller' => 'users', 'action' => 'view', $person['user']['id'])); 
			?>&nbsp;</td>
		<?php } ?>
		<td><?php echo $person['modified']; ?>&nbsp;</td>
		<td class="actions">
			<?php if ($mayView) echo $this->Html->link(__('View'), array('action' => 'view', $person['id'])); ?>
			<?php if ($mayEdit) echo $this->Html->link(__('Edit'), array('action' => 'edit', $person['id'])); ?>
			<?php if ($mayDelete && count($person->registrations) === 0) echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $person['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $person['display_name'])]); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<?php echo $this->element('paginator'); ?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php 
			if ($Acl->check($current_user, 'People/add')) 
				echo '<li>' . $this->Html->link(__('New Person'), array('action' => 'add')) . '</li>'; 
		?>
		<?php 
			// if ($Acl->check($current_user, 'People/update')) 
			// 	echo '<li>' . $this->Html->link(__('Update People'), array('action' => 'update')) . '</li>'; 
		?>
	</ul>
<?php $this->end(); ?>
