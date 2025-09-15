<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Dear Table Tennis friend!
<p>
<?php echo $partner['person']['display_name'];?> has selected you as his/her <?php echo $event_i18n;?> partner.
</p>
<p>
It is necessary that your partner must be confirmed by your side.
You may also chose to reject the request.
</p>
<p>
If you accept the request you will play in the age category <?php echo $partner['participant'][$field]['description'];?>
</p>
We wish you a successful <?php echo $name;?>

