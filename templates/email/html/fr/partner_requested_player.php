<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Cher(e) ami(e) pongiste!
<p>
Vous avez demandé à <?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> d'être votre partenaire de <?php echo $event_i18n;?>.
Vous serez dans la catégorie d'âge <?php echo $registration['participant'][$field]['description'];?>.
</p>
<p>
Votre demande doit être confirmé par votre partenaire <?php echo $event_i18n;?>.
</p>
En vous souhaitant une bonne compétition <?php echo $name;?>


