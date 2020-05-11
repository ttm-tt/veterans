<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\View\Helper;

use Cake\View\Helper\PaginatorHelper as CakePaginatorHelper;

class PaginatorHelper extends CakePaginatorHelper {
	// Purpose is to add our sort-* parameters to the url
    public function generateUrlParams(array $options = [], ?string $model = null, array $url = []) : array {
		$ret = parent::generateUrlParams($options, $model, $url);
		
		$paging = $this->params($model);
		if (isset($paging['sort-0']))
			$ret['?']['sort-0'] = $paging['sort-0'];
		if (isset($paging['direction-0']))
			$ret['?']['direction-0'] = $paging['direction-0'];
		if (isset($paging['sort-1']))
			$ret['?']['sort-1'] = $paging['sort-1'];
		if (isset($paging['direction-1']))
			$ret['?']['direction-1'] = $paging['direction-1'];
		
		return $ret;
	}
	
}