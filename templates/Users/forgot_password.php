<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="users form">
<?php
	$this->Flash->render('auth');
	echo $this->Form->create(null, array('url' => array('action' => 'forgot_password')));
	echo '<fieldset>';
		echo '<legend>' . __d('user', 'Request Password') . '</legend>';
		echo $this->Form->control('username', array(
			'div' => array('class' => 'required'),
			'label' => __d('user', 'Email'),
				'onBlur' => 'this.value = $.trim(this.value);'
		));
	echo '</fieldset>';
	
	echo $this->element('savecancel', ['save' => __d('user', 'Request Password')]);
	
	echo $this->Form->end();
?>
</div>
