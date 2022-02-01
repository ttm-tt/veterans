<?php
use Cake\Routing\Router;

$routes->plugin('AclEdit', ['path' => '/AclEdit'], function ($routes) {
    $routes->fallbacks('DatasourceRoute');
});
