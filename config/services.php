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
    'mail_enable' => env('MAIL_ENABLE', false),
    'exception_mail' => env('EXCEPTION_MAIL', ''),

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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
    'apple' => [
        'private_key' => env('APPLE_PRIVATE_KEY'),
        'key_id' => env('APPLE_KEY_ID'),
        'issuer_id' => env('APPLE_ISSUER_ID'),
        'bundle_id' => env('APPLE_BUNDLE_ID'),
    ],

    'bunny' => [
        'cdn_url' => env('BUNNY_CDN_URL'),
        'access_key' => env('BUNNY_ACCESS_KEY'),
        'storage_zone' => env('BUNNY_STORAGE_ZONE'),
    ],
];
