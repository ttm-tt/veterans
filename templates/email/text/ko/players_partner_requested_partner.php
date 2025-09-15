<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Dear Table Tennis friend!

<?php echo $partner['person']['display_name'];?> has selected you as his/her <?php echo $event_i18n;?> partner.

It is necessary that your partner must be confirmed by your side.	
You may also chose to reject the request.

If you accept you will play in the age category <?php echo $partner['participant'][$field]['description'];?>

We wish you a successful <?php echo $name;?>


