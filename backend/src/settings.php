<?php
$keycloakService = strtoupper(getenv("KEYCLOAK_SERVICE"));

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Monolog settings
        'logger' => [
            'name' => 'book-service',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        'db' => [
            'host' => getenv('MONGO_HOST'),
            'username' => getenv('MONGO_USER'),
            'password' => getenv('MONGO_PASSWORD')
        ],

        'keycloak' => [
            'url' => 'http://' . getenv($keycloakService . "_SERVICE_HOST") . ":" . getenv($keycloakService . "_SERVICE_PORT") . "/auth/",
            'realm' => getenv("KEYCLOAK_REALM")
        ]
    ],
];
