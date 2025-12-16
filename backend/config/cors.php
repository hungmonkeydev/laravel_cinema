<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'https://frontend-laravel-production.up.railway.app',
    ],

    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,

    'supports_credentials' => true, // Phải là true mới đăng nhập được

];
