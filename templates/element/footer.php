<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<div class="navmenu cell">
	<div id="impressum" class="cell small-12 medium-11">
		<ul class="vertical medium-horizontal menu">
			<li>
				<?php
					echo $this->Html->link(
						__d('user', 'Privacy Policy', true), 
						array('plugin' => null, 'controller' => 'pages', 'action' => 'privacy'), 
						array('class' => 'impressum', 'target' => '_blank')
					);
				?>
			</li>
			<li>
				<?php
					echo $this->Html->link(
						__d('user', 'Impressum', true), 
						array('plugin' => null, 'controller' => 'pages', 'action' => 'impressum'), 
						array('class' => 'impressum', 'target' => '_blank')
					);
				?>
			</li>
			<li>
				<?php
					echo $this->Html->link(
						__d('user', 'Terms & Conditions', true), 
						array('plugin' => 'shop', 'controller' => 'pages', 'action' => 'shop_agb'), 
						array('class' => 'impressum', 'target' => '_blank')
					);
				?>
			</li>
		</ul>
	</div>
</div>
