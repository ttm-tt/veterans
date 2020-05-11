<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Dear Table Tennis friend,
<p>
<?php echo $partner['person']['display_name'];?> has confirmed you as his/her <?php echo $event_i18n;?> partner.<br>
You will start in the age category <?php echo $registration['participant'][$field]['description'];?>.
</p>
We wish you a successful <?php echo $name;?>
