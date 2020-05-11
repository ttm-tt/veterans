<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use Cake\Routing\Router;
?>

<?php
	$name = $tournament['description'];
	$url = Router::url('/', true);
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'dobles mixtos' : 'dobles');
	$companero = $partner['person']['sex'] === 'M' ? 'compañero' : 'compañera';
?>
Estimado amigo de tenis de mesa,
<p>
<?php echo $partner['person']['display_name'];?> le ha solicitado como <?php echo $companero;?> de <?php echo $event_i18n;?>.
</p>
<p>	
Si estás de acuerdo, es necesario que to <?php echo $companero;?> confirmarla. 
Pero también se puede rechazar la solicitud. 
En cualquier caso:
<ul>
  <li>Inicia la sesión en <a href="<?php echo $url;?>"><?php echo $url;?></a> con tu dirección de email y tu contraseña.</li>
  <li>Haz clic en "Solicitudes" junto a su nombre.</li>
  <li>Haz clic en "Aceptar" para confirmar la solicitud o en "Rechazar" para rechazar.</li>
</ul>
</p>
<p>
Un email de confirmación será enviado a tu también a tu <?php echo $companero;?>.
</p>
<p>
Si acepta la solicitud que jugaréis en la categoría de edad <?php echo $partner['participant'][$field]['description'];?>
</p>
Le deseamos un <?php echo $name;?> exitoso

