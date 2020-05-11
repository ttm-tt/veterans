<?php
return [
	'EmailTransport' => [
		'default' => [
			'className' => 'Mail',
			'host' => 'localhost',
			'port' => 25,
			'timeout' => 30
		]
	],
	
    /**
     * Email delivery profiles
     *
     * Delivery profiles allow you to predefine various properties about email
     * messages from your application and give the settings a name. This saves
     * duplication across your application and makes maintenance and development
     * easier. Each profile accepts a number of keys. See `Cake\Mailer\Email`
     * for more information.
     */
    'Email' => [
        'default' => [
            'transport' => 'default',
            //'charset' => 'utf-8',
            //'headerCharset' => 'utf-8',
        ],
    ],
];
