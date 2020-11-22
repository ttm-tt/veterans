<?php
/* Copyright (c) 2020 Christoph Theis */
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;


class CompetitionsFixture extends TestFixture {
	public $import = ['model' => 'Competitions'];
	
	public function init() : void {
		$this->records = [
			[
				'id' => 1,
				'name' => 'MS40',
				'description' => 'Men\'s Singles over 40',
				'sex' => 'M',
				'born' => date('Y', strtotime('-40 years')),
				'type_of' => 'S',
				'tournament_id' => 1
			]
		];

		parent::init();
	}
}
