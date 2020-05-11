<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'dobles mixtos' : 'dobles');
	$companero = $partner['person']['sex'] === 'M' ? 'compañero' : 'compañera';
?>
Estimado amigo de tenis de mesa,
<p>
<?php echo $partner['person']['display_name'];?> ha solicitado como <?php echo $companero;?> de <?php echo $event_i18n;?>.
<p>
Es necesario que tu <?php echo $companero;?> debe confirmala.
Pero también puedes rechazar la solicitud.
<p>
Si estás de acuerdo con la solicitud jugaréis en la categoría de edad <?php echo $partner['participant'][$field]['description'];?>
<p>
Le deseamos un <?php echo $name;?> exitoso

