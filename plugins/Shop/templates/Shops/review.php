<?php
	uasort($items, function($a, $b) {return $a['sort_order'] - $b['sort_order'];});
?>

<div class="order review form">
	<?php echo $this->Wizard->create(null);?>
	<?php echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Review Registration');?></h2>
	<div class="hint cell">
	<?php
		echo '<p>';
		echo __d('user', 'Please review your registration.');
		echo '</p>';
		
		echo '<p>';
		echo __d('user', 'You have to agree to the terms and conditions before you can proceed.');
		echo '</p>';
	?>
	</div>
	<?php echo $this->element('shop_order');?>
	<?php
		if (!empty($people)) {
			echo '<br>';
			echo $this->element('shop_people', array('edit' => false));
			echo '<br>';
		}
	?>
	<h3><?php echo __d('user', 'Billing Address'); ?></h3>
	<?php echo $this->element('shop_address');?>
	<br>
	<?php echo $this->Form->control('agb', array(
		'type' => 'checkbox',
		'label' => false,
		'checked' => false,
		'templateVars' => [
			'after' => __d('user', 'I agree to the {0}', $this->Html->link(__d('user', 'terms and conditions'), array('controller' => 'pages', 'action' => 'shop_agb'), array('target' => '_blank'))),
		],
		'templates' => [
			'inputContainer' => '<div class="grid-x input {{type}}{{required}}">{{content}}&nbsp;<p class="small-9 medium-6">{{after}}</p></div>',
		]
	));?>
	<?php echo $this->element('shop_footer'); ?>
	<?php echo $this->Form->end(); ?>
</div>

