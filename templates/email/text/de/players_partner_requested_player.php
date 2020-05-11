<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'Mixed' : 'Doppel');
?>
Please scroll down for the english version.

Lieber Sportsfreund, liebe Sportsfreundin!

<?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> wurde als Ihr Partner/-in im <?php echo $event_i18n;?> ausgewählt.

Sie werden in der Altersklasse <?php echo $registration['participant'][$field]['description'];?> spielen.

Um die Meldung abzuschließen ist es nötig, dass Ihr Partner/-in Sie als seinen/ihren Wunschpartner bestätigt.

Viel Erfolg bei den <?php echo $name;?>

