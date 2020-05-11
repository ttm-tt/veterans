<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\View\Helper;

use Cake\View\Helper\FormHelper as CakeFormHelper;

class FormHelper extends CakeFormHelper {
	
	// I do my own wrapping of dateTime.
	// I need the id, but including {{attrs}} in the outer <span> adds "type=date", which makes
	// the span to an input field. So unset what I don't need
    protected function _datetimeOptions($options) {
		$ret = parent::_dateTimeOptions($options);
		
		unset($ret['type']);
		return $ret;
	}

}
