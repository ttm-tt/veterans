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
Lieber Sportsfreund, liebe Sportsfreundin!

Vielen Dank für ihre Anmeldung zur <?php echo $name;?> in <?php echo $location;?>.
Ab sofort haben Sie die Möglichkeit, sich Ihren Doppelpartner/-in auszuwählen:

<?php if (empty($password)) { ?>
1. Hierzu melden Sie sich auf der Webseite <?php echo $url;?> mit Ihrer 
Emailadresse <?php echo $email;?> und Ihrem Passwort an. 
Ein Passwort wurde Ihnen bereits zugeschickt.
<?php } else { ?>
1. Hierzu melden Sie sich auf der Webseite <?php echo $url;?> mit Ihrer 
Emailadresse <?php echo $email;?> und Ihrem Passwort <?php echo $password;?> an.
<?php } ?>

2. Nach Anmeldung sehen Sie eine Übersicht über Ihren Anmeldestatus bzw. aller 
von Ihnen gemeldeten Spieler/-innen sowie Begleitpersonen. Sollten Sie neben 
Ihrer eigenen Anmeldung noch weitere Spieler/-innen angemeldet haben, können nur Sie 
für diese Spieler/-innen einen Doppelpartner/-in auswählen.

- Klicken Sie nun auf "Bearbeiten".

- Wählen Sie dann die Alterskategorie ihres Doppelpartners.

- Wählen Sie nun Ihren Doppelpartner aus und klicken auf "Speichern".

Klicken Sie auf "Spieler ohne Doppelpartner", um eine Liste aller verfügbaren 
Spieler/-innen angezeigt zu bekommen.

3. Sie bekommen anschließend eine Bestätigungsmail zugesandt.

4. Auch Ihr Wunschpartner bekommt eine Benachrichtigung, wer ihn als 
Doppelpartner wünscht.

5. Um die Doppelauswahl abzuschließen ist es notwendig, dass auch der von Ihnen 
gewählte Doppelpartner/-in ihren/seinen Wunschpartner bestätigt. Wenn die Auswahl 
durch beide Spieler erfolgreich war, erhalten beide Doppelpartner eine finale 
Bestätigungsmail.


Wenn Sie ihren Wunschpartner nicht finden:

- Suchen Sie in allen Altersklassen.

- Suchen Sie nach anderen Schreibweisen des Vor- oder Nachnamen bzw. nach dem 
  richtigen Vornamen.


Sollten Sie Ihren Wunschpartner trotzdem nicht im System finden können, müssen
Sie diesen persönlich kontaktieren:

- Fragen Sie Ihren Wunschpartner, ob er sich bereits angemeldet hat.

- Fragen Sie Ihren Wunschpartner, ob er bereits einen anderen Spieler/-in als 
  Doppelpartner ausgewählt hat. Sie können nur unter bereits gemeldeten und 
  freien Spieler/-innen einen Doppelpartner aussuchen.


Falls Sie weitere Fragen haben, wenden Sie sich an <?php echo $organizers_email;?>


Viel Erfolg bei den <?php echo $name;?>

