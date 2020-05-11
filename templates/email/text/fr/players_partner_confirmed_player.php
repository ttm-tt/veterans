<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>Cher(e) ami(e) pongiste,

<?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> sera bien votre partenaire de double <?php echo $event_i18n;?> partner.
Vous serez dans la catégorie d'âge <?php echo $registration['participant'][$field]['description'];?>. En vous souhaitant une bonne compétition <?php echo $name;?>


