<?php

namespace App\Http\Requests\Insurence;

use Illuminate\Foundation\Http\FormRequest;

class PersonBirthdate extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'birth_date' => 'required|date',
            'passport_seria' => 'required|string|min:2|max:2',
            'passport_number' => 'required|string|min:7|max:7',
        ];
    }

    public function messages(): array
    {
        return [
            'birth_date.required' => __('validation.birth_date_required'),
            'passport_seria.required' => __('validation.passport_seria_required'),
            'passport_number.required' => __('validation.passport_number_required'),
            'passport_seria.min' => __('validation.passport_seria_min'),
            'passport_seria.max' => __('validation.passport_seria_max'),
            'passport_number.min' => __('validation.passport_number_min'),
            'passport_number.max' => __('validation.passport_number_max'),
        ];
    }
}
