<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	echo $this->Form->control($organizer . '.name');
	echo $this->Form->control($organizer . '.description');
	echo $this->Form->control($organizer . '.address', array('type' => 'textarea'));
	echo $this->Form->control($organizer . '.email');
	echo $this->Form->control($organizer . '.phone');
	echo $this->Form->control($organizer . '.fax');
	echo $this->Form->control($organizer . '.url');
	