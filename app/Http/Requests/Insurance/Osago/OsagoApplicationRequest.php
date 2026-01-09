<?php

namespace App\Http\Requests\Insurance\Osago;

use Illuminate\Foundation\Http\FormRequest;

class OsagoApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Faqat kerakli maydonlarni decode qilamiz
        if ($this->has('other_info') && is_string($this->other_info)) {
            $this->merge(['other_info' => json_decode($this->other_info, true)]);
        }
        if ($this->has('insurance_infos') && is_string($this->insurance_infos)) {
            $this->merge(['insurance_infos' => json_decode($this->insurance_infos, true)]);
        }

        // Driver info decoding - driver_full_info array'ni decode qilamiz
        if ($this->has('driver_full_info') && is_array($this->driver_full_info)) {
            $decodedDrivers = [];
            foreach ($this->driver_full_info as $key => $driver) {
                if (is_string($driver)) {
                    // JSON string bo'lsa, decode qilamiz
                    $decoded = json_decode($driver, true);
                    $decodedDrivers[$key] = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                } else {
                    // Array bo'lsa, to'g'ridan-to'g'ri qo'shamiz
                    $decodedDrivers[$key] = $driver;
                }
            }
            $this->merge(['driver_full_info' => $decodedDrivers]);
        }
    }

    public function rules(): array
    {
        return [
            // Avtomobil ma'lumotlari (Sessionda yo'q, requestdan kelishi shart)
            'gov_number' => ['required', 'string', 'max:15'],
            'tech_passport_series' => ['required', 'string', 'max:5'],
            'tech_passport_number' => ['required', 'string', 'max:15'],
            'model' => ['required', 'string', 'max:100'],
            'car_year' => ['required', 'integer'],
            'engine_number' => ['required', 'string', 'max:50'],
            'other_info' => ['required', 'array', 'required_array_keys:techPassportIssueDate,typeId,bodyNumber'],

            // Ariza beruvchi (Faqat aloqa uchun ma'lumotlar)
            'applicant.phoneNumber' => ['required', 'regex:/^\+?[0-9]{9,15}$/'],
            'applicant.email' => ['nullable', 'email'],
            'is_applicant_owner' => ['required', 'string'], // "on" yoki "off"

            // Sug'urta shartlari
            'policy_start_date' => ['required', 'date'],
            'policy_end_date' => ['required', 'date', 'after:policy_start_date'],
            'insurance_infos.period' => ['required', 'integer'], // Muddat kerak (1 yil va h.k.)

            // Haydovchilar (Agar cheklangan bo'lsa)
            'driver_limit' => ['required', 'string', 'in:limited,unlimited'],
            'driver_full_info' => ['nullable', 'array', 'required_if:driver_limit,limited'],
        ];
    }
}
