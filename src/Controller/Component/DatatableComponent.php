<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Controller\ComponentRegistry;

class DatatableComponent extends Component {
	
	public function getResponse($request, $model) {
		$paginate = $this->getController()->paginate;
		if (!isset($paginate['conditions']))
			$paginate['conditions'] = array(1 => 1);
		
		$fields = $paginate['fields'];

		// Total unfiltered 
		$recordsTotal = $model->find('all', $paginate)->count();
		
		// Calculate conditions
		$conditions = $this->getConditions($request);
		
		$paginate['conditions'] = $conditions;
		
		// Total filtered
		$recordsFiltered = $model->find('all', $paginate)->count();
		
		// Calculate order
		$order = $this->getOrder($request);
		$paginate['order'] = $order;
		
		// Set range
		if (isset($request['start']))
			$paginate['offset'] = $request['start'];
		if (isset($request['length']))
			$paginate['limit'] = $request['length'];

		$data = $model->find('all', $paginate);
		
		if ($data === false)
			$data = array();
		
		$response = array(
			'draw' => (isset($request['draw']) ? intval($request['draw']) : 1),
			'recordsTotal' => $recordsTotal,
			'recordsFiltered' => $recordsFiltered,
			'data' => array(),
			// 'query' => $model->buildQuery('all', $paginate)
		);
		
		if (false && Configure::read('debug'))
			$response['query'] = $model->buildQuery('all', $paginate);
		
		foreach ($data as $d) {
			$newData = array();
			foreach ($fields as $col) {
				$tmp = explode('.', $col);
				$newData[] = $d[$tmp[0]][$tmp[1]];
			}
			
			$response['data'][] = $newData;
		}
		
		return $response;
	}
	
	
	private function getConditions($request) {
		$paginate = $this->getController()->paginate;
		
		$fields = $paginate['fields'];
		$conditions = isset($paginate['conditions']) ? $paginate['conditions'] : array();
		$or = array();
		
		foreach ($fields as $i => $col) {
			if ( !empty($request['search']['value']) && !empty($request['columns'][$i]['searchable']) )
				$or[] = array($col . ' LIKE' => '%' . $request['search']['value'] . '%');
			
			if (!empty($request['columns'][$i]['search']['value']))
				$conditions[] = array($col . ' LIKE' => '%' . $request['columns'][$i]['search']['value'] . '%');
		}
	
		if (count($or) > 0)
			$conditions[] = array('OR' => $or);
		
		return $conditions;
	}
	
	
	private function getOrder($request) {
		$paginate = $this->getcontroller()->paginate;
		
		$fields = $paginate['fields'];

		$order = array();

		for ($i = 0; $i < count($fields) && isset($request['order'][$i]['column']); ++$i) {
			$order[] = $fields[$request['order'][$i]['column']] . ' ' . $request['order'][$i]['dir'];
		}
		
		if (empty($order)) {
			foreach ($fields as $i => $col) {
				if (isset($request['columns'][$i]['orderable']))
					$order[] = $col . ' ASC';
			}
		} else {
			// Always implictely order by name
			$order[] = $fields[0] . ' ASC';
		}
		
		return $order;
	}
	
}
