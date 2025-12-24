<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('185486726358-rmgdif7i1upk3b737eh1ka34ndm46lnr.apps.googleusercontent.com'),
        'client_secret' => env('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'),
        'redirect' => env('APP_URL') . '/api/auth/google/callback',

        // THÊM DÒNG NÀY ĐỂ TẮT CHECK SSL CHO GOOGLE
        'guzzle' => [
            'verify' => false,
        ],
    ],
    'sendgrid' => [
        'key' => env('SENDGRID_API_KEY'),
    ],

];
