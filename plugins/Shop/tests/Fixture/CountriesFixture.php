<?php
/* Copyright (c) 2020 Christoph Theis */
declare(strict_types=1);

namespace Shop\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;


class CountriesFixture extends TestFixture {
	public $import = ['model' => 'Shop.Countries'];	
	
	public function init() : void {
		$this->records = [
			[
				'id' => 1,
				'name' => 'Afghanistan',
				'iso_code_2' => 'AF',
				'iso_code_3' => 'AFG',
			]
		];

		parent::init();
	}
}
