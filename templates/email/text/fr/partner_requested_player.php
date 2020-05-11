<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
Cher(e) ami(e) pongiste!

 Vous avez demandé à <?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> d'être votre partenaire de double <?php echo $event_i18n;?>. Vous serez dans la catégorie d'âge <?php echo $registration['participant'][$field]['description'];?>. Votre demande doit être confirmé par votre partenaire <?php echo $event_i18n;?> . En vous souhaitant une bonne compétition <?php echo $name;?>


