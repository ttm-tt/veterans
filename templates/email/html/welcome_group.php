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

Dear table tennis friend,
<p>
we have created an account for you to register your group at the <?php echo $name;?>.
<p>
<ol>
<li>
<?php if (empty($password)) { ?>
To login go to <?php echo $url;?> and login with your email address <?php echo $email;?>.
To get the password click on "Forgot Password" and enter your email address there. 
A password will be sent to you.
<?php } else { ?>
To login go to <?php echo $url;?> and login with your email address <?php echo $email;?> 
and your password <?php echo $password;?>
<?php } ?>
</li>
<li>	
After login you will see all people have have registered so far. 
To add a new person click on "New Registration" and fill out the details there.
You can also add additional items the person has bought.

To request a partner for doubles both players must be registered. 
<ul>
	<li>Now click "Edit" next to the player</li>

	<li>afterwards choose the category of your double partner</li>

	<li>choose your double partner and click "Save"</li>
</ul>

Click "List Partner Wanted" to get a list of all available players.
</li>
<li>
Adjacent you'll get a confirmation of your selection by email
</li>
<li>
Your double partner will be informed by email about your request
</li>
<li>
To finalize the double entry confirmation of your request by your double partner
is required. If that finalization is successful both double partner will get
final confirmation by email.
</li>
</ol>
<p>
Many success during <?php echo $name;?>
