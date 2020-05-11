<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use Cake\Routing\Router;
?>

<?php
	$name = $tournament['description'];
	$url = Router::url('/', true);
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Cher(e) ami(e) pongiste!
<p>
<?php echo $partner['person']['display_name'];?> vient de vous demander d'être son/sa <?php echo $event_i18n;?> partenaire.
</p>
<p>
 Si vous êtes d'accord, vous devez confirmer. 
 Vous pouvez aussi refuser cette demande. 
 Dans tous les cas:
<ul>
	<li>allez sur le site <?php echo $url;?> avec vore email et votre password.</li>
	<li>Cliquez sur "Requests" à coté de votre nom.</li>
	<li>Puis Cliquez sur "Accept" pour accepter la demande, ou sur "Reject" pour la refuser.</li>
</ul>
</p>
<p>
Un mail de confirmation vous sera envoyé, ainsi qu'au demandeur.
</p>
<p>
Si vous accepetez, vous serez dans la catégorie d'âge <?php echo $partner['participant'][$field]['description'];?>.
</p>
En vous souhaitant un bonne compétition <?php echo $name;?>


