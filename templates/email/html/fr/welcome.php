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
Cher(e) ami(e) pongiste,
<p>
tout d'abord, merci pour votre participation au <?php echo $name;?> à <?php echo $location;?>.
Vous avez maintenant la possibilité de proposer à des joueur(se)s d'être votre partenaire pour les tableaux Doubles. 
</p>
<p>
<ol>
<?php if (empty($password)) { ?>
	<li>Pour cela, vous devez vous connecter sur le site <?php echo $url;?> avec votre email <?php echo $email;?> et votre mot de passe. Le mot de passe vous a été envoyé préalabmement.</li>
<?php } else { ?>
	<li>Pour cela, vous devez vous connecter sur le site <?php echo $url;?> avec votre email <?php echo $email;?> et votre mot de passe <?php echo $password;?>.</li>
<?php } ?>

	<li>Après vous être connecté, vous trouverez un point de l'état de votre participation individuelle et/ou un point de l'état des joueurs/participants que vous avez enregistrez Si vous avez enregistré d'autres joueurs, vous êtes le seul à pouvoir gerer leur partenaire de double.</li>

	<li>
		<ul>
			<li>Pour cela, cliquez sur "Edit"</li>

			<li>puis sélectionner la catégorie d'âge de votre partenaire</li>

			<li>puis selectionner une personne et cliquez sur "Save". Clique sur "List Partner Wanted" pour avoir la liste des partenaires éventuels disponibles.</li>
		</ul>
	</li>
	<li>Une fois ces étapes effectuées, vous recevrez u mail de confirmation</li>

	<li>Votre éventuel partenauire sera informé de votre demande par email</li>

	<li>Cet éventuel partenaire devra, ou refuser, sa particpation avec vous en doubles. En cas de réponse positive, les deux partenaires recevront un mail de confirmation.</li>
</ol>
</p>
<p>
Si vous ne trouvez pas de partenaires adéquat:
<ul>
	<li>regardez dans d'autres catégories d'âge</li>

	<li>regarder bien les noms et prénoms des personnes recherchées, ou pour un nom spécifique</li>

	<li>demandez à votre partenaire éventuel s'il s'est déjà enregistré</li>

	<li>demandez à votre partenaire éventuel s'il a déjà chois un autre partenaire.</li>
</ul>
</p>
<p>
Vous ne pouvez choisir un éventuel partenaire qui doit être enregistré mais qui n'a pas encore choise de partenaire. Si vous avez des question ssupplémentaires, n'hésitez pas à contacter <?php echo $organizers_email;?>
</p>
En vous souhaitant une bonne réussite durant les <?php echo $name;?>


