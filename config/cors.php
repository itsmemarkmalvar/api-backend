<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Accept',
        'Origin',
        'Access-Control-Allow-Origin'
    ],
    'exposed_headers' => ['Authorization'],
    'max_age' => 86400,
    'supports_credentials' => false,
    'paths' => ['*'],
]; 