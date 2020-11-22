<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App;

use ErrorEmail\Middleware\ErrorHandlerMiddleware;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Http\ServerRequest;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use App\Middleware\HttpOptionsMiddleware;


/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication
{
	// Bootstrapping
	public function bootstrap() : void {
		parent::bootstrap();
		
		if (PHP_SAPI === 'cli')
			$this->bootstrapCli();
		
		$this->addPlugin('Acl');
		$this->addPlugin('AclEdit');
		$this->addPlugin('CakePdf');
		$this->addPlugin('Shop');
		$this->addPlugin('Wizard', ['bootstrap' => false, 'routes' => false]);
		$this->addPlugin('ErrorEmail');
		$this->addPlugin('Migrations');

		if (PHP_SAPI !== 'cli') {
			if (Configure::read('debug')) {
				// If cache is disabled clear all entries
				// Else clear at least routing entries so we can show DebugKit
				if (Configure::read('disableCache'))
					Cache::clearAll();
				else
					Cache::clear('_cake_routes_');
			} else if (Configure::read('clearCache')) {
				Cache::clearAll(false);
			}
		}
	}
    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue) : MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(ErrorHandlerMiddleware::class)

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(AssetMiddleware::class)

            // Add routing middleware.
            // Routes collection cache enabled by default, to disable route caching
            // pass null as cacheConfig, example: `new RoutingMiddleware($this)`
            // you might want to disable this cache in case your routing is extremely simple
            ->add(new RoutingMiddleware($this, '_cake_routes_'))
				
			// CORS OPTIONS handler
			->add(new HttpOptionsMiddleware($this))
        
            // Add CSFR protection, if not shops
			// Temp disabled, to many errors, e.g. when users are using Google translate
/*				
			->add(function(
					ServerRequestInterface $request, 
					ResponseInterface $response, 
					callable $next
			) {
				$params = $request->getAttribute('params');

				// Allow all actions for ShopsController
				if ($params['controller'] !== 'Shops') {
		            $csrf = new CsrfProtectionMiddleware();
					return $csrf($request, $response, $next);
				}
				
				return $next($request, $response);
			})
*/
        ;

        return $middlewareQueue;
    }
	
	/**
     * Bootrapping for CLI application.
     *
     * That is when running commands.
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        try {
            $this->addPlugin('Bake');
        } catch (MissingPluginException $e) {
            // Do not halt if the plugin is missing
        }
    }	
	
	/*
	 * By default load config/routes.php, if not done so
	 * Will also add an URL filter: it is not done so if routes were already
	 * added from cache
	 */
    public function routes(RouteBuilder $routes): void
    {
		parent::routes($routes);

		Router::addUrlFilter(function (array $params, ?ServerRequest $request) {
			if ($request !== null && $request->getParam('ds') && !isset($params['ds'])) {
				$params['ds'] = $request->getParam('ds');
			}

			$params += ['ds' => null];

			return $params;
		});		
    }
	
}
