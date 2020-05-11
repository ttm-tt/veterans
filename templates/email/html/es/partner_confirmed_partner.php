<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'dobles mixtos' : 'dobles');
	$companero = $partner['person']['sex'] === 'M' ? 'compañero' : 'compañera';
?>
Estimado amigo de tenis de mesa,
<p>
<?php echo $partner['person']['display_name'];?> le ha confirmado como su <?php echo $companero;?> de <?php echo $event_i18n;?>.<br>
Jugaréis en la categoría de edad <?php echo $registration['participant'][$field]['description'];?>.
</p>
Le deseamos un <?php echo $name;?> exitoso

