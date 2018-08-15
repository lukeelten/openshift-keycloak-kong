<?php

namespace Heinlein;

use Slim\Http\Request;
use Slim\Http\Response;

class CorsMiddleware implements MiddlewareInterface {

    function __invoke(Request $request, Response $response, callable $next): Response {
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Max-Age', "" . (60 * 60)) // Cache access control for 1 hour
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');

        return $next($request, $response);
    }
}