<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

$app->add(new \Heinlein\CorsMiddleware());
$app->add(new \Heinlein\JwtMiddleware($container));
