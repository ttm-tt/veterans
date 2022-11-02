<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Datasource;

use Cake\Datasource\Paging\NumericPaginator as CakePaginator;
use Cake\Datasource\ResultSetInterface;


/*
  Header: zweite Sortierung, aktuelle Sortierung, neue Sortierung
  Pager:  zweite Sortierung, aktuelle Sortierung

  sort-0, direction-0: aktuell
  sort-1, direction-1: vor aktueller 
 
  Header: 
  if (sort == sort-0) then
    sort-0 = sort-0
    direction-0 = !direction-0
    sort/direction-1 = sort/direction-1
  else
    sort-0 = sort
    direction-0 = direction
    sort/direction-1 = sort/direction-0
 */

class Paginator extends CakePaginator {
	// Merge and update the previous sort arguments,i.e. sort-* and direction-*
    public function mergeOptions($params, $defaults) : array {
		// debug($params);
		$ret = parent::mergeOptions($params, $defaults);

		// Make sure some parameters do exist
		$sort = null;
		$order = [];
		$direction = 'asc';
		
		if (isset($ret['order']))
			$order = $ret['order'];
		
		// If there is a 'sort' parameter, take that (and directions as well)
		// If there is none take the default sort order. Because there is no such
		// value available here and the default sort order is put to the end of order,
		// take the last argument from order.
		if (isset($ret['sort'])) {
			$sort = $ret['sort'];
			if (isset($ret['direction']))
				$direction = $ret['direction'];
		} else if (count($order)) {
			end($order);
			$sort = key($order);
			$direction = $order[$sort];
		}

		// sort is the new sort order
		// sort-0 should be the current sort order, to be replaced by sort eventually
		// sort-1 is the previous sort order
		// If we sort by a new field shift sort-* one down
		// If we sort by the same field keep the previous sorting
		// And add the sorting to order
		// Later, validateSort, will put the new sort on top of order, we don't have to care about it here
		if (isset($ret['sort-0'])) {
			if ($sort === $ret['sort-0']) {
				// Sort on same key as before, add 2nd sort, if it exists
				if (isset($ret['sort-1']))
					$order = [$ret['sort-1'] => $ret['direction-1']] + $order;
			} else {
				if (isset($ret['sort-0'])) {
					$order = [$ret['sort-0'] => $ret['direction-0']] + $order;
					$ret['sort-1'] = $ret['sort-0'];
					$ret['direction-1'] = $ret['direction-0'];
				}
			}
		}
		
		// Update order 
		$ret['order'] = $order;
		// And current sort is the new sort
		$ret['sort-0'] = $sort;
		$ret['direction-0'] = $direction;

		// debug($ret);
		return $ret;
	}
	
	// Purpose is to add our sort-* parameters to paging, so the helper can accessit
    public function paginate($object, array $params = [], array $settings = []) : ResultSetInterface {
		// Play it again, Sam
		// We need the full options, so we have to call mergeOptions here
		// And parent::paginate will call it, again, Sorry, but how else could we know
		// what sort-* will be*
		
        $query = null;
        if ($object instanceof QueryInterface) {
            $query = $object;
            $object = $query->getRepository();
        }

        $alias = $object->getAlias();
        $defaults = $this->getDefaults($alias, $settings);
        $options = $this->mergeOptions($params, $defaults);
		
		$ret = parent::paginate($object, $params, $settings);
		
		// And now add sort-* to paging parameters
		if (isset($options['sort-0']))
			$this->_pagingParams[$alias]['sort-0'] = $options['sort-0'];
		if (isset($options['direction-0']))
			$this->_pagingParams[$alias]['direction-0'] = $options['direction-0'];
		if (isset($options['sort-1']))
			$this->_pagingParams[$alias]['sort-1'] = $options['sort-1'];
		if (isset($options['direction-1']))
			$this->_pagingParams[$alias]['direction-1'] = $options['direction-1'];
		
		return $ret;
	}
}