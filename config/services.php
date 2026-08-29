<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'printlet' => [
        'base_url' => env('PRINTLET_BASE_URL', 'https://stg-portal.printleteg.com/api/integrations/v1'),
        'api_key' => env('PRINTLET_API_KEY', 'prnt_test_default_key'),
        'webhook_secret' => env('PRINTLET_WEBHOOK_SECRET', ''),
        'country_id' => env('PRINTLET_DEFAULT_COUNTRY_ID', 'c1839db2-37eb-4b21-82ea-b248aeef1e97'),
        'country_code' => env('PRINTLET_DEFAULT_COUNTRY_CODE', 'EG'),
        'currency' => env('PRINTLET_DEFAULT_CURRENCY', 'EGP'),
        'shipping_method_id' => env('PRINTLET_DEFAULT_SHIPPING_METHOD_ID', 'sm_standard'),
        'mock_fallback' => env('PRINTLET_MOCK_FALLBACK', true),
    ],

];
