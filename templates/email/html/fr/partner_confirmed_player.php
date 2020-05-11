<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Cher(e) ami(e) pongiste, 
<p>
vous venez de confirmer <?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> comme votre partenaire de <?php echo $event_i18n;?>. 
Vous allez évoluer dans la catégorie d'âge <?php echo $registration['participant'][$field]['description'];?>. 
</p>
En vous souhaitant un bonne compétition <?php echo $name;?>




