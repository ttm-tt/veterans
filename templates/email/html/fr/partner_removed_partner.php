<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'doubles');
?>
Cher(e) ami(e) pongiste!
<p>
<?php echo $partner['person']['display_name'];?> ne va pas jouer le <?php echo $event_i18n;?> avec vous.
</p>
<p>
<?php if ($tournament['modify_before'] < date('Y-m-d')) { ?>
Vous êtes de nouveau dans la liste des "Partner wanted" et vous pouvez choisir un autre <?php echo $event_i18n;?> partenaire.
<?php } else { ?>
Vous êtes de nouveau dans la liste des "Partner wanted" et vous recevoir un autre <?php echo $event_i18n;?> partenaire tiré.
<?php } ?>
</p>
En vous souhaitant un bonne compétition <?php echo $name;?>


