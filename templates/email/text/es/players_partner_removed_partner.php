<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event_i18n = ($field === 'mixed' ? 'dobles mixtos' : 'dobles');
?>
Estimado amigo de tenis de mesa,
<p>
<?php echo $partner['person']['display_name'];?> no va a jugar <?php echo $event_i18n;?> con tu.
<p>
Estás en la lista "Sin compañero/a" de nuevo y puedes elegir un compañero/a cualquier momento.
<p>
Le deseamos un <?php echo $name;?> exitoso

