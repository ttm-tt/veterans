<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<?php echo $this->Html->charset();?>
		<?php echo $this->fetch('css');?>
		<?php echo $this->fetch('script');?>
	</head>
	<body>
		<?php echo $this->fetch('content'); ?>
	</body>
</html>
