<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use Cake\Routing\Router;
?>

<?php
	$name = $tournament['description'];
	$url = Router::url('/', true);
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'Mixed' : 'Doppel');
?>
Lieber Sportsfreund, liebe Sportsfreundin!
<p>
Sie wurden von <?php echo $partner['person']['display_name'];?> als sein/ihr Partner/-in im <?php echo $event_i18n;?> ausgewählt.
</p>
<p>
Wenn Sie damit einverstanden sind, müssen Sie die Meldung bestätigen. 
Sie können den Wunsch aber auch ablehnen. 
In beiden Fällen:
<ul>
  <li>Melden Sie sich mit Ihrer Emailadresse und Ihrem Passwort auf <a href="<?php echo $url;?>"><?php echo $url;?></a> an</li>
  <li>Klicken Sie auf "Anfragen" neben Ihren Namen</li>
  <li>Klicken Sie auf "Akzeptieren", um den Partner Partner zu bestätigen, oder auf "Ablehnen", um den Wunsch abzulehnen</li>
</ul>
</p>
<p>
Eine Bestätigungsmail wird sowohl an Sie als auch an Ihren Partner / Ihre Partnerin geschickt.
</p>
<p>
Falls Sie den Wunsch akzeptieren werden Sie in der Altersklasse <?php echo $partner['participant'][$field]['description'];?> spielen.
</p>
Viel Erfolg bei den <?php echo $name;?>
