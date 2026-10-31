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
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

    'google_calendar' => [
        'client_id' => env('GOOGLE_CALENDAR_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CALENDAR_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_CALENDAR_REDIRECT_URL'),
    ],

    'crawler' => [
        'secret' => env('CRAWLER_SERVICE_SECRET'),
    ],

    'mpesa' => [
        'env'          => env('MPESA_ENV', 'sandbox'),
        'key'          => env('MPESA_CONSUMER_KEY'),
        'secret'       => env('MPESA_CONSUMER_SECRET'),
        'passkey'      => env('MPESA_PASSKEY'),
        'shortcode'    => env('MPESA_SHORTCODE'),
        'type'         => env('MPESA_TYPE', 'paybill'),
        'callback_url' => env('MPESA_CALLBACK_URL'),
    ],

    'zadarma' => [
        'key'    => env('ZADARMA_KEY'),
        'secret' => env('ZADARMA_SECRET'),
    ],

    'intasend' => [
        'publishable_key' => env('INTASEND_PUBLISHABLE_KEY'),
        'secret_token'    => env('INTASEND_SECRET_TOKEN'),
        'wallet_id'       => env('INTASEND_WALLET_ID'),
    ],

    'drachtio' => [
        'host' => env('DRACHTIO_HOST', '127.0.0.1'),
        'port' => env('DRACHTIO_PORT', 9022),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
    ],

    'elevenlabs' => [
        'api_key' => env('ELEVENLABS_API_KEY'),
    ],

];
