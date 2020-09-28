<?php
$baseDir = dirname(dirname(__FILE__));
return [
    'plugins' => [
        'ErrorEmail' => $baseDir . '/vendor/ebrigham1/cakephp-error-email/',
        'Shim' => $baseDir . '/vendor/dereuromark/cakephp-shim/',
        'Cake/TwigView' => $baseDir . '/vendor/cakephp/twig-view/',
        'Bake' => $baseDir . '/vendor/cakephp/bake/',
        'DebugKit' => $baseDir . '/vendor/cakephp/debug_kit/',
        'Acl' => $baseDir . '/vendor/cakephp/acl/',
        'CakePdf' => $baseDir . '/vendor/friendsofcake/cakepdf/',
        'Migrations' => $baseDir . '/vendor/cakephp/migrations/'
    ]
];