<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class MoveAbleBodiedToParticipants extends AbstractMigration {
	public function up() {
		$this->table('participants')
				->addColumn('ptt_nonpara', 'boolean', [
					'after' => 'registration_id',
					'default' => 0,
					'null' => false
				])
				->update()
		;
		
		$this->execute(
				'UPDATE participants SET ptt_nonpara = ' .
				'(SELECT p.ptt_nonpara FROM people p INNER JOIN registrations r ON p.id = r.person_id ' .
				'  WHERE participants.registration_id = r.id)'
		);
		
		$this->table('people')
				->removeColumn('ptt_nonpara')
				->update()
		;				
	}
	
	public function down() {
		$this->table('people')
				->addColumn('ptt_nonpara', 'boolean', [
					'after' => 'ptt_wchc',
					'default' => 0,
					'null' => false
				])
				->update()
		;
		
		$this->execute(
				'UPDATE people  SET ptt_nonpara = ' .
				'IFNULL((SELECT p.ptt_nonpara FROM participants p INNER JOIN registrations r ON p.registration_id = r.id ' .
				'  WHERE r.person_id = people.id), 0)'
		);
		
		$this->table('participants')
				->removeColumn('ptt_nonpara')
				->update()
		;	
	}
}
