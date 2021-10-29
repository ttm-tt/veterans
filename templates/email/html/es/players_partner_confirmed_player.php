<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'dobles mixtos' : 'dobles');
	$companero = $registration['participant'][$field . '_partner']['person']['sex'] === 'M' ? 'compañero' : 'compañera';
?>
Estimado amigo de tenis de mesa,
<p>
<?php echo $registration['participant'][$event . '_partner']['person']['display_name'];?> ha sido confirmó como tu <?php echo $companero;?> de <?php echo $event_i18n;?>.<br>
Comenzarás en la categoría de edad  <?php echo $registration['participant'][$field]['description'];?>.
</p>
Le deseamos un <?php echo $name;?> exitoso

