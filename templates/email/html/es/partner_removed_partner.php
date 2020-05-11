<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event_i18n = ($field === 'mixed' ? 'dobles mixtos' : 'dobles');
	$companero = $partner['person']['sex'] === 'M' ? 'un otro compañero' : 'una otra compañera';
?>
Estimado amigo de tenis de mesa,
<p>
<?php echo $partner['person']['display_name'];?> no va a jugar <?php echo $event_i18n;?> con usted.
</p>
<p>
Estás en la lista "Sin compañero/a" de nuevo y puedes elegir <?php echo $companero;?> cualquier momento.
</p>
Le deseamos un <?php echo $name;?> exitoso

