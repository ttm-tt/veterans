<?php

// Router::extensions(['csv', 'pdf'], true);
$routes->plugin('Shop', ['path' => '/shop'], function ($routes) {
	$routes->setExtensions(['csv', 'pdf']);
    $routes->fallbacks('DatasourceRoute');
});
