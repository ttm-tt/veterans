<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;
use Cake\Routing\Router;
?>

<div id="tournament-top-bar" class="top-bar grid-x">
	<div id="logo" class="cell small-2 medium-1"
		<?php 
			if (Configure::check('App.url')) {
				echo ' onClick="window.open(\'' . Configure::read('App.url') . '\', \'_blank\');"';
				echo ' style="cursor:pointer;"';
			}
		?>
	></div>
	<div id="tournament" class="cell small-8 medium-10 title">
		<h3>
			<?php
				if (!empty($tournament)) {
					echo '<span class="show-for-large">' . $tournament['description'] . '</span>';
					echo '<span class="hide-for-large">' . $tournament['name'] . '</span>';
				}
				else
					echo '&nbsp;';
			?>
		</h3>
	</div>
	<div class="small-2 medium-1"></div>
</div>
<div id="auth-top-bar" class="top-bar grid-x">
	<div id="auth" class="cell auto">
		<?php 
			if (empty($current_user)) {
				if ($this->request->getParam('action') !== 'login') {
					echo '<span class="show-for-medium">' . __d('user', 'Not logged in') . '&nbsp;' . '</span>';
					echo '<span>' . $this->Html->link(__d('user', 'Login', true), array('plugin' => null, 'controller' => 'Users', 'action' => 'login')) . '</span>';
				}
				if ($this->request->getParam('controller') !== 'shops' && 
						!in_array($this->request->getParam('action'), ['wizard', 'add_person'])) {
					echo '<div>' . $this->Html->link(__d('user', 'Register'), Router::url('/register', true)) . '</div>';
				}
			} else {
				echo '<span class="show-for-medium">' . __d('user', 'Logged in as ') . '&nbsp;' . '</span>';
				echo '<span>' . $this->Html->link($current_user['username'], array('plugin' => null, 'controller' => 'Users', 'action' => 'profile', $current_user['id'])) . '&nbsp;' . '</span>';
				echo '<span>' . $this->Html->link(__d('user', 'Logout', true), array('plugin' => null, 'controller' => 'Users', 'action' => 'logout', 'plugin' => null, 'admin' => false)) . '</span>';
			}
		?>
	</div>
	<div id="lang" class="cell shrink">
		<?php
			echo $this->Form->control('language_id', array(
				'label' => false,
				'options' => $languages,
				'value' => $language_id,
				'onchange' => 'onChangeLanguage($(this).val()); return false;',
				// We are not in a form so we need the full width available
				'templates' => [
					'select' => '<select class="cell" name="{{name}}"{{attrs}}>{{content}}</select>',

				]
			));
		?>
	</div>
	<?php
		// echo '<div id="help">';
		// echo $this->Html->link(__d('user', 'Help', true), 'http://downloads.ttm.co.at/onlineentries/help.pdf', array('target' => '_blank'));
		// echo '</div>';
	?>
</div>

<?php if (!empty($controllerMenu)) { ?>
<div id="menu-top-bar" class="top-bar navmenu" data-responsive-toggle="navmenu" data-hide-for="large">
	<button class="menu-icon" type="button" data-toggle="navmenu">&nbsp;</button>
</div>

<div id="navmenu" class="navmenu">
	<?php
		echo '<ul class="menu vertical large-horizontal" data-responsive-menu="accordion large">';
		foreach ($controllerMenu as $menu) {
			$title = '';
			$url = array();
			$allowedKeys = array_flip(['plugin', 'controller', 'action']);

			if (is_array($menu)) {
				$title = __d('user', $menu['title']);
				$url = array_merge(array('action' => 'index', 'plugin' => null, 'admin' => false), $menu);
			} else {
				$title = __d('user', Inflector::humanize($menu));
				$url = array('controller' => $menu, 'action' => 'index', 'plugin' => null, 'admin' => false);
			}

			$url['controller'] = ucwords($url['controller']);

			if ($this->request->getParam('controller') == $url['controller'])
				echo '<li class="is-active"><a>' . $title . '</a></li>';
			else if (!empty($url['controllers']) && in_array($this->request->getParam('controller'), $url['controllers']))
				echo '<li class="is-active"><a>' . $title . '</a></li>';
			else
				echo '<li>' . $this->Html->link($title, array_intersect_key($url, $allowedKeys)) . '</li>';
		}

		echo '</ul>';
	?>
</div>
<?php } ?>
<div id="flash" class="flash">
	<?php echo $this->element('flash'); ?>
</div>
