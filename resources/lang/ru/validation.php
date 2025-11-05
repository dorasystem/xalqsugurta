<?php

return [
    //Osago validations
    'gov_number_required' => 'Требуется государственный номер автомобиля.',
    'tech_passport_series_required' => 'Требуется серия техпаспорта.',
    'tech_passport_number_required' => 'Требуется номер техпаспорта.',
    'invalid_driver_json' => 'Неверный формат данных о водителе.',

    'birth_date_required' => 'Дата рождения обязательна',
    'passport_seria_required' => 'Серия паспорта обязательна',
    'passport_number_required' => 'Номер паспорта обязательен',
    'pinfl_required' => 'ПИНФЛ обязателен',
    'consent_required' => 'Согласие обязательно',
    'passport_seria_min' => 'Серия паспорта должна быть не менее 2 символов',
    'passport_seria_max' => 'Серия паспорта должна быть не более 2 символов',
    'passport_number_min' => 'Номер паспорта должен быть не менее 7 символов',
    'passport_number_max' => 'Номер паспорта должен быть не более 7 символов',
    'passport_seria_size' => 'Серия паспорта должна состоять из 2 символов',
    'passport_number_size' => 'Номер паспорта должен состоять из 7 символов',
    'first_name_required' => 'Имя обязательно',
    'last_name_required' => 'Фамилия обязательна',
    'middle_name_required' => 'Отчество обязательно',
    'address_required' => 'Адрес обязателен',
    'phone_required' => 'Номер телефона обязателен',
    'insurance_amount_required' => 'Страховая сумма обязательна',
    'start_date_required' => 'Дата начала обязательна',
    'end_date_required' => 'Дата окончания обязательна',



    // --- General ---
    'required' => 'Это поле обязательно для заполнения.',
    'string' => 'Это поле должно быть строкой.',
    'integer' => 'Это поле должно быть числом.',
    'numeric' => 'Это поле должно быть числом.',
    'date' => 'Это поле должно быть корректной датой.',
    'birth_date_format' => 'Дата рождения должна быть в формате Y-m-d.',
    'json' => 'Это поле должно быть корректной JSON-строкой.',
    'array' => 'Это поле должно быть массивом.',
    'max' => [
        'string' => 'Это поле не может быть длиннее :max символов.',
        'array' => 'Это поле не может содержать более :max элементов.',
    ],
    'min' => [
        'numeric' => 'Минимальное значение — :min.',
        'string' => 'Минимальное количество символов — :min.',
        'array' => 'Минимальное количество элементов — :min.',
    ],
    'regex' => 'Неверный формат данных.',
    'in' => 'Выбранное значение недопустимо.',
    'after' => 'Дата окончания должна быть позже даты начала.',
    'digits' => 'Это поле должно содержать :digits цифр.',

    // --- Custom fields ---
    'gov_number_required' => 'Государственный номер обязателен.',
    'tech_passport_series_required' => 'Серия техпаспорта обязательна.',
    'tech_passport_number_required' => 'Номер техпаспорта обязателен.',
    'invalid_driver_json' => 'Неверный формат данных о водителе.',

    // --- Other Info ---
    'other_info.required' => 'Информация о транспортном средстве обязательна.',
    'other_info.array' => 'Информация о транспортном средстве должна быть в формате массива.',
    'other_info.required_array_keys' => 'Отсутствуют обязательные поля: techPassportIssueDate, typeId или bodyNumber.',

    // --- Owner Info ---
    'owner_infos.required' => 'Информация о владельце обязательна.',
    'owner_infos.array' => 'Информация о владельце должна быть в формате массива.',
    'owner_infos.required_array_keys' => 'Отсутствуют обязательные поля: regionId, districtId, issuedBy, issueDate, gender, birthDate или address.',

    // --- Applicant Info ---
    'applicant_infos.array' => 'Информация о заявителе должна быть в формате массива.',
    'applicant_infos.required_array_keys' => 'Отсутствуют обязательные поля: regionId, districtId, issuedBy, issueDate, gender, birthDate или address.',

    // --- Insurance Info ---
    'insurance_infos.required' => 'Информация о страховании обязательна.',
    'insurance_infos.array' => 'Информация о страховании должна быть в формате массива.',
    'insurance_infos.required_array_keys' => 'Отсутствуют обязательные поля: amount, period или insuranceAmount.',

    // --- Policy ---
    'policy_start_date.required' => 'Дата начала полиса обязательна.',
    'policy_end_date.required' => 'Дата окончания полиса обязательна.',
    'policy_end_date.after' => 'Дата окончания должна быть позже даты начала.',

    // --- Drivers ---
    'driver_limit.required' => 'Тип ограничения водителей обязателен.',
    'driver_limit.in' => 'Тип ограничения должен быть: limited или unlimited.',
    'driver_full_info.required_array_keys' => 'Отсутствуют обязательные поля в driver_full_info.',
    'driver_full_info.array' => 'Информация о водителях должна быть в формате массива.',

    // --- Kinship ---
    'kinship.*.in' => 'Выбран неверный тип родства.',
];
