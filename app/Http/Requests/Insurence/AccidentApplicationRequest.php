<?php

namespace App\Http\Requests\Insurence;

use Illuminate\Foundation\Http\FormRequest;

final class AccidentApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Applicant passport info
            'applicant_passport_series' => ['required', 'string', 'size:2'],
            'applicant_passport_number' => ['required', 'string', 'size:7'],
            'applicant_birthDate' => ['required', 'date', 'before:today'],

            // Applicant personal info
            'applicant_last_name' => ['required', 'string', 'max:255'],
            'applicant_first_name' => ['required', 'string', 'max:255'],
            'applicant_middle_name' => ['nullable', 'string', 'max:255'],
            'applicant_address' => ['required', 'string', 'max:500'],
            'applicant_phone_number' => ['required', 'string', 'regex:/^\+998\d{9}$/'],
            'applicant_pinfl' => ['nullable', 'string', 'size:14'],
            'applicant_birth_place' => ['nullable', 'string', 'max:255'],
            'applicant_birth_country' => ['nullable', 'string', 'max:255'],
            'applicant_gender' => ['nullable', 'string', 'in:1,2'],
            'applicant_region_id' => ['nullable', 'integer'],
            'applicant_district_id' => ['nullable', 'integer'],

            // Client/Insured passport info
            'client_passport_series' => ['required', 'string', 'size:2'],
            'client_passport_number' => ['required', 'string', 'size:7'],
            'client_birthDate' => ['required', 'date', 'before:today'],

            // Client/Insured personal info
            'client_last_name' => ['required', 'string', 'max:255'],
            'client_first_name' => ['required', 'string', 'max:255'],
            'client_middle_name' => ['nullable', 'string', 'max:255'],
            'client_address' => ['required', 'string', 'max:500'],
            'client_phone_number' => ['required', 'string', 'regex:/^\+998\d{9}$/'],
            'client_pinfl' => ['nullable', 'string', 'size:14'],
            'client_birth_place' => ['nullable', 'string', 'max:255'],
            'client_birth_country' => ['nullable', 'string', 'max:255'],
            'client_gender' => ['nullable', 'string', 'in:1,2'],
            'client_region_id' => ['nullable', 'integer'],
            'client_district_id' => ['nullable', 'integer'],

            // Insurance calculation
            'insurance_amount' => ['required', 'numeric', 'min:5000000', 'max:50000000'],
            'payment_start_date' => ['required', 'date', 'after_or_equal:today'],
            'payment_end_date' => ['required', 'date', 'after:payment_start_date'],

            // Checkbox
            'is_applicant_owner' => ['nullable', 'boolean'],
            'agreement' => ['required', 'accepted'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'applicant_passport_series' => __('messages.applicant_passport_series'),
            'applicant_passport_number' => __('messages.applicant_passport_number'),
            'applicant_birthDate' => __('insurance.passport.birth_date'),
            'applicant_last_name' => __('messages.applicant_last_name'),
            'applicant_first_name' => __('messages.applicant_first_name'),
            'applicant_middle_name' => __('messages.applicant_middle_name'),
            'applicant_address' => __('messages.applicant_address'),
            'applicant_phone_number' => __('messages.applicant_phone_number'),

            'client_passport_series' => __('insurance.passport.series'),
            'client_passport_number' => __('insurance.passport.number'),
            'client_birthDate' => __('insurance.passport.birth_date'),
            'client_last_name' => __('insurance.person.last_name'),
            'client_first_name' => __('insurance.person.first_name'),
            'client_middle_name' => __('insurance.person.middle_name'),
            'client_address' => __('insurance.person.address'),
            'client_phone_number' => __('insurance.person.telephone_number'),

            'insurance_amount' => __('messages.insurance_amount'),
            'payment_start_date' => __('messages.start_date'),
            'payment_end_date' => __('messages.end_date'),
            'agreement' => __('messages.agreement'),
        ];
    }
}
