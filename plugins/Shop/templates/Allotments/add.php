<div class="allotments form">
<?php echo $this->Form->create($allotment);?>
	<fieldset>
 		<legend><?php echo __('Add Allotment'); ?></legend>
	<?php
		echo $this->Form->control('tournament_id', array(
			'type' => 'hidden', 
			'value' => $this->request->getSession()->read('Tournaments.id')
		));
		
		echo $this->Form->control('article_id', array(
			'empty' => __('Select Article'),
			'options' => $articles
		));
		
		echo $this->Form->control('user_id', array(
			'empty' => __('Select User'),
			'options' => $users
		));
		
		echo $this->Form->control('allotment');
	?>
	</fieldset>
	
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List Allotments'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
