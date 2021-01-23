<?php
/* Copyright (c) 2020 Christoph Theis */
declare(strict_types=1);

namespace Shop\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;


class OrderStatusFixture extends TestFixture {
	public $import = ['model' => 'Shop.OrderStatus'];	
	
	public function init() : void {
        $this->records = [
            [
                'id' => '5',
                'name' => 'PEND',
                'description' => 'Pending',
            ],
            [
                'id' => '6',
                'name' => 'PAID',
                'description' => 'Paid',
            ],
            [
                'id' => '7',
                'name' => 'ERR',
                'description' => 'Error',
            ],
            [
                'id' => '8',
                'name' => 'CANC',
                'description' => 'Cancelled',
            ],
            [
                'id' => '9',
                'name' => 'FRD',
                'description' => 'Fraud',
            ],
            [
                'id' => '10',
                'name' => 'INVO',
                'description' => 'Invoice',
            ],
            [
                'id' => '11',
                'name' => 'INIT',
                'description' => 'Initiated',
            ],
            [
                'id' => '12',
                'name' => 'WAIT',
                'description' => 'Waiting List',
            ],
            [
                'id' => '13',
                'name' => 'DEL',
                'description' => 'Payment Delayed',
            ],
        ];
		parent::init();
	}
}
