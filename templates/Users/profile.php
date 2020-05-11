<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="users form">
<?php
	echo '<div class="hint" id="loginhint">';
	// echo __('If you need to change your email address below, please contact <a href="mailto:ettu@pt.lu">ETTU</a>.');
	echo '</div>';
	echo $this->Form->create($user);?>
	<fieldset>
		<legend><?php echo __d('user', 'Edit Profile'); ?></legend>
	<?php
		echo $this->Form->control('id', array('type' => 'hidden'));
		echo $this->Form->control('username', array('readonly' => 'readonly', 'required' => false));
		echo $this->Form->control('email', array('readonly' => 'readonly'));
		echo $this->Form->control('language_id', array(
			'label' => __d('user', 'Pref. language'),
			'options' => $languages,
			'empty' => false
		));
		// echo $this->Form->control('add_email', array('label' => __('Add. Email (CC:)')));
	?>
	</fieldset>
<?php
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

