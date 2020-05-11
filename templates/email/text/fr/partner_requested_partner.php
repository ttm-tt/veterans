<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use Cake\Routing\Router;
?>

<?php
	$name = $tournament['description'];
	$url = Router::url('/', true);
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>Cher(e) ami(e) pongiste!

<?php echo $partner['person']['display_name'];?> vient de vous demander d'être son/sa <?php echo $event_i18n;?> partenaire.

 Si vous êtes d'accord, vous devez confirmer. Vous pouvez aussi refuser cette demande. Dans tous les cas:
- allez sur le site <?php echo $url;?> avec vore email et votre password.

- Cliquez sur "Requests" à coté de votre nom.

 Puis Cliquez sur "Accept" pour accepter la demande, ou sur "Reject" pour la refuser. Un mail de confirmation 

A confirmation mail vous sera envoyé, ainsi qu'au demandeur. Si vous accepetez, vous serez dans la catégorie d'âge <?php echo $partner['participant'][$field]['description'];?> E
n vous souhaitant un bonne compétition <?php echo $name;?>


