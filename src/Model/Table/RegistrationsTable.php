<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Model\Table;

use App\Model\Table\AppTable;

class RegistrationsTable extends AppTable {
	public function initialize(array $config) : void {
		parent::initialize($config);
		
		$this->belongsTo('Tournaments');
		$this->belongsTo('People');
		$this->belongsTo('Types');
		
		$this->hasOne('Accommodations');
		$this->hasOne('Participants', [
			// foreignKey necessary because Participant is linked to 
			// Registrations via double_- / mixed_partner_id
			// Without in a query Reg. -> Participant -> Partner -> Participant
			// the last Participant would be linked via the xxx_partner_id and 
			// not via the registration_id
			'className' => 'Participants',
			'foreignKey'=> 'registration_id'
		]);	
	}


	// ----------------------------------------------------------------------
	public static function isDoublePartnerConfirmed($registration) {
		return 
			!empty($registration) && 
			!empty($registration['participant']) &&
			!empty($registration['participant']['double_partner']) &&
			!empty($registration['participant']['double_partner']['participant']) &&
			empty($registration['participant']['DoublePartner']['participant']['double_cancelled']) && 
			$registration['id'] == $registration['participant']['double_partner']['participant']['double_partner_id'];
	}

	public static function isMixedPartnerConfirmed($registration) {
		return 
			!empty($registration) && 
			!empty($registration['participant']) &&
			!empty($registration['participant']['mixed_partner']) &&
			!empty($registration['participant']['mixed_partner']['participant']) &&
			empty($registration['participant']['mixed_partner']['participant']['mixed_cancelled']) && 
			$registration['id'] == $registration['participant']['mixed_partner']['participant']['mixed_partner_id'];
	}
}
?>
