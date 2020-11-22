<?php

use Cake\Http\ServerRequest;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

/**
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 *
 */
Router::defaultRouteClass('DatasourceRoute');

Router::extensions(['csv', 'pdf', 'json']);

Router::addUrlFilter(function (array $params, ?ServerRequest $request) {
	if ($request !== null && $request->getParam('ds') && !isset($params['ds'])) {
		$params['ds'] = $request->getParam('ds');
	}

	$params += ['ds' => null];

	return $params;
});	
				
Router::scope('/', function (RouteBuilder $routes) {
    /**
     * Here, we are connecting '/' (base path) to a controller called 'Pages',
     * its action called 'display', and we pass a param to select the view file
     * to use (in this case, src/Template/Pages/home.ctp)...
     */
    $routes->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

    /**
     * ...and connect the rest of 'Pages' controller's URLs.
     */
    $routes->connect('/pages/*', ['controller' => 'Pages', 'action' => 'display']);

	/**
	 * ChT
	 * Route to participants
	 */
	Router::connect('/participants', array('controller' => 'pages', 'action' => 'participants'));
	Router::connect('/count_participants', array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'count_participants'));

	/**
	 * ChT
	 * Route to register
	 */
	Router::connect('/register', array('plugin' => 'shop', 'controller' => 'shops', 'action' => 'wizard'));

    /**
     * Connect catchall routes for all controllers.
     *
     * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
     *    `$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);`
     *    `$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);`
     *
     * Any route class can be used with this method, such as:
     * - DashedRoute
     * - InflectedRoute
     * - Route
     * - Or your own route class
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
     */
    $routes->fallbacks('DatasourceRoute');
});
