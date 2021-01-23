<?php
/* Copyright (c) 2020 Christoph Theis */
declare(strict_types=1);

namespace Shop\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;


class OrderArticlesFixture extends TestFixture {
	public $import = ['model' => 'Shop.OrderArticles'];	
	
	public function init() : void {
		parent::init();
	}
}
