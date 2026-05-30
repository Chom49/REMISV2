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

    /*
    |--------------------------------------------------------------------------
    | TRA (Tanzania Revenue Authority) VAT Verification API
    |--------------------------------------------------------------------------
    | TRA_API_BASE_URL  – Base URL of the TRA API server, e.g.
    |                     https://192.168.1.100:8443
    | TRA_API_KEY       – Bearer token / API key (leave blank if not required)
    | TRA_API_TIMEOUT   – HTTP timeout in seconds (default 15)
    |--------------------------------------------------------------------------
    */
    'tra' => [
        'base_url' => env('TRA_API_BASE_URL', ''),
        'api_key'  => env('TRA_API_KEY', ''),
        'timeout'  => (int) env('TRA_API_TIMEOUT', 15),
        'dev_mode' => env('TRA_DEV_MODE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | BRIQ Bulk SMS (Tanzania)
    |--------------------------------------------------------------------------
    | BRIQ_API_KEY      – API key from your BRIQ dashboard (Settings → API Keys)
    | BRIQ_BASE_URL     – BRIQ API base URL (default: https://karibu.briq.tz)
    | BRIQ_SENDER_ID    – Sender name displayed on recipient's phone
    | BRIQ_BEARER_TOKEN – Optional bearer token, only if your BRIQ app requires it
    |--------------------------------------------------------------------------
    */
    'briq' => [
        'api_key'      => env('BRIQ_API_KEY', ''),
        'base_url'     => env('BRIQ_BASE_URL', 'https://karibu.briq.tz'),
        'sender_id'    => env('BRIQ_SENDER_ID', 'BRIQ'),
        'bearer_token' => env('BRIQ_BEARER_TOKEN', ''),
    ],

];
