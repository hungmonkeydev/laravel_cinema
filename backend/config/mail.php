<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | Mặc định sẽ lấy từ file .env (MAIL_MAILER).
    | Nếu file .env không có, nó sẽ tự động dùng 'sendgrid'.
    |
    */

    'default' => env('MAIL_MAILER', 'sendgrid'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */

    'mailers' => [

        // Cấu hình SMTP tiêu chuẩn (Dùng cho Gmail, Mailgun, v.v. nếu cần)
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 465),
            'encryption' => env('MAIL_ENCRYPTION', 'ssl'), // Quan trọng
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
        ],

        // --- CẤU HÌNH SENDGRID (QUAN TRỌNG NHẤT) ---
        // Đã được cấu hình cứng để không bị lỗi dù .env có sai host/user
        'sendgrid' => [
            'transport' => 'smtp',
            'host' => 'smtp.sendgrid.net',
            'port' => 2525, // <--- ĐỔI TỪ 587 SANG 2525
            'encryption' => 'tls',
            'username' => 'apikey',
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'sendgrid',
                'log',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hung01634@gmail.com'),
        'name' => env('MAIL_FROM_NAME', 'Viecinema'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    */

    'markdown' => [
        'theme' => 'default',
        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
