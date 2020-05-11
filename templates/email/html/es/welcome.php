<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use Cake\Routing\Router;
?>

<?php
	$name = $tournament['description'];
	$url = Router::url('/', true);
	$location = $tournament['location'];
	$organizers_email = $tournament['host']['email'];
?>
Estimado amigo de tenis de mesa,
<p>
gracias en primera lugar por  inscribirte en los <?php echo $name;?> de <?php echo $location;?>.
Con efecto inmediato, tienes la oportunidad de elegir su compañero/a para las pruebas de dobles.
</p>
<p>
<ol>
<li>
<?php if (empty($password)) { ?>
Para ello inicia la sesión en el siguiente sitio web 
<a href="<?php echo $url;?>"><?php echo $url;?></a> con tu dirección de email 
<?php echo $email;?> y tu contraseña. La contraseña ya te ha sido enviada.
<?php } else { ?>
Para ello inicia la sesión en el siguiente sitio web  
<a href="<?php echo $url;?>"><?php echo $url;?></a> con tu dirección de email 
<?php echo $email;?> y tu contraseña <?php echo $password;?>
<?php } ?>
</li>
<li>
Después de iniciar la sesión  encontrarás un resumen de su estado de tu inscripción individual
y / o el estado de todos los otros jugadores / acompañantes que has introducido.
Si has introducido otros jugadores eras la única persona que
puede elegir a un compañero de dobles para ellos.
<ul>
<li>Ahora haz clic en "Editar"</li>

<li>luego elegir la categoría de tu compañero/a de dobles</li>

<li>una vez elegido to compañero/a y haz clic en "Guardar"</li>
</ul>

Haz clic en "Sin compañero/a" para obtener una lista de todos los jugadores disponibles.
</li>
<li>Además  recibirás una confirmación de tu selección por email</li>

<li>Tu compañero de dobles será informado por email sobre tu solicitud</li>

<li>
Para finalizar la confirmación del doble, tu compañero/a de dobles deberá también confirmarla siguiendo el mismo proceso.
Si tu compañero/a acepta recibirá la confirmación final por email.
</li>
</ol>
</p>
<p>
Si no puedes encontrar tu compañero/a de dobles en el sistema:
<ul>
<li>busca en diferentes categorías de edad</li>

<li>busca diferentes formas de escribir su nombre y apellido</li>

<li>pídele a tu compañero/a de dobles si ya se ha inscrito</li>

<li>
pídele a tu  compañero/a de dobles si ya ha elegido un compañero/a diferente
Sólo puedes elegir un compañero de dobles, que como ya se ha inscribido y no tiene
elegido un compañero/a de dobles todavía.
</li>
</ul>
</p>
<p>
Si tienes alguna pregunta adicional, no dudes en ponerte en contacto con
<a href="<?php echo $organizers_email;?>"><?php echo $organizers_email;?></a>
</p>
Le deseamos un <?php echo $name;?> exitoso
