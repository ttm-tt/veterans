<?php
use Cake\I18n\Date;
?>

<div class="order form">
<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __('Send Reminders');?></legend>
		<?php
			if (empty($ids)) {
				echo $this->Form->control('date', array(
					'type' => 'date',
					'dateFormat' => 'YMD',
					'minYear' => $tournament['enter_after']->year,
					'maxYear' => date('Y'),
					'value' => new Date('-14 days'),
					'required' => 'required',
					'label' => __('All before')
				));
			} else {
				echo $this->Form->hidden('date', array(
					'value' => '1970-01-01 00:00:00',
				));
			}
			
			echo $this->Form->control('until', array(
				'type' => 'date',
				'dateFormat' => 'YMD',
				'minYear' => $tournament['enter_after']->year,
				'maxYear' => date('Y'),
				'value' => new Date('+7 days'),
				'required' => 'required',
				'label' => __('Payment until')
			));
			
			echo $this->Form->control('reminder', array(
				'type' => 'checkbox',
				'label' => __('Reminder'),
				'checked' => true
			));
		?>
	</fieldset>
	<?php 
		echo $this->element('savecancel');
		echo $this->Form->end();
	?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
