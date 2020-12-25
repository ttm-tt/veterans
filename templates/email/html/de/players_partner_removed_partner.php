<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event_i18n = ($field === 'mixed' ? 'Mixed' : 'Doppel');
?>
Lieber Sportsfreund, liebe Sportsfreundin!
<p>
<?php echo $partner['person']['display_name'];?> wird nicht mit Ihnen im <?php echo $event_i18n;?> spielen.
</p>
<p>
<?php if ($tournament['modify_before'] < date('Y-m-d')) { ?>
Sie sind wieder als "Spieler ohne Partner" gelistet und können sich jederzeit einen neuen Partner suchen.
<?php } else { ?>
Sie sind wieder als "Spieler ohne Partner" gelistet und ein neuer Partner wird Ihnen zugelost.
<?php } ?>
</p>
Viel Erfolg bei den <?php echo $name;?>
