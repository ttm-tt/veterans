<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Cher(e) ami(e) pongiste, vous venez de confirmer <?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> comme votre <?php echo $event_i18n;?> partenaire de double. 
Vous allez évoluer dans la catégorie d'âge <?php echo $registration['participant'][$field]['description'];?>. 

En vous souhaitant un bonne compétition <?php echo $name;?>




