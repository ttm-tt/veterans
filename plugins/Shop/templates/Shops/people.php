<?php
use Cake\Routing\Router;
?>

<?php
	$this->Html->scriptStart(array('block' => true));
?>

function onAddPerson() {
	window.location = '<?php echo Router::url(array('action' => 'add_person'));?>';
}

<?php
	$this->Html->scriptEnd();
?>

<div class="order register form">
	<?php echo $this->Wizard->create(null); ?>
	<?php echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Register Players and Accompanying Persons');?></h2>
	<div class="hint cell">
	<?php
		echo '<p>';
		echo __d('user', 'Register here your players and accompanying persons.');
		echo '</p>';
		
		echo '<p>';
		echo __d('user', 'Initially, players will be put into the competitions of their age category.');
		echo '</p>';
		
		echo '<p>';
		echo __d('user', 'If your player chooses a double partner of a different age category later, both of them will be put into the correct age category automatically.');
		echo '</p>';
	?>
	</div>
	<?php echo $this->element('shop_people', array('edit' => true));?>
	<?php 
		$before = array(
			__d('user', 'Add Person'), 
			array('name' => 'add_player', 'div' => false, 'onClick' => 'onAddPerson(); return false;')
		);
		$options = array('before' => $before);
		
		$count = 0;
		foreach ($people as $p) {
			if ($p['type'] === 'PLA')
				++$count;
		}
		
		if ($count === 0 && date('Y-m-d') <= $tournament['enter_before']) {
			$options['nextScript'] = "return confirm('" . __d('user', 'You have not registered any player. Are you sure you want to continue?') . "');";
		}
		echo $this->element('shop_footer', $options); 
	?>
	<?php echo $this->Form->end(); ?>
</div>

