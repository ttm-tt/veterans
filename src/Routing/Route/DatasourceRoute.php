<?php
/* Copyright (c) 2020 Christoph Theis */

/**
 * Add a datasource prefix to all routes
 * Borrowed from ADmad/cakephp-i18n
 */

namespace App\Routing\Route;

use Cake\Controller\ControllerFactory;
use Cake\Datasource\ConnectionManager;
use Cake\Routing\Route\InflectedRoute;
use Psr\Http\Message\ServerRequestInterface;

class DatasourceRoute extends InflectedRoute {
	
    /**
     * Constructor for a Route.
     *
     * @param string $template Template string with parameter placeholders
     * @param array $defaults Array of defaults for the route.
     * @param array $options Array of parameters and additional options for the Route
     *
     * @return void
     */
    public function __construct(string $template, array $defaults = [], array $options = []) {
        if (strpos($template, '{ds}') === false) {
            $template = '/{ds}' . $template;
        }
        if ($template === '/{ds}/') {
            $template = '/{ds}';
        }

        $options['inflect'] = 'underscore';
        $options['persist'][] = 'ds';
		
		$defaults += ['ds' => null];

        if (!array_key_exists('ds', $options)) {
			// Get all configured datasources as an array
			$ds = ConnectionManager::configured();
			// unset 'default' and 'test_debug_kit', we don't need them
			$ds = array_diff($ds, ['default', 'test_debug_kit']);
			// Add an emtpy string so it will match that, too.
			// This is a workaround, that parsing a path where the {ds} parameter
			// is missing will resolve {ds} to an empty string and constructing a
			// path from an array without 'ds' argument will fail because it will
			// try to match an empty string against our pattern
			$ds += [''];
			
            if (count($ds) > 0)
                $options['ds'] = implode('|', $ds);
        }

        parent::__construct($template, $defaults, $options);		
	}	
	
    /**
     * Checks to see if the given URL can be parsed by this route.
	 * 
	 * To find the best route the base class will sort all routes by (length of)
	 * the leading fixed part, but inside such group there is no further sorting.
	 * In case of this type of routes the fixed part will be '/' and the first
	 * matching route there may not be the best match but the first connected.
	 * This results that a plugin part is not recognized as such but as a controller.
	 * Therefore, if we don't have a plugin we will check if the controller exists.
	 * 
	 * Caveat: we don't get an error "controller does not exist" but "missing route"
	 * if we mistyped the controller name.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The URL to attempt to parse.
     * @return array|null An array of request parameters, or null on failure.
     */
    public function parseRequest(ServerRequestInterface $request): ?array
    {
        $params = parent::parseRequest($request);
		
		if ($params === null || $params['plugin'] !== null)
			return $params;
		
		// ControllerFactory does not have any instance variables so we can just
		// create one. We could even use a static instance ...
		if ( (new ControllerFactory())->getControllerClass(
				$request->withAttribute('params', $params)) === null )
			return null;
		
		return $params;
    }
}
