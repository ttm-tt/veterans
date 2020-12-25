<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'doubles');
?>
Dear Table Tennis friend!
<p>
<?php echo $partner['person']['display_name'];?> will not play <?php echo $event_i18n;?> with you.
</p>
<p>
<?php if ($tournament['modify_before'] < date('Y-m-d')) { ?>
You are listed as "Partner wanted" again and you can choose another partner any time.
<?php } else { ?>
You are listed as "Partner wanted" again and a new partner will be drawn for you.
<?php } ?>
</p>
We wish you a successful <?php echo $name;?>

