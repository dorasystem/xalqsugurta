<?php

namespace App\Http\Requests\Insurence;

use Illuminate\Foundation\Http\FormRequest;

final class GazApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Applicant basics
            'applicant.passportSeries' => ['required', 'string', 'size:2'],
            'applicant.passportNumber' => ['required', 'string', 'size:7'],
            'applicant.birthDate' => ['required', 'date'],
            'applicant.lastName' => ['required', 'string', 'max:100'],
            'applicant.firstName' => ['required', 'string', 'max:100'],
            'applicant.middleName' => ['nullable', 'string', 'max:100'],
            'applicant.address' => ['required', 'string', 'max:500'],
            'applicant.phoneNumber' => ['required', 'regex:/^\\+?[0-9]{9,15}$/'],
            'applicant.pinfl' => ['required', 'digits:14'],
            'applicant.gender' => ['nullable', 'in:1,2'],
            'applicant.regionId' => ['nullable', 'integer'],
            'applicant.districtId' => ['nullable', 'integer'],

            // Cadaster
            'property.cadasterNumber' => ['required', 'string'],
            'property.shortAddress' => ['nullable', 'string', 'max:255'],
            'property.region' => ['nullable', 'string', 'max:255'],
            'property.districtId' => ['nullable', 'string'],
            'property.district' => ['nullable', 'string', 'max:255'],
            'property.objectArea' => ['nullable', 'string'],
            'property.tip' => ['nullable', 'string'],
            'property.vid' => ['nullable', 'string'],
            'property.tipText' => ['nullable', 'string', 'max:255'],
            'property.vidText' => ['nullable', 'string', 'max:255'],
            'property.cost' => ['nullable', 'numeric'],
            'property.is_owner' => ['nullable', 'boolean'],

            // Calculation
            'cost.sum_bank' => ['required', 'numeric', 'min:100000'],

            // Period
            'details.startDate' => ['required', 'date'],
            'details.endDate' => ['required', 'date', 'after:details.startDate'],
        ];
    }

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
            'after' => __('validation.after'),
            'digits' => __('validation.digits'),
            'regex' => __('validation.regex'),
        ];
    }
}


