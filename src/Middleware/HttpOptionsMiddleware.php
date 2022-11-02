<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
 
class HttpOptionsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = $handler->handle($request);
		$response->withHeader('Access-Control-Allow-Origin', '*');

		if ($request->getMethod() == 'OPTIONS')
        {
            $method = $request->getHeader('Access-Control-Request-Method');
            $headers = $request->getHeader('Access-Control-Request-Headers');
            $allowed = empty($method) ? 'GET, POST, PUT, DELETE' : $method;
 
            $response = $response
                            ->withHeader('Access-Control-Allow-Headers', $headers)
                            ->withHeader('Access-Control-Allow-Methods', $allowed)
                            ->withHeader('Access-Control-Allow-Credentials', 'true')
                            ->withHeader('Access-Control-Max-Age', '86400')
			;
        }
  
        return $response;
    }
}
