<?php

namespace Heinlein;

use Slim\Http\Request;
use Slim\Http\Response;

interface MiddlewareInterface {

    function __invoke(Request $request, Response $response, callable $next) : Response;

}