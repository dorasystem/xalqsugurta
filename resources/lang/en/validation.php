
<?php

return [
    //Osago validations
    'gov_number_required' => 'The vehicle state number is required.',
    'tech_passport_series_required' => 'The technical passport series is required.',
    'tech_passport_number_required' => 'The technical passport number is required.',
    'invalid_driver_json' => 'Driver information data format is invalid.',

    'birth_date_required' => 'Birth date is required',
    'passport_seria_required' => 'Passport series is required',
    'passport_number_required' => 'Passport number is required',
    'pinfl_required' => 'PINFL is required',
    'consent_required' => 'Consent is required',
    'passport_seria_min' => 'Passport series must be at least 2 characters',
    'passport_seria_max' => 'Passport series must be at most 2 characters',
    'passport_number_min' => 'Passport number must be at least 7 characters',
    'passport_number_max' => 'Passport number must be at most 7 characters',
    'passport_seria_size' => 'Passport series must be 2 characters',
    'passport_number_size' => 'Passport number must be 7 characters',
    'first_name_required' => 'First name is required',
    'last_name_required' => 'Last name is required',
    'middle_name_required' => 'Middle name is required',
    'address_required' => 'Address is required',
    'phone_required' => 'Phone number is required',
    'insurance_amount_required' => 'Insurance amount is required',
    'start_date_required' => 'Start date is required',
    'end_date_required' => 'End date is required',



    // --- General ---
    'required' => 'This field is required.',
    'string' => 'This field must be a string.',
    'integer' => 'This field must be an integer.',
    'numeric' => 'This field must be a number.',
    'date' => 'This field must be a valid date.',
    'birth_date_format' => 'Birth date must be in the Y-m-d format.',
    'json' => 'This field must be a valid JSON string.',
    'array' => 'This field must be an array.',
    'max' => [
        'string' => 'This field may not be greater than :max characters.',
        'array' => 'This field may not have more than :max items.',
    ],
    'min' => [
        'numeric' => 'This field must be at least :min.',
        'string' => 'This field must be at least :min characters.',
        'array' => 'This field must have at least :min items.',
    ],
    'regex' => 'This field format is invalid.',
    'in' => 'The selected value is invalid.',
    'after' => 'The end date must be after the start date.',
    'digits' => 'This field must be :digits digits.',

    // --- Custom fields ---
    'gov_number_required' => 'The government number is required.',
    'tech_passport_series_required' => 'The technical passport series is required.',
    'tech_passport_number_required' => 'The technical passport number is required.',
    'invalid_driver_json' => 'Invalid driver information format.',

    // --- Other Info ---
    'other_info.required' => 'Vehicle additional information is required.',
    'other_info.array' => 'Vehicle additional information must be an array.',
    'other_info.required_array_keys' => 'Missing required fields in other_info: techPassportIssueDate, typeId, or bodyNumber.',

    // --- Owner Info ---
    'owner_infos.required' => 'Owner information is required.',
    'owner_infos.array' => 'Owner information must be an array.',
    'owner_infos.required_array_keys' => 'Missing required fields in owner_infos: regionId, districtId, issuedBy, issueDate, gender, birthDate, or address.',

    // --- Applicant Info ---
    'applicant_infos.array' => 'Applicant information must be an array.',
    'applicant_infos.required_array_keys' => 'Missing required fields in applicant_infos: regionId, districtId, issuedBy, issueDate, gender, birthDate, or address.',

    // --- Insurance Info ---
    'insurance_infos.required' => 'Insurance information is required.',
    'insurance_infos.array' => 'Insurance information must be an array.',
    'insurance_infos.required_array_keys' => 'Missing required fields in insurance_infos: amount, period, or insuranceAmount.',

    // --- Policy ---
    'policy_start_date.required' => 'The policy start date is required.',
    'policy_end_date.required' => 'The policy end date is required.',
    'policy_end_date.after' => 'The policy end date must be after the start date.',

    // --- Drivers ---
    'driver_limit.required' => 'Driver limit type is required.',
    'driver_limit.in' => 'Driver limit type must be either limited or unlimited.',
    'driver_full_info.required_array_keys' => 'Missing required fields in driver_full_info.',
    'driver_full_info.array' => 'Driver information must be in an array format.',

    // --- Kinship ---
    'kinship.*.in' => 'The selected kinship type is invalid.',
];
