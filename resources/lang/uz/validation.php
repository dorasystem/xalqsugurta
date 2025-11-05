<?php

return [
    //Osago validations
    'gov_number_required' => 'Avtomobil davlat raqami majburiy.',
    'tech_passport_series_required' => 'Tex pasport seriyasi majburiy.',
    'tech_passport_number_required' => 'Tex pasport raqami majburiy.',
    'invalid_driver_json' => 'Haydovchi maʼlumotlari formati notoʻgʻri.',

    'birth_date_required' => 'Tug\'ilgan sana majburiy',
    'passport_seria_required' => 'Passport seriyasi majburiy',
    'passport_number_required' => 'Passport raqami majburiy',
    'pinfl_required' => 'JSHSHIR majburiy',
    'consent_required' => 'Rozilik majburiy',
    'passport_seria_min' => 'Passport seriyasi kamida 2 ta belgi bo\'lishi kerak',
    'passport_seria_max' => 'Passport seriyasi ko\'pi bilan 2 ta belgi bo\'lishi kerak',
    'passport_number_min' => 'Passport raqami kamida 7 ta raqam bo\'lishi kerak',
    'passport_number_max' => 'Passport raqami ko\'pi bilan 7 ta raqam bo\'lishi kerak',
    'passport_seria_size' => 'Passport seriyasi 2 ta belgi bo\'lishi kerak',
    'passport_number_size' => 'Passport raqami 7 ta raqamdan iborat bo\'lishi kerak',
    'first_name_required' => 'Ism majburiy',
    'last_name_required' => 'Familiya majburiy',
    'middle_name_required' => 'Otasining ismi majburiy',
    'address_required' => 'Manzil majburiy',
    'phone_required' => 'Telefon raqami majburiy',
    'insurance_amount_required' => 'Sug\'urta summasi majburiy',
    'start_date_required' => 'Boshlanish sanasi majburiy',
    'end_date_required' => 'Tugash sanasi majburiy',



    // --- General ---
    'required' => 'Bu maydon to‘ldirilishi shart.',
    'string' => 'Bu maydon matn shaklida bo‘lishi kerak.',
    'integer' => 'Bu maydon butun son bo‘lishi kerak.',
    'numeric' => 'Bu maydon raqam bo‘lishi kerak.',
    'date' => 'Bu maydon to‘g‘ri sana formatida bo‘lishi kerak.',
    'birth_date_format' => 'Tug\'ilgan sana Y-m-d formatida bo\'lishi kerak.',
    'json' => 'Bu maydon to‘g‘ri JSON formatida bo‘lishi kerak.',
    'array' => 'Bu maydon massiv bo‘lishi kerak.',
    'max' => [
        'string' => 'Bu maydon :max ta belgidan oshmasligi kerak.',
        'array' => 'Bu maydon :max tadan ortiq elementga ega bo‘lmasligi kerak.',
    ],
    'min' => [
        'numeric' => 'Eng kichik qiymat :min bo‘lishi kerak.',
        'string' => 'Kamida :min ta belgi bo‘lishi kerak.',
        'array' => 'Kamida :min ta element bo‘lishi kerak.',
    ],
    'regex' => 'Maydon formati noto‘g‘ri.',
    'in' => 'Tanlangan qiymat noto‘g‘ri.',
    'after' => 'Tugash sanasi boshlanish sanasidan keyin bo‘lishi kerak.',
    'digits' => 'Bu maydon :digits ta raqamdan iborat bo‘lishi kerak.',

    // --- Custom fields ---
    'gov_number_required' => 'Davlat raqami majburiy.',
    'tech_passport_series_required' => 'Texnik pasport seriyasi majburiy.',
    'tech_passport_number_required' => 'Texnik pasport raqami majburiy.',
    'invalid_driver_json' => 'Haydovchi ma’lumotlari noto‘g‘ri formatda.',

    // --- Other Info ---
    'other_info.required' => 'Transport vositasi haqida qo‘shimcha ma’lumot kiritilishi shart.',
    'other_info.array' => 'Transport vositasi ma’lumotlari massiv formatida bo‘lishi kerak.',
    'other_info.required_array_keys' => 'Majburiy maydonlar yo‘q: techPassportIssueDate, typeId yoki bodyNumber.',

    // --- Owner Info ---
    'owner_infos.required' => 'Egasining ma’lumotlari majburiy.',
    'owner_infos.array' => 'Egasining ma’lumotlari massiv formatida bo‘lishi kerak.',
    'owner_infos.required_array_keys' => 'Majburiy maydonlar yo‘q: regionId, districtId, issuedBy, issueDate, gender, birthDate yoki address.',

    // --- Applicant Info ---
    'applicant_infos.array' => 'Arizachi ma’lumotlari massiv formatida bo‘lishi kerak.',
    'applicant_infos.required_array_keys' => 'Majburiy maydonlar yo‘q: regionId, districtId, issuedBy, issueDate, gender, birthDate yoki address.',

    // --- Insurance Info ---
    'insurance_infos.required' => 'Sug‘urta ma’lumotlari majburiy.',
    'insurance_infos.array' => 'Sug‘urta ma’lumotlari massiv formatida bo‘lishi kerak.',
    'insurance_infos.required_array_keys' => 'Majburiy maydonlar yo‘q: amount, period yoki insuranceAmount.',

    // --- Policy ---
    'policy_start_date.required' => 'Polis boshlanish sanasi majburiy.',
    'policy_end_date.required' => 'Polis tugash sanasi majburiy.',
    'policy_end_date.after' => 'Polis tugash sanasi boshlanish sanasidan keyin bo‘lishi kerak.',

    // --- Drivers ---
    'driver_limit.required' => 'Haydovchilar soni chegarasi tanlanishi kerak.',
    'driver_limit.in' => 'Haydovchilar chegarasi limited yoki unlimited bo‘lishi kerak.',
    'driver_full_info.required_array_keys' => 'driver_full_info ichida majburiy maydonlar yo‘q.',
    'driver_full_info.array' => 'Haydovchi ma’lumotlari massiv formatida bo‘lishi kerak.',

    // --- Kinship ---
    'kinship.*.in' => 'Tanlangan qarindoshlik turi noto‘g‘ri.',
];
