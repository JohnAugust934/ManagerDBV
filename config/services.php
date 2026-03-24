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

    'telegram' => [
        'enabled' => env('TELEGRAM_NOTIFICATIONS_ENABLED', false),
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
        'parse_mode' => env('TELEGRAM_PARSE_MODE', 'HTML'),
        'timeout' => (int) env('TELEGRAM_TIMEOUT', 10),
        'admin_notifications' => env('TELEGRAM_ADMIN_NOTIFICATIONS', true),
        'error_notifications' => env('TELEGRAM_ERROR_NOTIFICATIONS', true),
        'error_dedup_seconds' => (int) env('TELEGRAM_ERROR_DEDUP_SECONDS', 300),
        'suppress_transient_db_errors' => env('TELEGRAM_SUPPRESS_TRANSIENT_DB_ERRORS', true),
        'transient_db_suppress_windows' => env('TELEGRAM_TRANSIENT_DB_SUPPRESS_WINDOWS', '02:45-04:45'),
    ],

];
