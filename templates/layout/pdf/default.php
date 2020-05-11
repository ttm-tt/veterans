<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php echo $this->Html->charset();?>
		<title><?php echo $this->fetch('title');?></title>
		<?php echo $this->fetch('css');?>
	</head>
	<body>
		<?php echo $this->fetch('content'); ?>
	</body>
</html>
