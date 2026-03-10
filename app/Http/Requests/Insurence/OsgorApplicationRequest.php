<?php

namespace App\Http\Requests\Insurence;

use Illuminate\Foundation\Http\FormRequest;

final class OsgorApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Clean phone number: remove spaces and keep only digits
        if ($this->has('organization.phone')) {
            $phoneNumber = $this->input('organization.phone');

            // Remove all non-digit characters (spaces, +, etc.)
            $phoneNumber = preg_replace('/[^\d]/', '', $phoneNumber);

            // Ensure it starts with 998 for Uzbekistan
            if (str_starts_with($phoneNumber, '998')) {
                $phoneNumber = substr($phoneNumber, 0, 12);
            } elseif (strlen($phoneNumber) === 9) {
                $phoneNumber = '998' . $phoneNumber;
            } elseif (strlen($phoneNumber) > 9) {
                if (strlen($phoneNumber) >= 12) {
                    $phoneNumber = substr($phoneNumber, -12);
                } else {
                    $phoneNumber = '998' . substr($phoneNumber, -9);
                }
            }

            // Merge cleaned phone number back into organization array
            $organization = $this->input('organization', []);
            $organization['phone'] = $phoneNumber;
            $this->merge(['organization' => $organization]);
        }
    }

    public function rules(): array
    {
        return [
            // Organization (Insurant) - matches form fields
            'organization.inn' => ['required', 'string', 'size:9', 'regex:/^\d{9}$/'],
            'organization.name' => ['required', 'string', 'max:255'],
            'organization.address' => ['required', 'string', 'max:500'],
            'organization.oked' => ['required', 'string', 'max:255'],
            'organization.phone' => ['required', 'string', 'regex:/^998[0-9]{9}$/'],
            'organization.region_id' => ['required', 'string'],
            'organization.district_id' => ['required', 'string'],
            'organization.ownership_form_id' => ['nullable', 'string'],

            // Insurance details - matches form fields
            'insurance_amount' => ['required', 'numeric', 'min:50000000', 'max:500000000'],
            'payment_start_date' => ['required', 'date', 'after_or_equal:today'],
            'payment_end_date' => ['required', 'date', 'after:payment_start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'organization.inn.required' => __('validation.required', ['attribute' => __('messages.inn')]),
            'organization.inn.size' => __('validation.size.string', ['attribute' => __('messages.inn'), 'size' => 9]),
            'organization.inn.regex' => __('messages.inn_invalid'),
            'organization.name.required' => __('validation.required', ['attribute' => __('messages.organization_name')]),
            'organization.address.required' => __('validation.required', ['attribute' => __('messages.address')]),
            'organization.oked.required' => __('validation.required', ['attribute' => __('messages.oked')]),
            'organization.phone.required' => __('validation.required', ['attribute' => __('messages.phone_number')]),
            'organization.phone.regex' => __('validation.regex', ['attribute' => __('messages.phone_number')]),
            'organization.region_id.required' => __('validation.required', ['attribute' => __('messages.region')]),
            'organization.district_id.required' => __('validation.required', ['attribute' => __('messages.district')]),
            'insurance_amount.required' => __('validation.required', ['attribute' => __('messages.insurance_amount')]),
            'insurance_amount.min' => __('validation.min.numeric', ['attribute' => __('messages.insurance_amount'), 'min' => '50,000,000']),
            'insurance_amount.max' => __('validation.max.numeric', ['attribute' => __('messages.insurance_amount'), 'max' => '500,000,000']),
            'payment_start_date.required' => __('validation.required', ['attribute' => __('messages.start_date')]),
            'payment_start_date.after_or_equal' => __('validation.after_or_equal', ['attribute' => __('messages.start_date'), 'date' => __('messages.today')]),
            'payment_end_date.required' => __('validation.required', ['attribute' => __('messages.end_date')]),
            'payment_end_date.after' => __('validation.after', ['attribute' => __('messages.end_date'), 'date' => __('messages.start_date')]),
        ];
    }
}
