<?php
/* Copyright (c) 2020 Christoph Theis */
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;


class NationsFixture extends TestFixture {
	public $import = ['model' => 'Nations'];
	
	public $records = [
		[
			'id' => 1,
			'name' => 'AUT',
			'description' => 'Austria',
			'update_people' => 1
		],
		[
			'id' => 2,
			'name' => 'GER',
			'description' => 'Germany',
			'update_people' => 1
		],
		[
			'id' => 3,
			'name' => 'CHN',
			'description' => 'China',
			'update_people' => 0
		],
	];
}
