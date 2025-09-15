<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Dear Table Tennis friend,

you have confirmed <?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> as your <?php echo $event_i18n;?> partner.
You will start in the age category <?php echo $registration['participant'][$field]['description'];?>.

We wish you a successful <?php echo $name;?>


