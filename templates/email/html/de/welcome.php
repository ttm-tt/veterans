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
<p>
Vielen Dank für ihre Anmeldung zur <?php echo $name;?> in <?php echo $location;?>.
Ab sofort haben Sie die Möglichkeit, sich Ihren Doppelpartner/-in auszuwählen:
</p>
<p>
<ol>
<li>
<?php if (empty($password)) { ?>
Hierzu melden Sie sich auf der Webseite 
<a href="<?php echo $url;?>"><?php echo $url;?></a> mit Ihrer Emailadresse 
<?php echo $email;?> und Ihrem Passwort an. Ein Passwort wurde Ihnen bereits zugeschickt.
<?php } else { ?>
Hierzu melden Sie sich auf der Webseite 
<a href="<?php echo $url;?>"><?php echo $url;?></a> mit Ihrer Emailadresse 
<?php echo $email;?> und Ihrem Passwort <?php echo $password;?> an.
<?php } ?>
</li>

<li>Nach Anmeldung sehen Sie eine Übersicht über Ihren Anmeldestatus bzw. aller 
von Ihnen gemeldeten Spieler/-innen sowie Begleitpersonen. Sollten Sie neben 
Ihrer eigenen Anmeldung noch weitere Spieler/-innen angemeldet haben, können nur Sie 
für diese Spieler/-innen einen Doppelpartner/-in auswählen.
<ul>
<li>Klicken Sie nun auf "Bearbeiten".</li>

<li>Wählen Sie dann die Alterskategorie ihres Doppelpartners.</li>

<li>Wählen Sie nun Ihren Doppelpartner aus und klicken auf "Speichern".</li>
</ul>
Klicken Sie auf "Spieler ohne Doppelpartner", um eine Liste aller verfügbaren 
Spieler/-innen angezeigt zu bekommen.
</li>

<li>Sie bekommen anschließend eine Bestätigungsmail zugesandt.</li>

<li>
Auch Ihr Wunschpartner bekommt eine Benachrichtigung, wer ihn als 
Doppelpartner wünscht.
</li>

<li>
Um die Doppelauswahl abzuschließen ist es notwendig, dass auch der von Ihnen 
gewählte Doppelpartner/-in ihren/seinen Wunschpartner bestätigt. Wenn die Auswahl 
durch beide Spieler erfolgreich war, erhalten beide Doppelpartner eine finale 
Bestätigungsmail.
</li>
</ol>

Wenn Sie ihren Wunschpartner nicht finden:
<ul>
<li>Suchen Sie in allen Altersklassen.</li>

<li>
Suchen Sie nach anderen Schreibweisen des Vor- oder Nachnamen bzw. nach dem richtigen Vornamen.
</li>
</ul>
</p>
<p>
Sollten Sie Ihren Wunschpartner trotzdem nicht im System finden können, müssen
Sie diesen persönlich kontaktieren:
<ul>
<li>Fragen Sie Ihren Wunschpartner, ob er sich bereits angemeldet hat.</li>

<li>
Fragen Sie Ihren Wunschpartner, ob er bereits einen anderen Spieler/-in als 
Doppelpartner ausgewählt hat. Sie können nur unter bereits gemeldeten und 
freien Spieler/-innen einen Doppelpartner aussuchen.
</li>
</ul>
</p>
<p>
Falls Sie weitere Fragen haben, wenden Sie sich an 
<a href="mailto:<?php echo $organizers_email;?>"><?php echo $organizers_email;?></a>
</p>
Viel Erfolg bei den <?php echo $name;?>
