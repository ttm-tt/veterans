<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Caro/a amico/a del Tennistavolo,

<?php echo $partner['person']['display_name'];?> ti ha confermato come suo/sua <?php echo $event_i18n;?> partner.
Inizierai nella categoria di <?php echo $registration['participant'][$field]['description'];?>.

Ti auguriamo un <?php echo $name;?> di successo


