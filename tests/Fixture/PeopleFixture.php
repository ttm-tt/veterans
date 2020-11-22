<?php
/* Copyright (c) 2020 Christoph Theis */
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;


class PeopleFixture extends TestFixture {
	public $import = ['model' => 'People'];
	
	public function init() : void {
		$this->records = [
			[
				'id' => 1,
				'first_name' => 'Max',
				'last_name' => 'MUSTERMANN',
				'display_name' => 'MUSTERMANN, Max',
				'sex' => 'M',
				'dob' => '1976-02-01',
				'nation_id' => 2, // GER
				'extern_id' => '1P1',
			],
			[
				'id' => 2,
				'first_name' => 'Erika',
				'last_name' => 'MUSTERMANN',
				'display_name' => 'MUSTERMANN, Erika',
				'sex' => 'F',
				'dob' => '1964-08-12',
				'nation_id' => 2, // GER
				'extern_id' => '1A1',
			],
			[
				'id' => 3,
				'first_name' => 'Leon',
				'last_name' => 'MUSTERMANN',
				'display_name' => 'MUSTERMANN, Leon',
				'sex' => 'M',
				'dob' => strtotime('-15 years'),
				'nation_id' => 2, // GER
				'extern_id' => '1X1',
			],
		];
		
		parent::init();
	}
}
