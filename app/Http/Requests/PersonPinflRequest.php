<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonPinflRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'senderPinfl' => 'required',
            'passport_series' => 'required',
            'passport_number' => 'required',
            'pinfl' => 'required',
            'isConsent' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'senderPinfl.required' => __('validation.pinfl_required'),
            'passport_series.required' => __('validation.passport_seria_required'),
            'passport_number.required' => __('validation.passport_number_required'),
            'pinfl.required' => __('validation.pinfl_required'),
            'isConsent.required' => __('validation.consent_required'),
        ];
    }
}
