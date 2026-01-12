<?php

namespace App\Http\Requests\Insurence;

use Illuminate\Foundation\Http\FormRequest;

final class GazApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Clean phone number: remove spaces and keep only digits
        if ($this->has('owner.phoneNumber')) {
            $phoneNumber = $this->input('owner.phoneNumber');

            // Remove all non-digit characters (spaces, +, etc.)
            $phoneNumber = preg_replace('/[^\d]/', '', $phoneNumber);

            // Ensure it starts with 998 for Uzbekistan
            // Format should be: 998 + 9 digits = 12 digits total
            if (str_starts_with($phoneNumber, '998')) {
                // Already has 998 prefix, keep only first 12 digits
                $phoneNumber = substr($phoneNumber, 0, 12);
            } elseif (strlen($phoneNumber) === 9) {
                // Has 9 digits (local number), add 998 prefix
                $phoneNumber = '998' . $phoneNumber;
            } elseif (strlen($phoneNumber) > 9) {
                // Has more than 9 digits but no 998, assume it includes country code
                // Keep last 12 digits or add 998 prefix if needed
                if (strlen($phoneNumber) >= 12) {
                    $phoneNumber = substr($phoneNumber, -12);
                } else {
                    $phoneNumber = '998' . substr($phoneNumber, -9);
                }
            }
            // If less than 9 digits, leave as is (validation will catch it)

            // Merge cleaned phone number back into owner array
            $owner = $this->input('owner', []);
            $owner['phoneNumber'] = $phoneNumber;
            $this->merge(['owner' => $owner]);
        }
    }

    public function rules(): array
    {
        return [
            'owner.phoneNumber' => ['required', 'string', 'regex:/^998[0-9]{9}$/'],

            // Calculation
            'insurance_amount' => ['required', 'numeric', 'min:100000'],

            // Period
            'payment_start_date' => ['required', 'date'],
            'payment_end_date' => ['required', 'date', 'after:payment_start_date'],
        ];
    }
}


