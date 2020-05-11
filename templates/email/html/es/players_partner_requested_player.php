<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'dobles mixtos': 'dobles');
	$companero = $registration['participant'][$field . '_partner']['person']['sex'] === 'M' ? 'compañero' : 'compañera';
?>
Estimado amigo de tenis de mesa,
<p>
<?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> fue seleccionado como <?php echo $partner;?> de <?php echo $event_i18n;?>.<br> 
</p>
<p>
Jugaréis en la categoría de edad <?php echo $registration['participant'][$field]['description'];?>.
</p>
<p>
Para completar, es necesario que tu <?php echo $companero;?> confirmala.
</p>
Le deseamos un <?php echo $name;?> exitoso

