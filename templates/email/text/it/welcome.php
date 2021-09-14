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

per prima cosa grazie per essere entrato/a nel <?php echo $name;?> in <?php echo $location;?>.
Hai la possibilità di scegliere con effetto immediato il tuo/la tua partner per gli eventi di doppio.

<?php if (empty($password)) { ?>
1. Per procedere fai il login al seguente sito web <?php echo $url;?> con il tuo indirizzo email <?php echo $email;?> e la tua password. 
La password ti è stata già spedita. 
<?php } else { ?>
1. Per procedere effettua il login al seguente sito web <?php echo $url;?> con il tuo indirizzo email <?php echo $email;?> e la tua password <?php echo $password;?>.
<?php } ?>

2. Dopo essere entrato/a troverai un sommario del tuo stato corrente di registrazione e/o lo stato di tutti gli altri giocatori o accompagnatori che sono stati da te registrati.
Se hai registrato altri giocatori a parte te stesso/a, sei l'unica persona che può selezionare un/una partner di doppio per loro.

- Ora clicca su "Modifica"

- dopo scegli la categoria del/della tuo/tua partner di doppio

- scegli il/la tuo/tua partner di doppio e clicca su "Salva"

Clicca "Lista Senza Partner" per avere una lista di tutti i giocatori disponibili.

3. A fianco avrai una conferma della tua selezione tramite email

4. Il/la tuo/tua partner di doppio sarà informata della tua richiesta tramite email.

5. Per finalizzare la registrazione dei giocatori di doppio è necessario che il/la tuo/tua partner di doppio confermi la tua richiesta. Se la finalizzazione avrà successo, entrambi i partners avranno una conferma finale tramite email.


Se non puoi trovare nel sistema il/la tuo/tua partner di doppio:

- guarda nelle categorie di età differenti

- controlla se il nome proprio o il cognome sono scritti in altri modi, oppure cerca solo il nome proprio

- chiedi al/alla tuo/tua partner di doppio se è già stato/a registrato/a

- chiedi al/alla tuo/tua partner di doppio se ha già scelto un/una partner differente.
  Puoi selezionare come partner di doppio solo chi si è già registrato e non ha ancora scelto un/una partner.


Se dovessi avere altre domande da fare, non esitare a scrivere a <?php echo $organizers_email;?>

Ti auguriamo un <?php echo $name;?> di successo


