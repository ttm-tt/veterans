<?php

	if ($email === null) {
		echo $this->Form->create(null);

		echo $this->Form->control('email', [
				'label' => __('Email'),
				'type' => 'text'
			]
		);
		
		echo $this->element('savecancel');
	}
