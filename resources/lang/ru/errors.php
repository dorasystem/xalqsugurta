<?php

return [
    // General Errors
    'general_error' => 'Произошла ошибка',
    'unknown_error' => 'Произошла неизвестная ошибка',
    'try_again' => 'Пожалуйста, попробуйте еще раз',

    // Insurance Errors
    'insurance' => [
        // General Errors
        'general_error' => 'Произошла ошибка',

        // Session Errors
        'session_not_found' => 'Данные заявки не найдены. Пожалуйста, заполните заявку заново.',
        'session_expired' => 'Сессия истекла. Пожалуйста, заполните заявку заново.',

        // Order Errors
        'order_not_found' => 'Заказ не найден.',
        'order_create_failed' => 'Ошибка при создании заказа',

        // API Errors
        'api_request_failed' => 'Ошибка в API запросе',
        'api_response_invalid' => 'Ошибка в ответе API',
        'api_connection_failed' => 'Ошибка подключения к API',

        // Validation Errors
        'validation_failed' => 'Введенные данные неверны',
        'required_fields_missing' => 'Заполните все обязательные поля',

        // Accident Insurance
        'accident' => [
            'application_failed' => 'Ошибка при отправке заявки на страхование от несчастных случаев',
            'calculation_failed' => 'Ошибка при расчете',
        ],

        // Property Insurance
        'property' => [
            'application_failed' => 'Ошибка при отправке заявки на страхование имущества',
            'cadaster_not_found' => 'Данные по кадастровому номеру не найдены',
            'cadaster_invalid' => 'Неверный формат кадастрового номера',
        ],

        // OSAGO Insurance
        'osago' => [
            'application_failed' => 'Ошибка при отправке заявки на страхование ОСАГО',
            'calculation_failed' => 'Ошибка при расчете ОСАГО',
            'vehicle_not_found' => 'Транспортное средство не найдено',
        ],

        // Payment Errors
        'payment' => [
            'processing_failed' => 'Ошибка при обработке платежа',
            'amount_invalid' => 'Неверная сумма платежа',
            'method_not_selected' => 'Выберите способ оплаты',
        ],
    ],
    'all_fields' => 'Пожалуйста, убедитесь в заполнении всех обязательных полей.',
    'connection_with_api' => 'Сервис Eosgouz вернул ошибку: Нет данных для указанных параметров. Пожалуйста, попробуйте еще раз.',
    'unexpected_issue' => 'Произошла неожиданная ошибка. Пожалуйста, попробуйте еще раз.',
    'drivers_limited' => 'Вы можете добавить не более 5 водителей!',
];
