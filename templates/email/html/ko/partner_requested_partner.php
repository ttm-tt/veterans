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
<p>
<?php echo $partner['person']['display_name'];?> has selected you as his/her <?php echo $event_i18n;?>  partner.
</p>
<p>	
If you agree, it is necessary to confirm. 
But you can also reject the request. 
In either case:
<ul>
  <li>Log on to <a href="<?php echo $url;?>"><?php echo $url;?></a> with your email address and your password.</li>
  <li>Click on "Requests" next to your name.</li>
  <li>Click on "Accept" to confirm the request or on "Reject" to reject.</li>
</ul>
</p>
<p>
A confirmation mail will be sent to you as well to your partner.
</p>
<p>
If you accept the request you will play in the age category <?php echo $partner['participant'][$field]['description'];?>
</p>
We wish you a successful <?php echo $name;?>

