<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Table;

use App\Model\Table\AppTable;

class ParticipantHistoriesTable extends AppTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->setTable('participant_histories');
				
		$this->belongsTo('Registrations');		
		$this->belongsTo('Users');
		$this->belongsTo('Tournaments');
	}
}
?>
