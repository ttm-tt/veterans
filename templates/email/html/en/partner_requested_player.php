<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed'? 'mixed' : 'double');
?>
Dear Table Tennis friend!
<p>
You have selected <?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> as your <?php echo $event_i18n;?> partner.<br> 
You will start in the age category <?php echo $registration['participant'][$field]['description'];?>.
</p>
<p>
To complete it is necessary that your partner confirms you as his/her <?php echo $event_i18n;?> partner.
</p>
We wish you a successful <?php echo $name;?>

