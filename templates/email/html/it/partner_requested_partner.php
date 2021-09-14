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
Caro/a amico/a del Tennistavolo!
<p>
<?php echo $partner['person']['display_name'];?> ti ha selezionato come suo <?php echo $event_i18n;?> partner.
</p>
<p>
Se sei d'accordo, bisogna che tu dia la conferma.
Potresti anche rifiutare la richiesta.
In entrambi i casi:
<ul>
	<li>Entra in <?php echo $url;?> con il tuo indirizzo email e la tua password.</li>

	<li>Clicca su "Richieste" a fianco al tuo nome.</li>

	<li>Clicca su "Accetto" per accettare la richiesta o su "Rifiuto" per rigettare la richiesta.</li>
</ul>
</p>
<p>
Una mail di conferma sarà inviata a te ed al/alla tuo/tua partner.
</p>
<p>
Se accetti la richiesta giocherai nella categoria di età <?php echo $partner['participant'][$field]['description'];?>
</p>
Ti auguriamo un <?php echo $name;?> di successo.


