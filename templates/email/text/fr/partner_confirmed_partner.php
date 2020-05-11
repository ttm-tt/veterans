<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Cher(e) ami(e) pongiste,

<?php echo $partner['person']['display_name'];?> vient de vous confirmer comme son/sa <?php echo $event_i18n;?> partenaire de double.
 Vous allez évoluer dans la catégorie d'âge <?php echo $registration['participant'][$field]['description'];?>.

En vous souhaitant un bonne compétition <?php echo $name;?>


