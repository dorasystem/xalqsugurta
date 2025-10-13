<?php

namespace App\Http\Requests\Insurance\Osago;

use Illuminate\Foundation\Http\FormRequest;

class InsurantInfoRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // --- Vehicle information ---
            'gov_number' => ['required', 'string', 'max:15'],
            'tech_passport_series' => ['required', 'string', 'max:5'],
            'tech_passport_number' => ['required', 'string', 'max:15'],
            'model' => ['required', 'string', 'max:100'],
            'car_year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'registration_region' => ['required', 'string', 'max:255'],

            // --- Owner info ---
            'owner_first_name' => ['nullable', 'string', 'max:100'],
            'owner_last_name' => ['nullable', 'string', 'max:100'],
            'owner_middle_name' => ['nullable', 'string', 'max:100'],
            'owner_address' => ['nullable', 'string', 'max:255'],
            'owner_pinfl' => ['nullable', 'digits:14'],

            // --- Applicant info ---
            'applicant_first_name' => ['nullable', 'string', 'max:100'],
            'applicant_last_name' => ['nullable', 'string', 'max:100'],
            'applicant_middle_name' => ['nullable', 'string', 'max:100'],
            'applicant_phone' => ['nullable', 'regex:/^\+?[0-9]{9,15}$/'],

            // --- Insurance details ---
            'insurance_period' => ['required', 'string', 'in:1,3,6,12'],
            'insurance_start_date' => ['required', 'date'],
            'insurance_end_date' => ['required', 'date', 'after:insurance_start_date'],
            'insurance_amount' => ['required', 'numeric', 'min:10000'],

            // --- Driver info arrays ---
            'driver_full_name' => ['required', 'array', 'min:1'],
            'driver_full_info' => ['required', 'array', 'min:1'],
            'driver_full_info.*' => ['required', 'string', 'json'],
            'kinship' => ['required', 'array'],
            'kinship.*' => ['required', 'integer', 'in:0,1,2,3,4,5,6,7,8,9,10'],
        ];
    }

    public function messages(): array
    {
        return [
            // --- General ---
            'required' => __('validation.required'),
            'string' => __('validation.string'),
            'integer' => __('validation.integer'),
            'numeric' => __('validation.numeric'),
            'date' => __('validation.date'),
            'json' => __('validation.json'),
            'array' => __('validation.array'),
            'max' => __('validation.max.string'),
            'min' => __('validation.min.numeric'),
            'regex' => __('validation.regex'),
            'in' => __('validation.in'),
            'after' => __('validation.after'),
            'digits' => __('validation.digits'),

            // --- Custom fields ---
            'gov_number.required' => __('messages.gov_number_required'),
            'tech_passport_series.required' => __('messages.tech_passport_series_required'),
            'tech_passport_number.required' => __('messages.tech_passport_number_required'),
            'driver_full_info.*.json' => __('messages.invalid_driver_json'),
        ];
    }
}
