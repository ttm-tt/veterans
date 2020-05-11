<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php

namespace App\View\Helper;

use App\View\Helper\AppHelper;

class MultipleFlashHelper extends AppHelper {

	function flash() {
		// Retrieve sages flash messages and delete them
		$messages = $this->getView()->getRequest()->getSession()->read('Flash');
		$this->getView()->getRequest()->getSession()->delete('Flash');

		// Output var
		$out = '';

		if (!empty($messages['error'])) {
			$errors = $messages['error'];
			foreach ($errors as $text)
				$out .= '<div class="flashMessage error">' . $text . '</div>' . "\n";
		}
		unset($messages['error']);

		if (!empty($messages['warning'])) {
			$warnings = $messages['warning'];
			foreach ($warnings as $text)
				$out .= '<div class="flashMessage warning">' . $text . '</div>' . "\n";
		}
		unset($messages['warning']);
	
		if (!empty($messages['success'])) {
			$success = $messages['success'];
			foreach ($success as $text)
				$out .= '<div class="flashMessage success">' . $text . '</div>' . "\n";
		}
		unset($messages['success']);

		if (!empty($messages['info'])) {
			$infos = $messages['info'];
			foreach ($infos as $text)
				$out .= '<div class="flashMessage info">' . $text . '</div>' . "\n";
		}
		unset($messages['info']);

		if (!empty($messages)) {
			foreach ($messages as $type => $message) {
				foreach ($message as $text) 
					$out .= '<div class="flashMessage ' . $type . '">' . $text . '</div>' . "\n";
			}
		}

		return $out;
	}
}

?>
