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
        'cities' => [
            'city_cairo' => 'Cairo',
            'city_giza' => 'Giza',
            'city_alexandria' => 'Alexandria',
            'city_qalyubia' => 'Qalyubia',
            'city_dakahlia' => 'Dakahlia',
            'city_red_sea' => 'Red Sea',
            'city_beheira' => 'Beheira',
            'city_fayoum' => 'Fayoum',
            'city_gharbia' => 'Gharbia',
            'city_ismailia' => 'Ismailia',
            'city_monufia' => 'Monufia',
            'city_minya' => 'Minya',
            'city_qena' => 'Qena',
            'city_new_valley' => 'New Valley',
            'city_north_sinai' => 'North Sinai',
            'city_port_said' => 'Port Said',
            'city_sharqia' => 'Sharqia',
            'city_south_sinai' => 'South Sinai',
            'city_suez' => 'Suez',
            'city_luxor' => 'Luxor',
            'city_aswan' => 'Aswan',
            'city_assiut' => 'Assiut',
            'city_beni_suef' => 'Beni Suef',
            'city_damietta' => 'Damietta',
            'city_kafr_el_sheikh' => 'Kafr El Sheikh',
            'city_matrouh' => 'Matrouh',
            'city_sohag' => 'Sohag',
        ],
        'mock_fallback' => env('PRINTLET_MOCK_FALLBACK', true),
    ],

    'instapay' => [
        'handle' => env('INSTAPAY_HANDLE', 'quirka@instapay'),
        'phone' => env('INSTAPAY_PHONE', '+20 100 000 0000'),
        'account_name' => env('INSTAPAY_ACCOUNT_NAME', 'Quirka Art Prints'),
        'payment_url' => env('INSTAPAY_PAYMENT_URL', 'https://ipn.eg/'),
    ],

];
