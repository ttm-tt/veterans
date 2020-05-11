<?php
$baseDir = dirname(dirname(__FILE__));
return [
    'plugins' => [
        'CakePdf' => $baseDir . '/vendor/friendsofcake/cakepdf/',
        'ErrorEmail' => $baseDir . '/vendor/ebrigham1/cakephp-error-email/',
        'Acl' => $baseDir . '/vendor/cakephp/acl/',
        'Shim' => $baseDir . '/vendor/dereuromark/cakephp-shim/',
        'DebugKit' => $baseDir . '/vendor/cakephp/debug_kit/',
        'Migrations' => $baseDir . '/vendor/cakephp/migrations/',
        'Cake/TwigView' => $baseDir . '/vendor/cakephp/twig-view/',
        'Bake' => $baseDir . '/vendor/cakephp/bake/'
    ]
];