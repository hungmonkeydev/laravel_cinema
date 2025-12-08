<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', '*'],

    //Thêm localhost:3000 vào danh sách cho phép
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost',      // Thêm dòng này
    ],

    'supports_credentials' => true,
    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'allowed_origins' => ['http://localhost:3000'],
    'max_age' => 0,
];
