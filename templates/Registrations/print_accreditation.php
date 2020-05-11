<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php

	if (file_exists(WWW_ROOT . $tournament['Tournament']['name'] . DS . 'css' . DS . 'print_accreditation.css'))
		echo $this->Html->css('/' . $tournament['Tournament']['name'] . '/' . 'css' . '/' . 'print_accreditation', null, array('block' => true));
	else
		echo $this->Html->css('print_accreditation', null, array('block' => true));

	echo $this->Html->script('jquery.js', array('block' => true));
	$this->Html->scriptStart(array('block' => true));
?>
$(document).ready(function() {
	$('div:hidden').attr('background-image', '');
});
<?php
	$this->Html->scriptEnd();

	$logourl = 
		file_exists(WWW_ROOT . $tournament['Tournament']['name'] . DS . 'img' . DS . 'logo.png') ?
		'/' . $tournament['Tournament']['name'] . '/' . 'img' . '/' . 'logo.png' :
		'/' . IMAGES_URL . 'logo.png';
	
	foreach ($data as $registration) {
		echo '<div class="page">';
			echo '<div class="image">';
				echo '<div class="logo" style="background-image:url(';
				echo $this->Url->build($logourl);
				echo ');"></div>';

				echo '<div class="photo" style="background-image: url(';
				echo $this->Url->build(
					array('controller' => 'people', 'action' => 'photo', $registration['Person']['id'])
				);
				echo ');"></div>';
			echo '</div>';

			echo '<div class="function">';
				echo '<div class="name ' . $types[$registration['Registration']['type_id']] . '">';
				echo '</div>';
				echo '<div class="code ' . $types[$registration['Registration']['type_id']] . '">';
				echo '</div>';
			echo '</div>';

			echo '<div class="person">';
				echo '<div class="name">';
					echo '<div class="first name">';
						echo $registration['Person']['first_name'];
					echo '</div>';
					echo '<div class="last name">';
						echo $registration['Person']['last_name'];
					echo '</div>';
				echo '</div>';
				echo '<div class="assoc">';
					echo $nations[$registration['Person']['nation_id']];
				echo '</div>';
			echo '</div>';

			echo '<div class="footer">';
				echo '<div class="access">';
					echo '<table class="access"><tr>';
					$codes = array(1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five');

					for ($i = 1; $i <= 5; $i++) {
						echo '<td class="access ' . $types[$registration['Registration']['type_id']] . ' ' . $codes[$i] . '">';
							echo $i;
						echo '</td>';
					}
					echo '</table>';
				echo '</div>';

				echo '<div class="sponsor">';
				echo '</div>';
			echo '</div>';
		echo '</div>';

		echo "\n";
	}
?>
