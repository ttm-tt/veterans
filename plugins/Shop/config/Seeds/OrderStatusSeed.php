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
                'name' => 'PEND',
                'description' => 'Pending',
            ],
            [
                'name' => 'PAID',
                'description' => 'Paid',
            ],
            [
                'name' => 'ERR',
                'description' => 'Error',
            ],
            [
                'name' => 'CANC',
                'description' => 'Cancelled',
            ],
            [
                'name' => 'FRD',
                'description' => 'Fraud',
            ],
            [
                'name' => 'INVO',
                'description' => 'Invoice',
            ],
            [
                'name' => 'INIT',
                'description' => 'Initiated',
            ],
            [
                'name' => 'WAIT',
                'description' => 'Waiting List',
            ],
            [
                'name' => 'DEL',
                'description' => 'Payment Delayed',
            ],
        ];

        $table = $this->table('shop_order_status');
        $table->insert($data)->save();
    }
}
