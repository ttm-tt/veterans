<?php
use Cake\I18n\FrozenDate;
?>

<div class="order form">
<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo __('Cancel Pendings');?></legend>
		<?php
			echo $this->Form->control('date', array(
				'type' => 'date',
				'dateFormat' => 'YMD',
				'minYear' => date('Y', $tournament['enter_after']->year),
				'maxYear' => date('Y'),
				'value' => new FrozenDate('-28 days'),
				'required' => 'required',
				'label' => __('All before')
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
		<li><?php echo $this->Html->link(__('List Orders'), array('action' => 'index'));?></li>
	</ul>

<?php $this->end(); ?>
