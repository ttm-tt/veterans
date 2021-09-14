<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'doubles');
?>
Caro/a amico/a del Tennistavolo!
<p>
<?php echo $partner['person']['display_name'];?> non giocherà <?php echo $event_i18n;?> con te.
</p>
<p>
<?php if ($tournament['modify_before'] < date('Y-m-d')) { ?>
Sei di nuovo nella lista "Senza Partner" e puoi scegliere un/una altro/altra partner in qualsiasi momento.
<?php } else { ?>
Sei di nuovo nella lista "Senza Partner" ed un/una nuovo/nuova partner verrà selezionato/a per te.
<?php } ?>
</p>
Ti auguriamo un <?php echo $name;?> di successo.


