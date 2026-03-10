<?php

namespace App\Http\Requests\Insurence\Osgop;

use Illuminate\Foundation\Http\FormRequest;

class OsgopStoreCompanyApplicant extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inn'            => ['required', 'digits:9'],
            'offerta_agreed' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'inn.required'            => __('messages.inn_required'),
            'inn.digits'              => __('messages.inn_must_be_9_digits'),
            'offerta_agreed.required' => __('messages.offerta_required'),
            'offerta_agreed.accepted' => __('messages.offerta_required'),
        ];
    }

    public function attributes(): array
    {
        return [
            'inn' => __('messages.inn'),
        ];
    }
}