<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Cher(e) ami(e) pongiste! 
<p>
<?php echo $partner['person']['display_name'];?> vient de vous proposer d'être son(a) partenaire de <?php echo $event_i18n;?>.
</p>
<p>
Vous pouvez accepter ou refuser. 
Si vous accepetez, vous serez dans la catégorie d'âge <?php echo $partner['participant'][$field]['description'];?>.
</p>
En vous souhaitant un bonne compétition <?php echo $name;?>


