<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

class MultipleFlashComponent extends Component {

	function setFlash($message, $type = "message") {
		if (is_array($message)) {
			foreach ($message as $m)
				$this->setFlash($m, $type);
			
			return;
		}
		
		// If flash messages are already stored in session, fetch array
		// Else, create a new empty array
		if ($this->getController()->getRequest()->getSession()->check('Flash'))
			$messages = $this->getController()->getRequest()->getSession()->read('Flash');
		else
			$messages = array();

		$messages[$type][] = $message;

		// Write back
		$this->getController()->getRequest()->getSession()->write('Flash', $messages);
	}
}
