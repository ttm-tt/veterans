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
Dear Table Tennis friends,
<p>
thank you first for entering the <?php echo $name;?> in <?php echo $location;?>.
With immediate effect you have the chance of choosing your partner for the 
double events.
</p>
<p>
<ol>
<li>
<?php if (empty($password)) { ?>
For that purpose log in to the following web site 
<a href="<?php echo $url;?>"><?php echo $url;?></a> with your email address 
<?php echo $email;?> and your password. The password has already been sent to you.
<?php } else { ?>
For that purpose log in to the following web site 
<a href="<?php echo $url;?>"><?php echo $url;?></a> with your email address 
<?php echo $email;?> and your password <?php echo $password;?>
<?php } ?>
</li>
<li>
After log in you'll find a summary of your individual current entry status 
and / or the status of all other players / accompanying persons entered by you.
If you have entered any other players beside yourself you are the only person who 
could choose a double partner for them.
<ul>
<li>Now click "Edit"</li>

<li>afterwards choose the category of your double partner</li>

<li>choose your double partner and click "Save"</li>
</ul>

Click "List Partner Wanted" to get a list of all available players.
</li>
<li>Adjacent you'll get a confirmation of your selection by email</li>

<li>Your double partner will be informed by email about your request</li>

<li>
To finalize the double entry confirmation of your request by your double partner
is required. If that finalization is successful both double partner will get
final confirmation by email.
</li>
</ol>
</p>
<p>
If you cannot find your double partner in the system:
<ul>
<li>look in different age categories</li>

<li>look for different spellings of given and family name, or for the given name</li>

<li>ask your double partner if he has already entered</li>

<li>
ask your double partner if he has already chosen a different partner.
You can only choose a double partner who has already entered and has not 
choose a partner yet.
</li>
</ul>
</p>
<p>
If you have any additional questions don't hesitate to contact 
<a href="<?php echo $organizers_email;?>"><?php echo $organizers_email;?></a>
</p>
Many success during <?php echo $name;?>
