<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use Cake\Core\Configure;
?>

<div class="user form">
<?php
	// $this->Flash->render('auth');
	echo '<div class="hint" id="loginhint">';
	// echo __('If you don\'t have a password yet, click on "Forgot Password" above. A password will then be sent to your email address.');
	// echo __('Login will be available 15 August');
	echo '</div>';
	echo $this->Form->create(null, array('url' => array('action' => 'login')));
	echo '<fieldset>';
		echo '<legend>' . __d('user', 'Login') . '</legend>';
		echo $this->Form->control('username', array(
			'label' => __d('user', 'Email'),
			'onBlur' => 'this.value = $.trim(this.value);'
		));
		
		$pwdUrl = $this->Html->link(__d('user', 'Forgot Password'), array('plugin' => null, 'controller' => 'Users', 'action' => 'forgot_password'));
		
		// templates is awful, but at least the "Forgot Password" link aligns with the password field
		echo $this->Form->control('password', array(
			'label' => __d('user', 'Password'),
			'required' => 'required',
			'onBlur' => 'this.value = $.trim(this.value);',
			'required' => false,
			'templates' => [
				'input' => '<div class="cell small-12 medium-9 large-6 grid-x">' .
							'<input class="cell small-12" type="{{type}}" name="{{name}}"{{attrs}}/>' .
							'<div class="cell" style="padding-top: 0;">' . $pwdUrl . '</div>'
				]
		));
		
		
		if (Configure::check('Session.authcookie')) {
			echo $this->Form->control('remember_me', array(
				'label' => __d('user', 'Remember me'),
				'type' => 'checkbox'
			));
		}
		
	echo '</fieldset>';

	echo $this->element('savecancel', array('save' => __('Login')));

	echo $this->Form->end();
?>
</div>
