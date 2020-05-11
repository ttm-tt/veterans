<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Cher(e) ami(e) pongiste,

<?php echo $partner['person']['display_name'];?> vient de confirmé qu'il sera votre partenaire de double <?php echo $event_i18n;?>. Vous serez dans la catégorie d'âge  <?php echo $registration['participant'][$field]['description'];?>.

En vous souhaitant une bonne compétition <?php echo $name;?>


