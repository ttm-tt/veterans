<?php
$baseDir = dirname(dirname(__FILE__));

return [
    'plugins' => [
        'Acl' => $baseDir . '/vendor/cakephp/acl/',
        'AclEdit' => $baseDir . '/plugins/AclEdit/',
        'Bake' => $baseDir . '/vendor/cakephp/bake/',
        'CakePdf' => $baseDir . '/vendor/friendsofcake/cakepdf/',
        'Cake/TwigView' => $baseDir . '/vendor/cakephp/twig-view/',
        'DebugKit' => $baseDir . '/vendor/cakephp/debug_kit/',
        'ErrorEmail' => $baseDir . '/vendor/ebrigham1/cakephp-error-email/',
        'Migrations' => $baseDir . '/vendor/cakephp/migrations/',
        'Recaptcha' => $baseDir . '/vendor/giginc/cakephp3-recaptcha/',
        'Shim' => $baseDir . '/vendor/dereuromark/cakephp-shim/',
        'Shop' => $baseDir . '/plugins/Shop/',
        'Wizard' => $baseDir . '/plugins/Wizard/',
    ],
];
