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

Sie wurden von <?php echo $partner['person']['display_name'];?> als sein/ihr Partner/-in im <?php echo $event_i18n;?> ausgewählt.

Wenn Sie damit einverstanden sind, müssen Sie die Meldung bestätigen.
Sie können den Wunsch aber auch zurückweisen.
In jedem Fall:
- Melden Sie sich mit Ihrer Emailadresse und Ihrem Passwort auf <?php echo $url;?> an

- Klicken Sie auf "Anfragen" neben Ihren Namen

- Klicken Sie auf "Akzeptieren", um den Wunsch zu bestätigen, oder auf "Ablehnen", um in abzulehnen.

Eine Bestätigungsmail wird sowohl an Sie als auch an Ihren Partner / Ihre Partnerin geschickt.

Falls Sie akzeptieren werden Sie in der Altersklasse <?php echo $partner['participant'][$field]['description'];?> spielen.

Viel Erfolg bei den <?php echo $name;?>

