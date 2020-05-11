<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'Mixed' : 'Doppel');
?>
Lieber Sportsfreund, liebe Sportsfreundin!

Sie haben <?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> als Ihren Partner/ Ihre Partnerin im <?php echo $event_i18n;?> bestätigt.
Sie werden in der Altersklasse <?php echo $registration['participant'][$field]['description'];?> starten.

Viel Erfolg bei den <?php echo $name;?>

