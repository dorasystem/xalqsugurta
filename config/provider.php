<?php

return [
    'base_url'     => env('PROVIDER_BASE_URL'),
    'username'     => env('PROVIDER_USERNAME'),
    'password'     => env('PROVIDER_PASSWORD'),
    'sender_pinfl' => env('PROVIDER_SENDER_PINFL'),

    'agency_id' => env('PROVIDER_AGENCY_ID', '546'),

    'calc' => [
        'osgop' => env('PROVIDER_CALC_OSGOP', 'http://online.xalqsugurta.uz/xs/ins/eshop/osgopcalc'),
        'osgor' => env('PROVIDER_CALC_OSGOR', 'http://online.xalqsugurta.uz/xs/ins/eshop/osgorcalc'),
    ],

    'submit' => [
        'osgop'    => env('PROVIDER_SUBMIT_OSGOP',    'http://online.xalqsugurta.uz/xs/ins/eshop/osgop'),
        'osgor'    => env('PROVIDER_SUBMIT_OSGOR',    'http://online.xalqsugurta.uz/xs/ins/eshop/osgor'),
        'accident' => env('PROVIDER_SUBMIT_ACCIDENT', 'http://online.xalqsugurta.uz/xs/ins/website/accident/sale'),
        'tourist'  => env('PROVIDER_SUBMIT_TOURIST',  'http://online.xalqsugurta.uz/xs/ins/website/accident/sale'),
    ],

    'osgop' => [
        'health_life_damage_sum' => env('OSGOP_HEALTH_LIFE_DAMAGE_SUM', 40000000),
        'property_damage_sum'    => env('OSGOP_PROPERTY_DAMAGE_SUM', 4000000),
    ],

    // Xalq Sugurta universal insurance API (gas, property)
    'xalq' => [
        'base_url'  => env('XALQ_BASE_URL', 'http://online.xalqsugurta.uz/xs/ins/unv/gazballonsayt'),
        'username'  => env('XALQ_USERNAME', 'gazballonsayt'),
        'password'  => env('XALQ_PASSWORD', 'dorasystem1'),
        'loan_type' => [
            'gas'      => env('XALQ_LOAN_TYPE_GAS',      '35'),
            'property' => env('XALQ_LOAN_TYPE_PROPERTY', '36'),
            'kasko'    => env('XALQ_LOAN_TYPE_KASKO',    '37'),
        ],
    ],

    // Legacy alias kept for backward compat
    'gas' => [
        'base_url'  => env('XALQ_BASE_URL', 'http://online.xalqsugurta.uz/xs/ins/unv/gazballonsayt'),
        'username'  => env('XALQ_USERNAME', 'gazballonsayt'),
        'password'  => env('XALQ_PASSWORD', 'dorasystem1'),
        'loan_type' => env('XALQ_LOAN_TYPE_GAS', '35'),
    ],
];
