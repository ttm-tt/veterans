<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="registration form">
<?php echo $this->Form->create(null, $warnOverwrite? array('onsubmit' => 'return confirm("All existing player numbers will be lost");') : array());?>
	<fieldset>
		<legend><?php echo __('Assign Start Numbers'); ?></legend>
		<div id="criteria" style="display:table;width:66%">
			<div style="display:table-row">
				<div style="display:table-cell">
					<?php 
						echo __('Start with Number');
					?>
				</div>	
				<div style="display:table-cell">
					<?php
						echo $this->Form->text('offset', array(
							'value' => 1
						));
					?>
				</div>	
			</div>
			<br>
			<div style="display:table-row">
				<div style="display:table-cell">
					<label style="text-align:left;width:100%">Criteria</label>
				</div>	
				<div style="display:table-cell">
					<label style="text-align:left;width:100%">Group Size</label>
				</div>	
			</div>
			<div style="display:table-row">
				<div style="display:table-cell">
					<?php 
						echo $this->Form->select('first', $sort_options, array(
							'empty' => __('None'),
							'style' => 'width:100%',
						));
					?>
				</div>	
				<div style="display:table-cell">
					<?php
						echo $this->Form->text('first_grouping', array(
						));
					?>
				</div>	
			</div>
			<div style="display:table-row">
				<div style="display:table-cell">
					<?php 
						echo $this->Form->select('second', $sort_options, array(
							'empty' => __('None'),
							'style' => 'width:100%',
						));
					?>
				</div>	
				<div style="display:table-cell">
					<?php
						echo $this->Form->text('second_grouping', array(
						));
					?>
				</div>	
			</div>
			<div style="display:table-row">
				<div style="display:table-cell">
					<?php 
						echo $this->Form->select('third', $sort_options, array(
							'empty' => __('None'),
							'style' => 'width:100%',
						));
					?>
				</div>	
				<div style="display:table-cell">
					<?php
						echo $this->Form->text('third_grouping', array(
						));
					?>
				</div>	
			</div>
		</div>
	</fieldset>

	<?php
		echo $this->element('savecancel', array('save' => __('Assign Numbers')));
	?>
<?php
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List Registrations'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
