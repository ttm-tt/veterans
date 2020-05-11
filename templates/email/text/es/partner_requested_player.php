<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed'? 'dobles mixtos' : 'dobles');
	$companero = $registration['participant'][$field . '_partner']['person']['sex'] === 'M' ? 'otro compañero' : 'otra compañera';
?>
Estimado amigo de tenis de mesa,

Tu has seleccionado <?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> como su <?php echo $companero;?> de <?php echo $event_i18n;?>.
Jugaréis en la categoría de edad <?php echo $registration['participant'][$field]['description'];?>.

Para completar, es necesario que tu <?php echo $companero;?> le confirma como su <?php echo $companero;?> de <?php echo $event_i18n;?>.

Le deseamos un <?php echo $name;?> exitoso

