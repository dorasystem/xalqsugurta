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

    'napp' => [
        'api_url' => env('NAPP_API_URL', 'https://erspapi.e-osgo.uz/api/v3'),
        'api_token' => env('NAPP_API_TOKEN'),
    ],

    'impex' => [
        'cadaster_api_url' => env('IMPEX_CADASTER_API_URL', 'https://impex-insurance.uz/api/fetch-cadaster'),
    ],

    'payme' => [
        'merchant_id' => env('PAYME_MERCHANT_ID'),
        'kassa_id' => env('PAYME_KASSA_ID', '68f7581688f28864c066266f'),
        'secret_key' => env('PAYME_SECRET_KEY'),
        'test_secret_key' => env('PAYME_TEST_SECRET_KEY', 'trNM0xMZv0bzrNws7E19mgMSCbbjEKxSz#j0'),
        'production_secret_key' => env('PAYME_PRODUCTION_SECRET_KEY', 'YpR&UdoV2@pGoqgqwn#gIX3uCqzxhAwh7z5n@'),
        'endpoint' => env('PAYME_ENDPOINT', 'https://checkout.paycom.uz'),
        'test_mode' => env('PAYME_TEST_MODE', false),
    ],

    'insurance' => [
        'agency_id' => env('INSURANCE_AGENCY_ID', 28),
        'osago' => [
            'endpoint' => env('INSURANCE_OSAGO_ENDPOINT', 'http://online.xalqsugurta.uz/xs/ins/doraosago/create'),
            'username' => env('INSURANCE_OSAGO_USERNAME'),
            'password' => env('INSURANCE_OSAGO_PASSWORD'),
            'timeout' => env('INSURANCE_OSAGO_TIMEOUT', 10),
            'retries' => env('INSURANCE_OSAGO_RETRIES', 3),
        ],
        'accident' => [
            'endpoint' => env('INSURANCE_ACCIDENT_ENDPOINT', 'https://impex-insurance.uz/api/contract/add'),
            'api_token' => env('INSURANCE_ACCIDENT_TOKEN'),
            'timeout' => env('INSURANCE_ACCIDENT_TIMEOUT', 10),
            'retries' => env('INSURANCE_ACCIDENT_RETRIES', 3),
        ],
    ],

];
