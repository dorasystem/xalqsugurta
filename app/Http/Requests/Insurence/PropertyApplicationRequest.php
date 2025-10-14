<?php

namespace App\Http\Requests\Insurence;

use Illuminate\Foundation\Http\FormRequest;

final class PropertyApplicationRequest extends FormRequest
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
            // Property data
            'property.cadasterNumber' => ['required', 'string', 'regex:/^\d{2}:\d{2}:\d{2}:\d{2}:\d{2}:\d{4}$/'],
            'property.address' => ['required', 'string', 'max:500'],
            'property.shortAddress' => ['required', 'string', 'max:255'],
            'property.street' => ['nullable', 'string', 'max:255'],
            'property.tip' => ['required', 'string'],
            'property.vid' => ['required', 'string'],
            'property.tipText' => ['required', 'string', 'max:255'],
            'property.vidText' => ['required', 'string', 'max:255'],
            'property.objectArea' => ['required', 'string'],
            'property.objectAreaL' => ['nullable', 'string'],
            'property.objectAreaU' => ['nullable', 'string'],
            'property.regionId' => ['required', 'string'],
            'property.region' => ['required', 'string', 'max:255'],
            'property.districtId' => ['required', 'string'],
            'property.district' => ['required', 'string', 'max:255'],
            'property.domNum' => ['nullable', 'string', 'max:50'],
            'property.kvartiraNum' => ['nullable', 'string', 'max:50'],
            'property.neighborhood' => ['nullable', 'string', 'max:255'],
            'property.neighborhoodId' => ['nullable', 'string'],

            // Owner passport info
            'owner.passportSeries' => ['required', 'string', 'size:2'],
            'owner.passportNumber' => ['required', 'string', 'size:7'],
            'owner.birthDate' => ['required', 'date', 'before:today'],

            // Owner personal info
            'owner.lastName' => ['required', 'string', 'max:255'],
            'owner.firstName' => ['required', 'string', 'max:255'],
            'owner.middleName' => ['nullable', 'string', 'max:255'],
            'owner.address' => ['required', 'string', 'max:500'],
            'owner.phoneNumber' => ['required', 'string'],
            'owner.pinfl' => ['nullable', 'string', 'size:14'],
            'owner.inn' => ['nullable', 'string', 'max:50'],
            'owner.type' => ['nullable', 'string'],
            'owner.percent' => ['nullable', 'string'],
            'owner.gender' => ['nullable', 'string', 'in:1,2'],

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
            'applicant.pinfl' => ['nullable', 'string', 'size:14'],
            'applicant.inn' => ['nullable', 'string', 'max:50'],
            'applicant.gender' => ['nullable', 'string', 'in:1,2'],

            // Insurance calculation
            'insurance_amount' => ['required', 'numeric', 'min:50000000', 'max:500000000'],
            'payment_start_date' => ['required', 'date', 'after_or_equal:today'],
            'payment_end_date' => ['required', 'date', 'after:payment_start_date'],

            // Checkbox
            'is_owner_applicant' => ['nullable', 'boolean'],
            'agreement' => ['required', 'accepted'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'property.cadasterNumber' => 'Kadastr raqami',
            'property.address' => 'Mulk manzili',

            'owner.passportSeries' => __('messages.owner_passport_series'),
            'owner.passportNumber' => __('messages.owner_passport_number'),
            'owner.birthDate' => __('insurance.passport.birth_date'),
            'owner.lastName' => __('messages.owner_last_name'),
            'owner.firstName' => __('messages.owner_first_name'),
            'owner.middleName' => __('messages.owner_middle_name'),
            'owner.address' => __('messages.owner_address'),
            'owner.phoneNumber' => __('messages.owner_phone_number'),

            'applicant.passportSeries' => __('messages.applicant_passport_series'),
            'applicant.passportNumber' => __('messages.applicant_passport_number'),
            'applicant.birthDate' => __('insurance.passport.birth_date'),
            'applicant.lastName' => __('messages.applicant_last_name'),
            'applicant.firstName' => __('messages.applicant_first_name'),
            'applicant.middleName' => __('messages.applicant_middle_name'),
            'applicant.address' => __('messages.applicant_address'),
            'applicant.phoneNumber' => __('messages.applicant_phone_number'),

            'insurance_amount' => __('messages.insurance_amount'),
            'payment_start_date' => __('messages.start_date'),
            'payment_end_date' => __('messages.end_date'),
            'agreement' => __('messages.agreement'),
        ];
    }
}
