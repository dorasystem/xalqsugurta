<?php

namespace App\Http\Requests\Insurence;

use Illuminate\Foundation\Http\FormRequest;

class PersonBirthdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'passport_series' => 'required|string|size:2',
            'passport_number' => 'required|string|size:7',
            'birthDate' => 'required|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'passport_series.required' => __('validation.passport_seria_required'),
            'passport_series.size' => __('validation.passport_seria_size'),
            'passport_number.required' => __('validation.passport_number_required'),
            'passport_number.size' => __('validation.passport_number_size'),
            'birthDate.required' => __('validation.birth_date_required'),
            'birthDate.date_format' => __('validation.birth_date_format'),
        ];
    }
}
