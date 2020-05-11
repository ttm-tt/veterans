<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * Languages seed.
 */
class LanguagesSeed extends AbstractSeed
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
                'id' => '1',
                'name' => 'de',
                'description' => 'Deutsch',
            ],
            [
                'id' => '2',
                'name' => 'en',
                'description' => 'English',
            ],
            [
                'id' => '3',
                'name' => 'es',
                'description' => 'Español',
            ],
            [
                'id' => '4',
                'name' => 'fr',
                'description' => 'Français',
            ],
        ];

        $table = $this->table('languages');
        $table->insert($data)->save();
    }
}
