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
Dear Table Tennis friend!

<?php echo $partner['person']['display_name'];?> has selected you as his/her <?php echo $event_i18n;?> partner.

If you agree, it is necessary to confirm.
You can also reject the request.
In either case:
- Log on to <?php echo $url;?> with your email address and your password.

- Click on "Requests" next to your name.

- Click on "Accept" to accept the request or on "Reject" to reject the request.

A confirmation mail will be sent to you as well to your partner.

If you accept the request you will play in the age category <?php echo $partner['participant'][$field]['description'];?>

We wish you a successful <?php echo $name;?>


