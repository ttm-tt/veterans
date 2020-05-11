<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Table;

use App\Model\Table\AppTable;

class OrganisationsTable extends AppTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->setTable('organisations');
				
		$this->belongsTo('Tournaments');
	}
}
?>
