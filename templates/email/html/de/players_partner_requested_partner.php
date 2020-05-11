<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'Mixed' : 'Doppel');
?>
Lieber Sportsfreund, liebe Sportsfreundin!
<p>
Sie wurden von <?php echo $partner['person']['display_name'];?> als sein/ihr Partner/-in im <?php echo $event_i18n;?> ausgewählt.
</p>
<p>
Es ist notwendig, dass Ihr Partner / Ihre Partnerin von Ihrer Seite bestätigt wird.	
Sie können den Wunsch aber auch ablehnen lassen.
</p>
<p>
Falls Sie den Wunsch akzeptieren werden Sie in der Altersklasse <?php echo $partner['participant'][$field]['description'];?> spielen.
</p>
Viel Erfolg bei den <?php echo $name;?>
