<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Table;

use App\Model\Table\AppTable;

class NationsTable extends AppTable {
	
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->hasMany('People');
		$this->hasMany('Users');
		$this->hasMany('Tournaments');
	}
}
?>
