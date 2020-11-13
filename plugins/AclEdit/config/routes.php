<?php
use Cake\Routing\Router;

Router::plugin('AclEdit', ['path' => '/AclEdit'], function ($routes) {
    $routes->fallbacks('DatasourceRoute');
});
