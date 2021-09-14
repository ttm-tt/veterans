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
Caro/a amico/a del Tennistavolo,
<P>
per prima cosa grazie per essere entrato/a nel <?php echo $name;?> in <?php echo $location;?>.
Hai la possibilità di scegliere con effetto immediato il tuo/la tua partner per gli eventi di doppio.
</p>
<p>
<ol>
<li>
<?php if (empty($password)) { ?>
Per procedere fai il login al seguente sito web <?php echo $url;?> con il tuo indirizzo email <?php echo $email;?> e la tua password. 
La password ti è stata già spedita. 
<?php } else { ?>
Per procedere effettua il login al seguente sito web <?php echo $url;?> con il tuo indirizzo email <?php echo $email;?> e la tua password <?php echo $password;?>.
<?php } ?>
</li>
<li>
Dopo essere entrato/a troverai un sommario del tuo stato corrente di registrazione e/o lo stato di tutti gli altri giocatori o accompagnatori che sono stati da te registrati.
Se hai registrato altri giocatori a parte te stesso/a, sei l'unica persona che può selezionare un/una partner di doppio per loro.
<ul>
	<li>Ora clicca su "Modifica"</li>
	
	<li>dopo scegli la categoria del/della tuo/tua partner di doppio</li>	

	<li>scegli il/la tuo/tua partner di doppio e clicca su "Salva"</li>
</ul>

Clicca "Lista Senza Partner" per avere una lista di tutti i giocatori disponibili.
</li>

<li>A fianco avrai una conferma della tua selezione tramite email</li>

<li>Il/la tuo/tua partner di doppio sarà informata della tua richiesta tramite email.</li>

<li>Per finalizzare la registrazione dei giocatori di doppio è necessario che il/la tuo/tua partner di doppio confermi la tua richiesta. Se la finalizzazione avrà successo, entrambi i partners avranno una conferma finale tramite email.</li>

</ol>
</p>
<p>
Se non puoi trovare nel sistema il/la tuo/tua partner di doppio:
<ul>
	<li>guarda nelle categorie di età differenti</li>

	<li>controlla se il nome proprio o il cognome sono scritti in altri modi, oppure cerca solo il nome proprio</li>

	<li>chiedi al/alla tuo/tua partner di doppio se è già stato/a registrato/a</li>

	<li>
		chiedi al/alla tuo/tua partner di doppio se ha già scelto un/una partner differente.
		Puoi selezionare come partner di doppio solo chi si è già registrato e non ha ancora scelto un/una partner.
	</li>
</ul>
</p>
<p>
Se dovessi avere altre domande da fare, non esitare a scrivere a <?php echo $organizers_email;?>
</p>

Ti auguriamo un <?php echo $name;?> di successo
