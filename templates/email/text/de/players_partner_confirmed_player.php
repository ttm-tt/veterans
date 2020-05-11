<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'Mixed' : 'Doppel');
?>
Lieber Sportsfreund, liebe Sportsfreundin!

<?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> wurde als Ihr Partner/-in im <?php echo $event_i18n;?> bestätigt.
Sie werden in der Altersklasse <?php echo $registration['participant'][$field]['description'];?> starten.

Viel Erfolg bei den <?php echo $name;?>

