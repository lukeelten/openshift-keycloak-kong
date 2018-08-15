<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/', function (Request $request, Response $response, array $args) use($app) {
    $books = [
        [
            "name" => "The Phoenix Project",
            "authors" => [
                "Gene Kim",
                "Kevin Behr",
                "George Spafford"
            ],
            "isbn" => "978-0-9882625-0-8"
        ],
        [
            "name" => "Java Cookbook",
            "authors" => [
                "Ian F. Darwin"
            ],
            "isbn" => "978-1-449-33704-9"
        ]
    ];

    return $response->withJson($books, 200, JSON_PRETTY_PRINT);
});

$app->options("/*", function (Request $request, Response $response, array $args) {
    return $response->withStatus(204);
});