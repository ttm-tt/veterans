<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="tournaments form">
<?php echo $this->Form->create($t);?>
	<fieldset class="has-tabs">
		<legend><?php echo __('Edit Tournament'); ?></legend>
		<ul class="tabs" data-tabs id="edit-tournament">
			<li class="tabs-title is-active">
				<a href="#tab-tournament" aria-selected="true"><?= __('Tournament'); ?></a>
			</li>
			<li class="tabs-title">
				<a href="#tab-organizer" data-tabs-target="tab-organizer"><?= __('Organizer'); ?></a>
			</li>
			<li class="tabs-title">
				<a href="#tab-comittee" data-tabs-target="tab-comittee"><?= __('Comittee'); ?></a>
			</li>
			<li class="tabs-title">
				<a href="#tab-host" data-tabs-target="tab-host"><?= __('Host'); ?></a>
			</li>
			<li class="tabs-title">
				<a href="#tab-contractor" data-tabs-target="tab-contractor"><?= __('Contractor'); ?></a>
			</li>
			<li class="tabs-title">
				<a href="#tab-dpa" data-tabs-target="tab-dpa"><?= __('Nat. DPA'); ?></a>
			</li>
		</ul>
	
		<div class="tabs-content" data-tabs-content="edit-tournament">
			<div class="tabs-panel is-active" id="tab-tournament">
				<?php
					echo $this->Form->control('id', array('type' => 'hidden'));
					echo $this->Form->control('organizer_id', array('type' => 'hidden'));
					echo $this->Form->control('committee_id', array('type'=> 'hidden'));
					echo $this->Form->control('host_id', array('type'=> 'hidden'));
					echo $this->Form->control('contractor_id', array('type'=> 'hidden'));
					echo $this->Form->control('dpa_id', array('type'=> 'hidden'));
					echo $this->Form->control('name');
					echo $this->Form->control('description');
					echo $this->Form->control('start_on', array(
						'type' => 'date',
						'empty' => [
							'year' => __('Year'), 
							'month' => __('Month'), 
							'day' => __('Day')
						],
					));
					echo $this->Form->control('end_on', array(
						'type' => 'date',
						'empty' => [
							'year' => __('Year'), 
							'month' => __('Month'), 
							'day' => __('Day')
						],
					));
					echo $this->Form->control('enter_after', array(
						'type' => 'date',
						'empty' => [
							'year' => __('Year'), 
							'month' => __('Month'), 
							'day' => __('Day')
						],
					));
					echo $this->Form->control('enter_before', array(
						'type' => 'date',
						'empty' => [
							'year' => __('Year'), 
							'month' => __('Month'), 
							'day' => __('Day')
						],
					));
					echo $this->Form->control('modify_before', array(
						'type' => 'date',
						'empty' => [
							'year' => __('Year'), 
							'month' => __('Month'), 
							'day' => __('Day')
						],
					));
					echo $this->Form->control('accreditation_start', array(
						'label' => __('Start Accreditation'), 
						'type' => 'date',
						'empty' => [
							'year' => __('Year'), 
							'month' => __('Month'), 
							'day' => __('Day')
						],
					));
					echo $this->Form->control('nation_id', array(
						'options' => $nations, 
						'empty' => __('Select Host Association'),
						'label' => __('Host Association')
					));
					echo $this->Form->control('location');
				?>
			</div>
			<div class="tabs-panel" id="tab-organizer">
				<?php		
					echo $this->element('organizer', ['organizer' => 'organizer']);
				?>
			</div>

			<div class="tabs-panel" id="tab-comittee">
				<?php		
					echo $this->element('organizer', ['organizer' => 'committee']);
				?>
			</div>

			<div class="tabs-panel" id="tab-host">
				<?php		
					echo $this->element('organizer', ['organizer' => 'host']);
				?>
			</div>

			<div class="tabs-panel" id="tab-contractor">
				<?php		
					echo $this->element('organizer', ['organizer' => 'contractor']);
				?>
			</div>

			<div class="tabs-panel" id="tab-dpa">
				<?php		
					echo $this->element('organizer', ['organizer' => 'dpa']);
				?>
			</div>
		</div>
	</fieldset>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List Tournaments'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
