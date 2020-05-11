<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="people form">
	<?php echo $this->Form->create('Registration', array('url' => array('action' => 'print_accreditation_settings'), 'type' => 'file')); ?>
	<fieldset>
 		<legend><?php echo __('Print Accreditation Settings'); ?></legend>
	<?php
		echo $this->Form->control('css', array('type' => 'textarea', 'label' => 'CSS', 'style' => 'width:80%;height:400px;font-family:monospace;'));
		echo '<p>';
    	echo $this->Form->control('logo', array('type' => 'file', 'label' => 'Logo'));
	?>
	</fieldset>
	<?php
		echo $this->element('savecancel');
    	echo $this->Form->end();
	?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<?php
			if ($Acl->check($current_user, 'Registrations/index'))
				echo '<li>' . $this->Html->link(__('List Registrations'), array('action' => 'index')) . '</li>';
		?>
		<?php 
			if ($Acl->check($current_user, 'Registrations/print_accreditation'))
				echo '<li>' . $this->Html->link(
					__('Print Accreditation'), 
					array('action' => 'print_accreditation'), 
					array('target' => '_blank')
				) . '</li>';
		?>
	</ul>
<?php $this->end(); ?>
