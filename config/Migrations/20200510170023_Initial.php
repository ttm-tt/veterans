<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Initial extends AbstractMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     * @return void
     */
    public function up()
    {
        $this->table('acos')
            ->addColumn('parent_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('model', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('foreign_key', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('alias', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('lft', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('rght', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addIndex(
                [
                    'lft',
                    'rght',
                ]
            )
            ->addIndex(
                [
                    'alias',
                ]
            )
            ->addIndex(
                [
                    'model',
                ]
            )
            ->create();

        $this->table('aros')
            ->addColumn('parent_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('model', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('foreign_key', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('alias', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('lft', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('rght', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addIndex(
                [
                    'lft',
                    'rght',
                ]
            )
            ->addIndex(
                [
                    'alias',
                ]
            )
            ->addIndex(
                [
                    'model',
                ]
            )
            ->create();

        $this->table('aros_acos')
            ->addColumn('aro_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('aco_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('_create', 'string', [
                'default' => '0',
                'limit' => 2,
                'null' => false,
            ])
            ->addColumn('_read', 'string', [
                'default' => '0',
                'limit' => 2,
                'null' => false,
            ])
            ->addColumn('_update', 'string', [
                'default' => '0',
                'limit' => 2,
                'null' => false,
            ])
            ->addColumn('_delete', 'string', [
                'default' => '0',
                'limit' => 2,
                'null' => false,
            ])
            ->addIndex(
                [
                    'aro_id',
                    'aco_id',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'aro_id',
                ]
            )
            ->addIndex(
                [
                    'aco_id',
                ]
            )
            ->create();

        $this->table('competitions')
            ->addColumn('tournament_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('sex', 'string', [
                'default' => null,
                'limit' => 1,
                'null' => false,
            ])
            ->addColumn('type_of', 'string', [
                'default' => null,
                'limit' => 1,
                'null' => false,
            ])
            ->addColumn('born', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('entries', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('entries_host', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'tournament_id',
                ]
            )
            ->addIndex(
                [
                    'tournament_id',
                ]
            )
            ->create();

        $this->table('groups')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('parent_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('type_ids', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('i18n')
            ->addColumn('locale', 'string', [
                'default' => null,
                'limit' => 6,
                'null' => false,
            ])
            ->addColumn('model', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('foreign_key', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('field', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('content', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'locale',
                    'model',
                    'foreign_key',
                    'field',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'model',
                    'foreign_key',
                    'field',
                ]
            )
            ->create();

        $this->table('languages')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->create();

        $this->table('nations')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('continent', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('notifications')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('all_notifications', 'tinyinteger', [
                'default' => '1',
                'limit' => 4,
                'null' => false,
            ])
            ->addColumn('new_player', 'tinyinteger', [
                'default' => '1',
                'limit' => 4,
                'null' => false,
            ])
            ->addColumn('delete_registration_player_after', 'tinyinteger', [
                'default' => '1',
                'limit' => 4,
                'null' => false,
            ])
            ->addColumn('edit_registration_player_after', 'tinyinteger', [
                'default' => '1',
                'limit' => 4,
                'null' => false,
            ])
            ->addIndex(
                [
                    'user_id',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->create();

        $this->table('organisations')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('address', 'string', [
                'default' => null,
                'limit' => 256,
                'null' => false,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('phone', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('fax', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('url', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->create();

        $this->table('participant_histories')
            ->addColumn('tournament_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('registration_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('field_name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('old_value', 'string', [
                'default' => null,
                'limit' => 4096,
                'null' => true,
            ])
            ->addColumn('new_value', 'string', [
                'default' => null,
                'limit' => 4096,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'tournament_id',
                ]
            )
            ->addIndex(
                [
                    'registration_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->create();

        $this->table('participants')
            ->addColumn('registration_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('single_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('single_cancelled', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('double_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('double_partner_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('double_cancelled', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('mixed_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('mixed_partner_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('mixed_cancelled', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('team_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('team_cancelled', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('cancelled', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('replaced_by_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('start_no', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'registration_id',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'registration_id',
                ]
            )
            ->addIndex(
                [
                    'mixed_id',
                ]
            )
            ->addIndex(
                [
                    'team_id',
                ]
            )
            ->addIndex(
                [
                    'double_partner_id',
                ]
            )
            ->addIndex(
                [
                    'replaced_by_id',
                ]
            )
            ->addIndex(
                [
                    'mixed_partner_id',
                ]
            )
            ->addIndex(
                [
                    'single_id',
                ]
            )
            ->addIndex(
                [
                    'double_id',
                ]
            )
            ->create();

        $this->table('people')
            ->addColumn('first_name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('last_name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('display_name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('extern_id', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('phone', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('sex', 'string', [
                'default' => null,
                'limit' => 1,
                'null' => false,
            ])
            ->addColumn('dob', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('nation_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'display_name',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'nation_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->create();

        $this->table('registrations')
            ->addColumn('tournament_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('person_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('type_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('cancelled', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('comment', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'tournament_id',
                ]
            )
            ->addIndex(
                [
                    'type_id',
                ]
            )
            ->addIndex(
                [
                    'person_id',
                ]
            )
            ->create();

        $this->table('settings')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('category', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('setting', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('value', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'setting',
                ],
                ['unique' => true]
            )
            ->create();

        $this->table('shop_allotments')
            ->addColumn('article_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('allotment', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'article_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->create();

        $this->table('shop_article_variants')
            ->addColumn('article_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('variant_type', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('sort_order', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('price', 'decimal', [
                'default' => '0.00',
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'article_id',
                ]
            )
            ->create();

        $this->table('shop_articles')
            ->addColumn('tournament_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('price', 'decimal', [
                'default' => '0.00',
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('tax', 'decimal', [
                'default' => '0.00',
                'null' => false,
                'precision' => 5,
                'scale' => 2,
            ])
            ->addColumn('cancellation_fee', 'decimal', [
                'default' => '0.00',
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('visible', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('available', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('available_from', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('available_until', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('waitinglist_limit_enabled', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('waitinglist_limit_max', 'integer', [
                'default' => '0',
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('sort_order', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('article_description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('article_image', 'binary', [
                'default' => null,
                'limit' => 16777215,
                'null' => true,
            ])
            ->addColumn('article_url', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'tournament_id',
                ]
            )
            ->create();

        $this->table('shop_cancellation_fees')
            ->addColumn('shop_settings_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('fee', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('start', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'shop_settings_id',
                ]
            )
            ->create();

        $this->table('shop_countries')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('iso_code_2', 'string', [
                'default' => null,
                'limit' => 2,
                'null' => false,
            ])
            ->addColumn('iso_code_3', 'string', [
                'default' => null,
                'limit' => 3,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('shop_order_addresses')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('type', 'string', [
                'default' => null,
                'limit' => 1,
                'null' => false,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => false,
            ])
            ->addColumn('first_name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('last_name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('street', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('zip_code', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('city', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('country_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('phone', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'country_id',
                ]
            )
            ->addIndex(
                [
                    'order_id',
                ]
            )
            ->create();

        $this->table('shop_order_article_histories')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('order_article_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('field_name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('old_value', 'string', [
                'default' => null,
                'limit' => 4096,
                'null' => true,
            ])
            ->addColumn('new_value', 'string', [
                'default' => null,
                'limit' => 4096,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->addIndex(
                [
                    'order_article_id',
                ]
            )
            ->create();

        $this->table('shop_order_articles')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('article_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('article_variant_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('detail', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('person_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('quantity', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('price', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('total', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('cancelled', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('cancellation_fee', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'order_id',
                ]
            )
            ->addIndex(
                [
                    'article_id',
                ]
            )
            ->addIndex(
                [
                    'article_variant_id',
                ]
            )
            ->addIndex(
                [
                    'person_id',
                ]
            )
            ->create();

        $this->table('shop_order_authorizenet')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('x_response_code', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('x_response_reason_code', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('x_response_reason_text', 'string', [
                'default' => null,
                'limit' => 256,
                'null' => true,
            ])
            ->addColumn('x_auth_code', 'string', [
                'default' => null,
                'limit' => 6,
                'null' => true,
            ])
            ->addColumn('x_avs_code', 'string', [
                'default' => null,
                'limit' => 1,
                'null' => true,
            ])
            ->addColumn('x_trans_id', 'biginteger', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('x_invoice_num', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('x_description', 'string', [
                'default' => null,
                'limit' => 256,
                'null' => true,
            ])
            ->addColumn('x_amount', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('x_method', 'string', [
                'default' => null,
                'limit' => 6,
                'null' => true,
            ])
            ->addColumn('x_type', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('x_account_number', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('x_card_type', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('x_split_tender_id', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('x_cust_id', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('x_first_name', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('x_last_name', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('x_company', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('x_address', 'string', [
                'default' => null,
                'limit' => 60,
                'null' => true,
            ])
            ->addColumn('x_city', 'string', [
                'default' => null,
                'limit' => 40,
                'null' => true,
            ])
            ->addColumn('x_state', 'string', [
                'default' => null,
                'limit' => 40,
                'null' => true,
            ])
            ->addColumn('x_zip', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('x_country', 'string', [
                'default' => null,
                'limit' => 60,
                'null' => true,
            ])
            ->addColumn('xMD5_Hash', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('x_cvv2_resp_code', 'string', [
                'default' => null,
                'limit' => 1,
                'null' => true,
            ])
            ->addColumn('x_cavv_response', 'string', [
                'default' => null,
                'limit' => 1,
                'null' => true,
            ])
            ->addColumn('x_test_request', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'order_id',
                ]
            )
            ->create();

        $this->table('shop_order_bpayment')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('orderhash', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('authorizationcode', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('creditcardnumber', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('step', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('errordescription', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('errorcode', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('errordetail1', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('auditlog1', 'string', [
                'default' => null,
                'limit' => 256,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'order_id',
                ]
            )
            ->create();

        $this->table('shop_order_card_pointe')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('paymentType', 'string', [
                'default' => null,
                'limit' => 2,
                'null' => true,
            ])
            ->addColumn('type', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('ipAddress', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('total', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('invoice', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('number', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => true,
            ])
            ->addColumn('expirationDateMonth', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('experationDateYear', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('billCompany', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('billFName', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('billLName', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('billAddress1', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('billAddress2', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('billCity', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('billState', 'string', [
                'default' => null,
                'limit' => 2,
                'null' => true,
            ])
            ->addColumn('billCountry', 'string', [
                'default' => null,
                'limit' => 2,
                'null' => true,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('phone', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('cardType', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('gatewayTransactionId', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('merchantId', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('cf_orderid', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'order_id',
                ]
            )
            ->create();

        $this->table('shop_order_comments')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('comment', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'order_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->create();

        $this->table('shop_order_dibs')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('acquirer', 'string', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('agreement', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('amount', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('approvalcode', 'string', [
                'default' => null,
                'limit' => 6,
                'null' => true,
            ])
            ->addColumn('authkey', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('cardcountry', 'string', [
                'default' => null,
                'limit' => 2,
                'null' => true,
            ])
            ->addColumn('currency', 'string', [
                'default' => null,
                'limit' => 3,
                'null' => true,
            ])
            ->addColumn('dibsmd5', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('fee', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('ip', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('lang', 'string', [
                'default' => null,
                'limit' => 2,
                'null' => true,
            ])
            ->addColumn('newDIBSTransactionID', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('newDIBSTransactionIDVerification', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('orderid', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('ordertext', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('paytype', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('statuscode', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('severity', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('suspect', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('test', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('threeDstatus', 'integer', [
                'default' => '0',
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('token', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('transact', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'order_id',
                ]
            )
            ->create();

        $this->table('shop_order_histories')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('field_name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('old_value', 'string', [
                'default' => null,
                'limit' => 4096,
                'null' => true,
            ])
            ->addColumn('new_value', 'string', [
                'default' => null,
                'limit' => 4096,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'order_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->create();

        $this->table('shop_order_ipayment')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('addr_name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('addr_street', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('addr_zip', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('addr_city', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('addr_country', 'string', [
                'default' => null,
                'limit' => 2,
                'null' => false,
            ])
            ->addColumn('trxuser_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('trx_amount', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('trx_currency', 'string', [
                'default' => null,
                'limit' => 3,
                'null' => false,
            ])
            ->addColumn('trx_typ', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('trx_paymenttyp', 'string', [
                'default' => null,
                'limit' => 4,
                'null' => false,
            ])
            ->addColumn('ret_transdate', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('ret_transtime', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('ret_errorcode', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('ret_fatalerror', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('ret_errormsg', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('ret_additionalmsg', 'string', [
                'default' => null,
                'limit' => 256,
                'null' => true,
            ])
            ->addColumn('ret_authcode', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('ret_ip', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('ret_booknr', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('ret_trx_number', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('redirect_needed', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('trx_paymentmethod', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('trx_paymentdata_country', 'string', [
                'default' => null,
                'limit' => 2,
                'null' => false,
            ])
            ->addColumn('trx_remoteip_country', 'string', [
                'default' => null,
                'limit' => 2,
                'null' => false,
            ])
            ->addColumn('trx_issuer_avs_response', 'string', [
                'default' => null,
                'limit' => 1,
                'null' => true,
            ])
            ->addColumn('trx_payauth_status', 'string', [
                'default' => null,
                'limit' => 1,
                'null' => true,
            ])
            ->addColumn('paydata_cc_cardowner', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('paydata_cc_number', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('paydata_cc_expdata', 'string', [
                'default' => null,
                'limit' => 4,
                'null' => true,
            ])
            ->addColumn('paydata_cc_typ', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('ret_status', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'order_id',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'order_id',
                ]
            )
            ->create();

        $this->table('shop_order_payment_details')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('payment', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('value', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'current_timestamp()',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'order_id',
                ]
            )
            ->create();

        $this->table('shop_order_paypal')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('payer_id', 'string', [
                'default' => null,
                'limit' => 13,
                'null' => false,
            ])
            ->addColumn('payer_email', 'string', [
                'default' => null,
                'limit' => 127,
                'null' => false,
            ])
            ->addColumn('first_name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('last_name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('residence_country', 'string', [
                'default' => null,
                'limit' => 2,
                'null' => false,
            ])
            ->addColumn('payer_status', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('receiver_id', 'string', [
                'default' => null,
                'limit' => 13,
                'null' => true,
            ])
            ->addColumn('receiver_email', 'string', [
                'default' => null,
                'limit' => 127,
                'null' => false,
            ])
            ->addColumn('invoice', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('reason_code', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('payment_type', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('payment_status', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('payment_date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('payment_gross', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('payment_fee', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('mc_currency', 'string', [
                'default' => null,
                'limit' => 3,
                'null' => false,
            ])
            ->addColumn('mc_gross', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('mc_fee', 'decimal', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('tax', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('shipping', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('handling_amount', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('parent_txn_id', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('txn_id', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('txn_type', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('item_name', 'string', [
                'default' => null,
                'limit' => 127,
                'null' => false,
            ])
            ->addColumn('item_number', 'string', [
                'default' => null,
                'limit' => 127,
                'null' => false,
            ])
            ->addColumn('quantity', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('test_ipn', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('transaction_subject', 'string', [
                'default' => null,
                'limit' => 127,
                'null' => false,
            ])
            ->addColumn('custom', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('protection_eligibility', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('notify_version', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => false,
            ])
            ->addColumn('charset', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('ipn_track_id', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'order_id',
                ]
            )
            ->create();

        $this->table('shop_order_redsys')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('Ds_Date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('Ds_Hour', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('Ds_Amount', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('Ds_Card_Country', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('Ds_Currency', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('Ds_Order', 'string', [
                'default' => null,
                'limit' => 12,
                'null' => true,
            ])
            ->addColumn('Ds_Terminal', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('Ds_Signature', 'string', [
                'default' => null,
                'limit' => 40,
                'null' => true,
            ])
            ->addColumn('Ds_Response', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('Ds_MechantData', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => true,
            ])
            ->addColumn('Ds_SecurePayment', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('Ds_TransactionType', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('Ds_AuthorisationCode', 'string', [
                'default' => null,
                'limit' => 6,
                'null' => true,
            ])
            ->addColumn('Ds_ConsumerLang', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('Ds_Card_Type', 'string', [
                'default' => null,
                'limit' => 1,
                'null' => true,
            ])
            ->addColumn('Ds_ErrorCode', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'order_id',
                ]
            )
            ->create();

        $this->table('shop_order_status')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->create();

        $this->table('shop_orders')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('tournament_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('order_status_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('total', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('discount', 'decimal', [
                'default' => '0.00',
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('cancellation_fee', 'decimal', [
                'default' => '0.00',
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('cancellation_discount', 'decimal', [
                'default' => '0.00',
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('paid', 'decimal', [
                'default' => '0.00',
                'null' => false,
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('invoice', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('invoice_no', 'integer', [
                'default' => '0',
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('invoice_paid', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('invoice_split', 'integer', [
                'default' => '0',
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('invoice_cancelled', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('accepted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('payment_method', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('ticket', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('language', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'order_status_id',
                ]
            )
            ->addIndex(
                [
                    'tournament_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->create();

        $this->table('shop_settings')
            ->addColumn('tournament_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('open_from', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('open_until', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('cancellation_date_50', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('cancellation_date_100', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('invoice_no_prefix', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('invoice_no_postfix', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('currency', 'string', [
                'default' => null,
                'limit' => 3,
                'null' => false,
            ])
            ->addColumn('tax', 'float', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('street', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('city', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('country', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('vat', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('correspondent_bank', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('bank_name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('bank_address', 'string', [
                'default' => null,
                'limit' => 256,
                'null' => true,
            ])
            ->addColumn('account_holder', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('account_no', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('iban', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('bic', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('swift', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('aba', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('phone', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('fax', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('add_footer', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('header', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('footer', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('creditcard', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('banktransfer', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'tournament_id',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'tournament_id',
                ]
            )
            ->create();

        $this->table('tournaments')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('start_on', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('end_on', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('enter_after', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('enter_before', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('accreditation_start', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modify_before', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('nation_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('location', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('organizer_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('committee_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('host_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('contractor_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('dpa_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'organizer_id',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'host_id',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'nation_id',
                ]
            )
            ->addIndex(
                [
                    'organizer_id',
                ]
            )
            ->addIndex(
                [
                    'host_id',
                ]
            )
            ->addIndex(
                [
                    'committee_id',
                ]
            )
            ->addIndex(
                [
                    'contractor_id',
                ]
            )
            ->addIndex(
                [
                    'dpa_id',
                ]
            )
            ->create();

        $this->table('types')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->create();

        $this->table('users')
            ->addColumn('username', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('login_token', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('enabled', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('add_email', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('group_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('nation_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('tournament_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('language_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('prefix_people', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('last_login', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('count_successful', 'integer', [
                'default' => '0',
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('count_failed', 'integer', [
                'default' => '0',
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('count_failed_since', 'integer', [
                'default' => '0',
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('count_requests', 'integer', [
                'default' => '0',
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('ticket', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('ticket_expires', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'group_id',
                ]
            )
            ->addIndex(
                [
                    'nation_id',
                ]
            )
            ->addIndex(
                [
                    'language_id',
                ]
            )
            ->addIndex(
                [
                    'tournament_id',
                ]
            )
            ->create();

        $this->table('aros_acos')
            ->addForeignKey(
                'aro_id',
                'aros',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->addForeignKey(
                'aco_id',
                'acos',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('competitions')
            ->addForeignKey(
                'tournament_id',
                'tournaments',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->addForeignKey(
                'tournament_id',
                'tournaments',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->update();

        $this->table('notifications')
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('participant_histories')
            ->addForeignKey(
                'tournament_id',
                'tournaments',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->addForeignKey(
                'registration_id',
                'registrations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->update();

        $this->table('participants')
            ->addForeignKey(
                'registration_id',
                'registrations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->addForeignKey(
                'mixed_id',
                'competitions',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->addForeignKey(
                'team_id',
                'competitions',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->addForeignKey(
                'double_partner_id',
                'registrations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->addForeignKey(
                'replaced_by_id',
                'registrations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->addForeignKey(
                'mixed_partner_id',
                'registrations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->addForeignKey(
                'single_id',
                'competitions',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->addForeignKey(
                'double_id',
                'competitions',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->update();

        $this->table('people')
            ->addForeignKey(
                'nation_id',
                'nations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->update();

        $this->table('registrations')
            ->addForeignKey(
                'tournament_id',
                'tournaments',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->addForeignKey(
                'type_id',
                'types',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->addForeignKey(
                'person_id',
                'people',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('shop_allotments')
            ->addForeignKey(
                'article_id',
                'shop_articles',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('shop_article_variants')
            ->addForeignKey(
                'article_id',
                'shop_articles',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('shop_articles')
            ->addForeignKey(
                'tournament_id',
                'tournaments',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('shop_cancellation_fees')
            ->addForeignKey(
                'shop_settings_id',
                'shop_settings',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('shop_order_addresses')
            ->addForeignKey(
                'country_id',
                'shop_countries',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->addForeignKey(
                'order_id',
                'shop_orders',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('shop_order_article_histories')
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->addForeignKey(
                'order_article_id',
                'shop_order_articles',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('shop_order_articles')
            ->addForeignKey(
                'order_id',
                'shop_orders',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->addForeignKey(
                'article_id',
                'shop_articles',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->addForeignKey(
                'article_variant_id',
                'shop_article_variants',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->addForeignKey(
                'person_id',
                'people',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->update();

        $this->table('shop_order_authorizenet')
            ->addForeignKey(
                'order_id',
                'shop_orders',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('shop_order_bpayment')
            ->addForeignKey(
                'order_id',
                'shop_orders',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('shop_order_card_pointe')
            ->addForeignKey(
                'order_id',
                'shop_orders',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->update();

        $this->table('shop_order_comments')
            ->addForeignKey(
                'order_id',
                'shop_orders',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->update();

        $this->table('shop_order_dibs')
            ->addForeignKey(
                'order_id',
                'shop_orders',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('shop_order_histories')
            ->addForeignKey(
                'order_id',
                'shop_orders',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->update();

        $this->table('shop_order_ipayment')
            ->addForeignKey(
                'order_id',
                'shop_orders',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('shop_order_payment_details')
            ->addForeignKey(
                'order_id',
                'shop_orders',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('shop_order_paypal')
            ->addForeignKey(
                'order_id',
                'shop_orders',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('shop_orders')
            ->addForeignKey(
                'order_status_id',
                'shop_order_status',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->addForeignKey(
                'tournament_id',
                'tournaments',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->update();

        $this->table('shop_settings')
            ->addForeignKey(
                'tournament_id',
                'tournaments',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('tournaments')
            ->addForeignKey(
                'nation_id',
                'nations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'NO_ACTION',
                ]
            )
            ->addForeignKey(
                'organizer_id',
                'organisations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->addForeignKey(
                'host_id',
                'organisations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->addForeignKey(
                'committee_id',
                'organisations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->addForeignKey(
                'contractor_id',
                'organisations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->addForeignKey(
                'dpa_id',
                'organisations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'SET_NULL',
                ]
            )
            ->update();

        $this->table('users')
            ->addForeignKey(
                'group_id',
                'groups',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->addForeignKey(
                'nation_id',
                'nations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->addForeignKey(
                'language_id',
                'languages',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->addForeignKey(
                'tournament_id',
                'tournaments',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT',
                ]
            )
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down()
    {
        $this->table('aros_acos')
            ->dropForeignKey(
                'aro_id'
            )
            ->dropForeignKey(
                'aco_id'
            )->save();

        $this->table('competitions')
            ->dropForeignKey(
                'tournament_id'
            )
            ->dropForeignKey(
                'tournament_id'
            )->save();

        $this->table('notifications')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('participant_histories')
            ->dropForeignKey(
                'tournament_id'
            )
            ->dropForeignKey(
                'registration_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('participants')
            ->dropForeignKey(
                'registration_id'
            )
            ->dropForeignKey(
                'mixed_id'
            )
            ->dropForeignKey(
                'team_id'
            )
            ->dropForeignKey(
                'double_partner_id'
            )
            ->dropForeignKey(
                'replaced_by_id'
            )
            ->dropForeignKey(
                'mixed_partner_id'
            )
            ->dropForeignKey(
                'single_id'
            )
            ->dropForeignKey(
                'double_id'
            )->save();

        $this->table('people')
            ->dropForeignKey(
                'nation_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('registrations')
            ->dropForeignKey(
                'tournament_id'
            )
            ->dropForeignKey(
                'type_id'
            )
            ->dropForeignKey(
                'person_id'
            )->save();

        $this->table('shop_allotments')
            ->dropForeignKey(
                'article_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('shop_article_variants')
            ->dropForeignKey(
                'article_id'
            )->save();

        $this->table('shop_articles')
            ->dropForeignKey(
                'tournament_id'
            )->save();

        $this->table('shop_cancellation_fees')
            ->dropForeignKey(
                'shop_settings_id'
            )->save();

        $this->table('shop_order_addresses')
            ->dropForeignKey(
                'country_id'
            )
            ->dropForeignKey(
                'order_id'
            )->save();

        $this->table('shop_order_article_histories')
            ->dropForeignKey(
                'user_id'
            )
            ->dropForeignKey(
                'order_article_id'
            )->save();

        $this->table('shop_order_articles')
            ->dropForeignKey(
                'order_id'
            )
            ->dropForeignKey(
                'article_id'
            )
            ->dropForeignKey(
                'article_variant_id'
            )
            ->dropForeignKey(
                'person_id'
            )->save();

        $this->table('shop_order_authorizenet')
            ->dropForeignKey(
                'order_id'
            )->save();

        $this->table('shop_order_bpayment')
            ->dropForeignKey(
                'order_id'
            )->save();

        $this->table('shop_order_card_pointe')
            ->dropForeignKey(
                'order_id'
            )->save();

        $this->table('shop_order_comments')
            ->dropForeignKey(
                'order_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('shop_order_dibs')
            ->dropForeignKey(
                'order_id'
            )->save();

        $this->table('shop_order_histories')
            ->dropForeignKey(
                'order_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('shop_order_ipayment')
            ->dropForeignKey(
                'order_id'
            )->save();

        $this->table('shop_order_payment_details')
            ->dropForeignKey(
                'order_id'
            )->save();

        $this->table('shop_order_paypal')
            ->dropForeignKey(
                'order_id'
            )->save();

        $this->table('shop_orders')
            ->dropForeignKey(
                'order_status_id'
            )
            ->dropForeignKey(
                'tournament_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('shop_settings')
            ->dropForeignKey(
                'tournament_id'
            )->save();

        $this->table('tournaments')
            ->dropForeignKey(
                'nation_id'
            )
            ->dropForeignKey(
                'organizer_id'
            )
            ->dropForeignKey(
                'host_id'
            )
            ->dropForeignKey(
                'committee_id'
            )
            ->dropForeignKey(
                'contractor_id'
            )
            ->dropForeignKey(
                'dpa_id'
            )->save();

        $this->table('users')
            ->dropForeignKey(
                'group_id'
            )
            ->dropForeignKey(
                'nation_id'
            )
            ->dropForeignKey(
                'language_id'
            )
            ->dropForeignKey(
                'tournament_id'
            )->save();

        $this->table('acos')->drop()->save();
        $this->table('aros')->drop()->save();
        $this->table('aros_acos')->drop()->save();
        $this->table('competitions')->drop()->save();
        $this->table('groups')->drop()->save();
        $this->table('i18n')->drop()->save();
        $this->table('languages')->drop()->save();
        $this->table('nations')->drop()->save();
        $this->table('notifications')->drop()->save();
        $this->table('organisations')->drop()->save();
        $this->table('participant_histories')->drop()->save();
        $this->table('participants')->drop()->save();
        $this->table('people')->drop()->save();
        $this->table('registrations')->drop()->save();
        $this->table('settings')->drop()->save();
        $this->table('shop_allotments')->drop()->save();
        $this->table('shop_article_variants')->drop()->save();
        $this->table('shop_articles')->drop()->save();
        $this->table('shop_cancellation_fees')->drop()->save();
        $this->table('shop_countries')->drop()->save();
        $this->table('shop_order_addresses')->drop()->save();
        $this->table('shop_order_article_histories')->drop()->save();
        $this->table('shop_order_articles')->drop()->save();
        $this->table('shop_order_authorizenet')->drop()->save();
        $this->table('shop_order_bpayment')->drop()->save();
        $this->table('shop_order_card_pointe')->drop()->save();
        $this->table('shop_order_comments')->drop()->save();
        $this->table('shop_order_dibs')->drop()->save();
        $this->table('shop_order_histories')->drop()->save();
        $this->table('shop_order_ipayment')->drop()->save();
        $this->table('shop_order_payment_details')->drop()->save();
        $this->table('shop_order_paypal')->drop()->save();
        $this->table('shop_order_redsys')->drop()->save();
        $this->table('shop_order_status')->drop()->save();
        $this->table('shop_orders')->drop()->save();
        $this->table('shop_settings')->drop()->save();
        $this->table('tournaments')->drop()->save();
        $this->table('types')->drop()->save();
        $this->table('users')->drop()->save();
    }
}
