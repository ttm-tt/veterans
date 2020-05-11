<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Utility\Hash;

// Get the configuration engine so we can load our default config file
$engine = new PhpConfig();
// Read our providers file
$configValues = $engine->read('Shop.providers');

// Merge our default ErrorEmail config with the apps config ErrorEmail config prefering the apps version
Configure::write(
    'Shop',
    Hash::merge(
        $configValues['Shop'],
        Configure::read('Shop')
    )
);
