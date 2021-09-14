<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Caro/a amico/a del Tennistavolo!
<p>
<?php echo $partner['person']['display_name'];?> ti ha selezionato come suo/sua <?php echo $event_i18n;?> partner.
</p>
<p>
E' necessario che il tuo/la tua partner venga confermato/a da parte tua.	
Potresti anche rifiutare la richiesta.
</p>
<p>
Se accetti giocherai nella categoria di età <?php echo $partner['participant'][$field]['description'];?>
</p>
Ti auguriamo un <?php echo $name;?> di successo


