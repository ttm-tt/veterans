<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'Mixed' : 'Doppel');
?>
Lieber Sportsfreund, liebe Sportsfreundin!
<p>
<?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> wurde als Ihr Partner / Ihre Partnerin im <?php echo $event_i18n;?> bestätigt.<br>
Sie werden in der Alterskartegorie <?php echo $registration['participant'][$field]['description'];?> starten.
</p>
Viel Erfolg bei den <?php echo $name;?>
