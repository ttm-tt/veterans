<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Caro/a amico/a del Tennistavolo!

Hai selezionato <?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> come tuo/tua <?php echo $event_i18n;?> partner. 
Inizierai nella categoria di età <?php echo $registration['participant'][$field]['description'];?>.

Per completare la procedura è necessario che il/la tuo/tua partner ti confermi come suo <?php echo $event_i18n;?> partner.

Ti auguriamo un <?php echo $name;?> di successo.


