<div class="allotments form">
<?php echo $this->Form->create($allotment);?>
	<fieldset>
 		<legend><?php echo __('Edit Allotment'); ?></legend>
	<?php
		echo $this->Form->control('id', array('type' => 'hidden'));
		echo $this->Form->control('article_id', array('type' => 'hidden'));
		echo $this->Form->control('user_id', array('type' => 'hidden'));
		
		echo $this->Form->control('article.description', array(
			'readonly' => true,
			'name' => false
		));

		echo $this->Form->control('user.username', array(
			'readonly' => true,
			'name' => false
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
