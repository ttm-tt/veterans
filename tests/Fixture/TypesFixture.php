<?php
/* Copyright (c) 2020 Christoph Theis */
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;


class TypesFixture extends TestFixture {
	public $import = ['model' => 'Types'];
	
	public $records = [
		[
			'id' => '1',
			'name' => 'PLA',
			'description' => 'Player',
		],
		[
			'id' => '2',
			'name' => 'COA',
			'description' => 'Coach',
		],
		[
			'id' => '3',
			'name' => 'DEL',
			'description' => 'Delegate to Congress',
		],
		[
			'id' => '4',
			'name' => 'MED',
			'description' => 'Medical Personnel',
		],
		[
			'id' => '5',
			'name' => 'ACC',
			'description' => 'Accompanying Person',
		],
		[
			'id' => '6',
			'name' => 'UMP',
			'description' => 'Umpire',
		],
		[
			'id' => '7',
			'name' => 'REF',
			'description' => 'Referee',
		],
		[
			'id' => '8',
			'name' => 'PRE',
			'description' => 'Press',
		],
		[
			'id' => '9',
			'name' => 'TV',
			'description' => 'Television',
		],
		[
			'id' => '10',
			'name' => 'SUP',
			'description' => 'Supplier',
		],
		[
			'id' => '11',
			'name' => 'OFF',
			'description' => 'Official',
		],
		[
			'id' => '12',
			'name' => 'ORG',
			'description' => 'Organizer',
		],
		[
			'id' => '13',
			'name' => 'GUE',
			'description' => 'Invited Guest',
		],
	];
}
