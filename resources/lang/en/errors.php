<?php

return [
    // General Errors
    'general_error' => 'An error occurred',
    'unknown_error' => 'An unknown error occurred',
    'try_again' => 'Please try again',

    // Insurance Errors
    'insurance' => [
        // Session Errors
        'session_not_found' => 'Application data not found. Please fill out the application again.',
        'session_expired' => 'Session expired. Please fill out the application again.',

        // Order Errors
        'order_not_found' => 'Order not found.',
        'order_create_failed' => 'Error creating order',

        // API Errors
        'api_request_failed' => 'API request error',
        'api_response_invalid' => 'API response error',
        'api_connection_failed' => 'API connection error',

        // Validation Errors
        'validation_failed' => 'Submitted data is invalid',
        'required_fields_missing' => 'Please fill in all required fields',

        // Accident Insurance
        'accident' => [
            'application_failed' => 'Error submitting accident insurance application',
            'calculation_failed' => 'Calculation error',
        ],

        // Property Insurance
        'property' => [
            'application_failed' => 'Error submitting property insurance application',
            'cadaster_not_found' => 'Cadaster data not found',
            'cadaster_invalid' => 'Invalid cadaster number format',
        ],

        // OSAGO Insurance
        'osago' => [
            'application_failed' => 'Error submitting OSAGO insurance application',
            'calculation_failed' => 'OSAGO calculation error',
            'vehicle_not_found' => 'Vehicle not found',
        ],

        // Payment Errors
        'payment' => [
            'processing_failed' => 'Payment processing error',
            'amount_invalid' => 'Invalid payment amount',
            'method_not_selected' => 'Please select a payment method',
        ],
    ],
];
