<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'doubles');
?>
Caro/a amico/a del Tennistavolo!
<p>
<?php echo $partner['person']['display_name'];?> non giocherà  <?php echo $event_i18n;?> con te.
</p>
<p>
Sei di nuovo nella lista "Senza Partner" e puoi scegliere un altro/a partner in qualsiasi momento.
</p>
Ti auguriamo un <?php echo $name;?> di successo


