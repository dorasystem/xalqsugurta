<?php

namespace App\Http\Requests\Insurence\Osgop;

use Illuminate\Foundation\Http\FormRequest;

class OsgopStoreCompanyApplicant extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** "+998 90 123 45 67" → "998901234567" before the rules run */
    protected function prepareForValidation(): void
    {
        $digits = preg_replace('/\D/', '', (string) $this->input('phone'));

        $this->merge(['phone' => strlen($digits) === 9 ? '998' . $digits : $digits]);
    }

    public function rules(): array
    {
        return [
            'inn'   => ['required', 'digits:9'],
            'phone' => ['required', 'regex:/^998[0-9]{9}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'inn.required'            => __('messages.inn_required'),
            'inn.digits'              => __('messages.inn_must_be_9_digits'),
        ];
    }

    public function attributes(): array
    {
        return [
            'inn' => __('messages.inn'),
        ];
    }
}