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

    // Eskiz.uz SMS (one-time codes for "Mening polislarim"). Admin panel: Tizim → SMS (App\Services\SmsSettings)
    'eskiz' => [
        'enabled'  => env('ESKIZ_ENABLED', false),
        'base_url' => env('ESKIZ_BASE_URL', 'https://notify.eskiz.uz/api'),
        'email'    => env('ESKIZ_EMAIL'),
        'password' => env('ESKIZ_PASSWORD'),
        'from'     => env('ESKIZ_FROM', '4546'),
        // Eskiz sends only texts that match a template it has approved; {code} is replaced
        'template' => env('ESKIZ_TEMPLATE', "Xalq Sug'urta: tasdiqlash kodi {code}"),
    ],

    // Click SHOP API (Prepare/Complete callbacks: /api/prepare, /api/complete). See App\Services\Payments\ClickShopApi
    'click' => [
        'service_id'       => env('CLICK_SERVICE_ID'),
        'merchant_id'      => env('CLICK_MERCHANT_ID'),
        'merchant_user_id' => env('CLICK_MERCHANT_USER_ID'),
        'secret_key'       => env('CLICK_SECRET_KEY'),
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
        'eshop' => [
            'base_url' => env('INSURANCE_ESHOP_URL', 'http://online.xalqsugurta.uz/xs/ins/eshop'),
            'username' => env('INSURANCE_ESHOP_USERNAME', 'ESHOP'),
            'password' => env('INSURANCE_ESHOP_PASSWORD'),
        ],
        'osago' => [
            'endpoint' => env('INSURANCE_OSAGO_ENDPOINT', 'http://online.xalqsugurta.uz/xs/ins/doraosago/create'),
            'username' => env('INSURANCE_OSAGO_USERNAME'),
            'password' => env('INSURANCE_OSAGO_PASSWORD'),
            'timeout' => env('INSURANCE_OSAGO_TIMEOUT', 10),
            'retries' => env('INSURANCE_OSAGO_RETRIES', 3),
            // ERSP payment confirmation after a payment through the site's own Payme / Click
            // (admin: Tizim → Sug'urtachi API). Empty = nothing is sent. With a token: Bearer, else the OSAGO login
            'payment_url'   => env('INSURANCE_OSAGO_PAYMENT_URL'),
            'payment_token' => env('INSURANCE_OSAGO_PAYMENT_TOKEN'),
            'agency_id'     => env('INSURANCE_OSAGO_AGENCY_ID'),
        ],
        'accident' => [
            'endpoint' => env('INSURANCE_ACCIDENT_ENDPOINT', 'https://impex-insurance.uz/api/contract/add'),
            'api_token' => env('INSURANCE_ACCIDENT_TOKEN'),
            'timeout' => env('INSURANCE_ACCIDENT_TIMEOUT', 10),
            'retries' => env('INSURANCE_ACCIDENT_RETRIES', 3),
        ],
    ],

];
