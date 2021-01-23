<?php
/* Copyright (c) 2020 Christoph Theis */
declare(strict_types=1);

namespace Shop\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;


class ArticlesFixture extends TestFixture {
	public $import = ['model' => 'Shop.Articles'];	
	
	public function init() : void {
		$this->records = [
			[
				'id' => 1,
				'name' => 'PLA',
				'description' => 'Player',
				'visible' => 0,
				'sort_order' => 1,
				'tournament_id' => 1
			],
			[
				'id' => 2,
				'name' => 'ACC',
				'description' => 'Accompanying Person',
				'visible' => 0,
				'sort_order' => 2,
				'tournament_id' => 1
			]			
		];
		parent::init();
	}
}
