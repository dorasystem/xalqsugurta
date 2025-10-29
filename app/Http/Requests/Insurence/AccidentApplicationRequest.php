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
            'applicant.passportSeries' => ['required', 'string', 'size:2'],
            'applicant.passportNumber' => ['required', 'string', 'size:7'],
            'applicant.birthDate' => ['required', 'date', 'before:today'],

            // Applicant personal info
            'applicant.lastName' => ['required', 'string', 'max:255'],
            'applicant.firstName' => ['required', 'string', 'max:255'],
            'applicant.middleName' => ['nullable', 'string', 'max:255'],
            'applicant.address' => ['required', 'string', 'max:500'],
            'applicant.phoneNumber' => ['required', 'string'],
            'applicant.pinfl' => ['required', 'string', 'size:14'],
            'applicant.birthPlace' => ['nullable', 'string', 'max:255'],
            'applicant.birthCountry' => ['nullable', 'string', 'max:255'],
            'applicant.gender' => ['nullable', 'string', 'in:1,2'],
            'applicant.regionId' => ['nullable', 'integer'],
            'applicant.districtId' => ['nullable', 'integer'],

            // Client/Insured passport info
            'client.passportSeries' => ['required', 'string', 'size:2'],
            'client.passportNumber' => ['required', 'string', 'size:7'],
            'client.birthDate' => ['required', 'date', 'before:today'],

            // Client/Insured personal info
            'client.lastName' => ['required', 'string', 'max:255'],
            'client.firstName' => ['required', 'string', 'max:255'],
            'client.middleName' => ['nullable', 'string', 'max:255'],
            'client.address' => ['required', 'string', 'max:500'],
            'client.phoneNumber' => ['required', 'string'],
            'client.pinfl' => ['required', 'string', 'size:14'],
            'client.birthPlace' => ['nullable', 'string', 'max:255'],
            'client.birthCountry' => ['nullable', 'string', 'max:255'],
            'client.gender' => ['nullable', 'string', 'in:1,2'],
            'client.regionId' => ['nullable', 'integer'],
            'client.districtId' => ['nullable', 'integer'],

            // Insurance calculation
            'insurance_amount' => ['required', 'numeric', 'min:5000000', 'max:50000000'],
            'payment_start_date' => ['required', 'date', 'after_or_equal:today'],
            'payment_end_date' => ['required', 'date', 'after:payment_start_date'],

            // Checkbox
            'is_applicant_owner' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'applicant.passportSeries' => __('insurance.person.passport_series'),
            'applicant.passportNumber' => __('insurance.person.passport_number'),
            'applicant.birthDate' => __('insurance.person.birth_date'),
            'applicant.lastName' => __('insurance.person.last_name'),
            'applicant.firstName' => __('insurance.person.first_name'),
            'applicant.middleName' => __('insurance.person.middle_name'),
            'applicant.address' => __('insurance.person.address'),
            'applicant.phoneNumber' => __('insurance.person.phone'),
            'applicant.pinfl' => __('insurance.person.pinfl'),

            'client.passportSeries' => __('insurance.person.passport_series'),
            'client.passportNumber' => __('insurance.person.passport_number'),
            'client.birthDate' => __('insurance.person.birth_date'),
            'client.lastName' => __('insurance.person.last_name'),
            'client.firstName' => __('insurance.person.first_name'),
            'client.middleName' => __('insurance.person.middle_name'),
            'client.address' => __('insurance.person.address'),
            'client.phoneNumber' => __('insurance.person.phone'),
            'client.pinfl' => __('insurance.person.pinfl'),

            'insurance_amount' => __('messages.insurance_sum'),
            'payment_start_date' => __('messages.start_date'),
            'payment_end_date' => __('messages.end_date'),
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'required' => __('validation.required'),
            'string' => __('validation.string'),
            'size' => __('validation.size'),
            'max' => __('validation.max.string'),
            'min' => __('validation.min.numeric'),
            'numeric' => __('validation.numeric'),
            'date' => __('validation.date'),
            'before' => __('validation.before'),
            'after' => __('validation.after'),
            'after_or_equal' => __('validation.after_or_equal'),
            'boolean' => __('validation.boolean'),
            'integer' => __('validation.integer'),
        ];
    }
}
