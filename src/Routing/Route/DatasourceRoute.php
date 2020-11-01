<?php
/* Copyright (c) 2020 Christoph Theis */

/**
 * Add a datasource prefix to all routes
 * Borrowed from ADmad/cakephp-i18n
 */

namespace App\Routing\Route;

use Cake\Datasource\ConnectionManager;
use Cake\Routing\Route\InflectedRoute;


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
}
