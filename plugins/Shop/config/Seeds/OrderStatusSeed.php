<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * OrderStatus seed.
 */
class OrderStatusSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $data = [
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

        $table = $this->table('shop_order_status');
        $table->insert($data)->save();
    }
}
