<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'doubles');
?>
Cher(e) ami(e) pongiste!

<?php echo $partner['person']['display_name'];?> ne sera pas votre partenaire de double <?php echo $event_i18n;?>.

Vous êtes de nouveau dans la liste "Partner wanted" et vous pouvez choisir un autre partenaire. En vous souhaitant une bonne compétition <?php echo $name;?>


