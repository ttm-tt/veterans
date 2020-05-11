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

thank you first for entering the <?php echo $name;?> in <?php echo $location;?>.
With immediate effect you have the chance of choosing your partner for the 
double events.

<?php if (empty($password)) { ?>
1. For that purpose log in to the following web site <?php echo $url;?> with your 
email address <?php echo $email;?> and your password. 
The password has already been sent to you. 
<?php } else { ?>
1. For that purpose log in to the following web site <?php echo $url;?> with your 
email address <?php echo $email;?> and your password <?php echo $password;?>.
<?php } ?>

2. After log in you'll find a summary of your individual current entry status 
and/or the status of all other players / accompanying persons entered by you.
If you have entered any other players beside yourself you are the only person who 
could choose a double partner for them.

- Now click "Edit"

- afterwards choose the category of your double partner

- choose your double partner and click "Save"

Click "List Partner Wanted" to get a list of all available players.

3. Adjacent you'll get a confirmation of your selection by email

4. Your double partner will be informed by email about your request

5. To finalize the double entry confirmation of your request by your double partner
is required. If that finalization is successful both double partner will get
final confirmation by email.


If you cannot find your double partner in the system:

- look in different age categories

- look for different spellings of given and family name, or for the given name

- ask your double partner if he has already entered

- ask your double partner if he has already chosen a different partner.
  You can only choose a double partner who as already entered and hasn't 
  choose a partner yet.


If you have any additional questions don't hesitate to contact <?php echo $organizers_email;?>

Many success during <?php echo $name;?>


