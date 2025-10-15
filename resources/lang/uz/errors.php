<?php

return [
    // General Errors
    'general_error' => 'Xatolik yuz berdi',
    'unknown_error' => 'Noma\'lum xatolik yuz berdi',
    'try_again' => 'Iltimos, qaytadan urinib ko\'ring',

    // Insurance Errors
    'insurance' => [
        // Session Errors
        'session_not_found' => 'Ariza ma\'lumotlari topilmadi. Iltimos, qaytadan ariza to\'ldiring.',
        'session_expired' => 'Sessiya muddati tugadi. Iltimos, qaytadan ariza to\'ldiring.',

        // Order Errors
        'order_not_found' => 'Buyurtma topilmadi.',
        'order_create_failed' => 'Buyurtma yaratishda xatolik',

        // API Errors
        'api_request_failed' => 'API so\'rovida xatolik',
        'api_response_invalid' => 'API javobida xatolik',
        'api_connection_failed' => 'API bilan bog\'lanishda xatolik',

        // Validation Errors
        'validation_failed' => 'Kiritilgan ma\'lumotlar noto\'g\'ri',
        'required_fields_missing' => 'Barcha majburiy maydonlarni to\'ldiring',

        // Accident Insurance
        'accident' => [
            'application_failed' => 'Baxtsiz hodisa sug\'urtasi arizasini yuborishda xatolik',
            'calculation_failed' => 'Hisoblashda xatolik',
        ],

        // Property Insurance
        'property' => [
            'application_failed' => 'Mol-mulk sug\'urtasi arizasini yuborishda xatolik',
            'cadaster_not_found' => 'Kadastr raqami bo\'yicha ma\'lumot topilmadi',
            'cadaster_invalid' => 'Kadastr raqami formati noto\'g\'ri',
        ],

        // OSAGO Insurance
        'osago' => [
            'application_failed' => 'OSAGO sug\'urtasi arizasini yuborishda xatolik',
            'calculation_failed' => 'OSAGO hisoblashda xatolik',
            'vehicle_not_found' => 'Transport vositasi topilmadi',
        ],

        // Payment Errors
        'payment' => [
            'processing_failed' => 'To\'lovni amalga oshirishda xatolik',
            'amount_invalid' => 'To\'lov summasi noto\'g\'ri',
            'method_not_selected' => 'To\'lov usulini tanlang',
        ],
    ],
];
