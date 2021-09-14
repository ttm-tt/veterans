<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Caro/a amico/a del Tennistavolo,
<p>
<?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> è stato/a confermato/a come tuo/tua <?php echo $event_i18n;?> partner.
Inizierai nella categoria di <?php echo $registration['participant'][$field]['description'];?>.
</p>
Ti auguriamo un <?php echo $name;?> di successo


