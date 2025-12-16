<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        '*', // Cho phép tất cả các nguồn truy cập
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Quan trọng: Khi dùng '*' ở trên thì phải để cái này là false
    // Nếu sau này cần đăng nhập (Login) bị lỗi, bạn sửa false thành true
    // và thay dấu '*' ở trên bằng link web cụ thể của bạn.
    'supports_credentials' => false, 

];