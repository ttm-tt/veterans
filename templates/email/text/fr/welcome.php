<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use Cake\Routing\Router;
?>

<?php
	$name = $tournament['description'];
	$url = Router::url('/', true);
	$location = $tournament['location'];
	$organizers_email = $tournament['host']['email'];
?>Cher(e) ami(e) pongiste,

 tout d'abord, merci pour votre participation au <?php echo $name;?> à <?php echo $location;?>. Vous avez maintenant la possibilité de proposer à des joueur(se)s d'être votre partenaire pour les tableaux Doubles. 

<?php if (empty($password)) { ?>
1. Pour cela, vous devez vous connecter sur le site <?php echo $url;?> avec votre email <?php echo $email;?> et votre mot de passe. Le mot de passe vous a été envoyé préalabmement. 
<?php } else { ?>
1. Pour cela, vous devez vous connecter sur le site <?php echo $url;?> avec votre email <?php echo $email;?> et votre mot de passe <?php echo $password;?>.
<?php } ?>

2. Après vous être connecté, vous trouverez un point de l'état de votre participation individuelle et/ou un point de l'état des joueurs/participants que vous avez enregistrez Si vous avez enregistré d'autres joueurs, vous êtes le seul à pouvoir gerer leur partenaire de double.

- Pour cela, cliquez sur "Edit"

- puis sélectionner la catégorie d'âge de votre partenaire

- puis selectionner une personne et cliquez sur "Save". Clique sur "List Partner Wanted" pour avoir la liste des partenaires éventuels disponibles.

3. Une fois ces étapes effectuées, vous recevrez u mail de confirmation 

4. Votre éventuel partenauire sera informé de votre demande par email

5. Cet éventuel partenaire devra, ou refuser, sa particpation avec vous en doubles. En cas de réponse positive, les deux partenaires recevront un mail de confirmation.
Si vous ne trouvez pas de partenaires adéquat:

- regardez dans d'autres catégories d'âge

- regarder bien les noms et prénoms des personnes recherchées, ou pour un nom spécifique

- demandez à votre partenaire éventuel s'il s'est déjà enregistré

- demandez à votre partenaire éventuel s'il a déjà chois un autre partenaire.
 Vous ne pouvez choisir un éventuel partenaire qui doit être enregistré mais qui n'a pas encore choise de partenaire. Si vous avez des question ssupplémentaires, n'hésitez pas à contacter <?php echo $organizers_email;?>En vous souhaitant une bonne réussite durant les <?php echo $name;?>


