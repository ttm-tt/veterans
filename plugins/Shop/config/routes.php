<?php
use Cake\Routing\Router;

Router::extensions(['csv', 'pdf'], true);
Router::plugin('Shop', ['path' => '/shop'], function ($routes) {
    $routes->fallbacks('DatasourceRoute');
});
