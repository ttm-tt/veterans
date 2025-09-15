<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed': 'double');
?>
Dear Table Tennis friend!
<p>
<?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> has been selected as your <?php echo $event_i18n;?> partner.<br> 
</p>
<p>
You will start in the age category <?php echo $registration['participant'][$field]['description'];?>.
</p>
<p>
To complete it is necessary that your <?php echo $event_i18n;?> partner confirms you as his/her partner.
</p>
We wish you a successful <?php echo $name;?>

