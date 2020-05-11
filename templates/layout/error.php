<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<!DOCTYPE html>
<html>
<head>
    <?php $this->Html->charset() ?>
    <title>
		<?php
			$prefix = '';
			if (!empty($tournament))
				$prefix = 
					$tournament['name'] . ' ' .
					$tournament['location'] . ' ';
			echo $prefix . __('Online Registration');
		?>
		<?php echo $this->fetch('title'); ?>
    </title>
    <?php $this->Html->meta('icon') ?>

	<?php 
	/*
    <?php $this->Html->css('base.css') ?>
    <?php $this->Html->css('cake.css') ?>
	*/ 
	?>
	<?php $this->Html->css('cake.generic.css'); ?>
	<?php $this->Html->css('onlineentries.css'); ?>
	

    <?php $this->fetch('meta') ?>
    <?php $this->fetch('css') ?>
    <?php $this->fetch('script') ?>
</head>
<body>
    <div id="container">
        <div id="header">
			<h1>An error occurred</h1>
			<?php echo $this->fetch('header'); ?>
        </div>
        <div id="content">
            <?php echo $this->Flash->render() ?>

            <?php echo $this->fetch('content') ?>
        </div>
        <div id="footer">
			<?php echo $this->fetch('footer'); ?>
        </div>
    </div>
</body>
</html>
