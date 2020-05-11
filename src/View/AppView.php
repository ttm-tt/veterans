<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\View;

use Cake\View\View;

/**
 * Application View
 */
class AppView extends View
{
	// Initialize view to include TranslateWidget
    public function initialize() : void
    {		
		$this->loadHelper('Form', [
			'templates' => 'templates',
			'widgets' => [
				'translate' => [
					'App\View\Widget\TranslateWidget',
					'select',
					'text',
					'textarea'
				]
			]
		]);
    }
	
	
	// Format a date which can be a string, an array, or an object (which formats itself)
	public function formatDate($date) {
		if (is_string($date))
			return $date;

		if (is_array($date) && isset($date['year']))
			return $date['year'] . '-' . $date['month'] . '-' . $date['day'];
		
		return $date;
	}
	
}
