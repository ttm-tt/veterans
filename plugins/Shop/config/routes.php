<?php
use Cake\Routing\Router;

Router::extensions(['csv', 'pdf'], true);
$routes->plugin('Shop', ['path' => '/shop'], function ($routes) {
    $routes->fallbacks('DatasourceRoute');
});
