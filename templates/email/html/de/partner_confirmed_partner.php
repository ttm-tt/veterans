<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];	
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'Mixed' : 'Doppel');
?>
Lieber Sportsfreund, liebe Sportsfreundin!
<p>
<?php echo $partner['person']['display_name'];?> hat Sie als seinen/ihren Partner im <?php echo $event_i18n;?> bestätigt.<br>
Sie werden in der Altersklasse <?php echo $registration['participant'][$field]['description'];?> starten.
</p>
Viel Erfolg bei den <?php echo $name;?>
