<?php

namespace App\Http\Requests\Insurance\Osago;

use Illuminate\Foundation\Http\FormRequest;

class InsurantInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Decode JSON before validation
        if ($this->has('other_info') && is_string($this->other_info)) {
            $this->merge([
                'other_info' => json_decode($this->other_info, true),
            ]);
        }

        if ($this->has('owner_infos') && is_string($this->owner_infos)) {
            $this->merge([
                'owner_infos' => json_decode($this->owner_infos, true),
            ]);
        }

        if ($this->has('applicant_infos') && is_string($this->applicant_infos)) {
            $this->merge([
                'applicant_infos' => json_decode($this->applicant_infos, true),
            ]);
        }

        if ($this->has('insurance_infos') && is_string($this->insurance_infos)) {
            $this->merge([
                'insurance_infos' => json_decode($this->insurance_infos, true),
            ]);
        }

        if ($this->has('driver_full_info') && is_string($this->insurance_infos)) {
            $this->merge([
                'insurance_infos' => json_decode($this->insurance_infos, true),
            ]);
        }

        if ($this->has('driver_full_info') && is_array($this->driver_full_info)) {
            $decodedDrivers = [];

            foreach ($this->driver_full_info as $driver) {
                if (is_string($driver)) {
                    $decoded = json_decode($driver, true);
                    $decodedDrivers[] = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                } elseif (is_array($driver)) {
                    $decodedDrivers[] = $driver;
                }
            }

            $this->merge(['driver_full_info' => $decodedDrivers]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // --- Vehicle information ---
            'gov_number' => ['required', 'string', 'max:15'],
            'tech_passport_series' => ['required', 'string', 'max:5'],
            'tech_passport_number' => ['required', 'string', 'max:15'],
            'model' => ['required', 'string', 'max:100'],
            'car_year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'registration_region' => ['required', 'string', 'max:255'],
            'other_info' => ['required', 'array', 'max:1000', 'required_array_keys:techPassportIssueDate,typeId,bodyNumber'],
            'engine_number' => ['required', 'string', 'max:50'],

            // --- Owner info ---
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['required', 'string', 'max:100'],
            'pinfl' => ['required', 'digits:14'],
            'passport_series' => ['required', 'string', 'max:2', 'min:2'],
            'passport_number' => ['required', 'string', 'max:7', 'min:7'],
            'owner_infos' => ['required', 'array', 'max:1000', 'required_array_keys:regionId,districtId,issuedBy,issueDate,gender,birthDate,address'],

            // --- Applicant info ---
            'applicant_first_name' => ['nullable', 'string', 'max:100'],
            'applicant_last_name' => ['nullable', 'string', 'max:100'],
            'applicant_middle_name' => ['nullable', 'string', 'max:100'],
            'applicant_phone' => ['nullable', 'max:12', 'min:12', 'regex:/^\+?[0-9]{9,15}$/'],
            'applicant_passport_series' => ['nullable', 'string', 'max:2', 'min:2'],
            'applicant_passport_number' => ['nullable', 'string', 'max:7', 'min:7'],
            'apllicant_infos' => ['nullable', 'array', 'max:1000', 'required_array_keys:regionId,districtId,issuedBy,issueDate,gender,birthDate,address'],
            // --- Insurance details ---
            'policy_start_date' => ['required', 'date'],
            'policy_end_date' => ['required', 'date', 'after:insurance_start_date'],
            'insurance_infos' => ['required', 'array', 'required_array_keys:amount,period,insuranceAmount'],

            // --- Driver info arrays ---
            'driver_limit' => ['required', 'string', 'in:limited,unlimited'],
            'driver_full_name' => ['nullable', 'array', 'min:1'],
            'driver_full_info' => ['nullable', 'array', 'min:1'],
            'driver_full_info.*' => ['nullable', 'array', 'min:1', 'required_array_keys:pinfl,seria,number,issuedBy,issueDate,firstname,lastname,middlename,licenseNumber,licenseSeria,birthDate,birthPlace,licenseIssueDate'],
            'kinship' => ['nullable', 'array', 'min:1'],
            'kinship.*' => ['required', 'min:1', 'in:0,1,2,3,4,5,6,7,8,9,10'],
        ];
    }

    public function messages(): array
    {
        return [
            // === General rules ===
            'required' => __('validation.required'),
            'string' => __('validation.string'),
            'integer' => __('validation.integer'),
            'numeric' => __('validation.numeric'),
            'array' => __('validation.array'),
            'date' => __('validation.date'),
            'json' => __('validation.json'),
            'max' => __('validation.max.string'),
            'min' => __('validation.min.numeric'),
            'regex' => __('validation.regex'),
            'in' => __('validation.in'),
            'after' => __('validation.after'),
            'digits' => __('validation.digits'),

            // === Vehicle ===
            'gov_number.required' => __('messages.gov_number_required'),
            'gov_number.max' => __('messages.gov_number_max'),
            'tech_passport_series.required' => __('messages.tech_passport_series_required'),
            'tech_passport_series.max' => __('messages.tech_passport_series_max'),
            'tech_passport_number.required' => __('messages.tech_passport_number_required'),
            'tech_passport_number.max' => __('messages.tech_passport_number_max'),
            'model.required' => __('messages.model_required'),
            'model.max' => __('messages.model_max'),
            'car_year.required' => __('messages.car_year_required'),
            'car_year.integer' => __('messages.car_year_integer'),
            'car_year.min' => __('messages.car_year_min'),
            'car_year.max' => __('messages.car_year_max'),
            'registration_region.required' => __('messages.registration_region_required'),
            'registration_region.max' => __('messages.registration_region_max'),
            'other_info.required' => __('messages.other_info_required'),
            'other_info.array' => __('messages.other_info_array'),
            'other_info.required_array_keys' => __('messages.other_info_required_keys'),
            'engine_number.required' => __('messages.engine_number_required'),
            'engine_number.max' => __('messages.engine_number_max'),

            // === Owner ===
            'first_name.required' => __('messages.owner_first_name_required'),
            'last_name.required' => __('messages.owner_last_name_required'),
            'middle_name.required' => __('messages.owner_middle_name_required'),
            'pinfl.required' => __('messages.owner_pinfl_required'),
            'pinfl.digits' => __('messages.owner_pinfl_digits'),
            'passport_series.required' => __('messages.owner_passport_series_required'),
            'passport_series.min' => __('messages.owner_passport_series_length'),
            'passport_series.max' => __('messages.owner_passport_series_length'),
            'passport_number.required' => __('messages.owner_passport_number_required'),
            'passport_number.min' => __('messages.owner_passport_number_length'),
            'passport_number.max' => __('messages.owner_passport_number_length'),
            'owner_infos.required' => __('messages.owner_infos_required'),
            'owner_infos.array' => __('messages.owner_infos_array'),
            'owner_infos.required_array_keys' => __('messages.owner_infos_required_keys'),

            // === Applicant ===
            'applicant_first_name.string' => __('messages.applicant_first_name_string'),
            'applicant_last_name.string' => __('messages.applicant_last_name_string'),
            'applicant_middle_name.string' => __('messages.applicant_middle_name_string'),
            'applicant_phone.min' => __('messages.applicant_phone_length'),
            'applicant_phone.max' => __('messages.applicant_phone_length'),
            'applicant_phone.regex' => __('messages.applicant_phone_regex'),
            'applicant_passport_series.min' => __('messages.applicant_passport_series_length'),
            'applicant_passport_series.max' => __('messages.applicant_passport_series_length'),
            'applicant_passport_number.min' => __('messages.applicant_passport_number_length'),
            'applicant_passport_number.max' => __('messages.applicant_passport_number_length'),
            'apllicant_infos.array' => __('messages.applicant_infos_array'),
            'apllicant_infos.required_array_keys' => __('messages.applicant_infos_required_keys'),

            // === Insurance ===
            'policy_start_date.required' => __('messages.policy_start_date_required'),
            'policy_start_date.date' => __('messages.policy_start_date_date'),
            'policy_end_date.required' => __('messages.policy_end_date_required'),
            'policy_end_date.date' => __('messages.policy_end_date_date'),
            'policy_end_date.after' => __('messages.policy_end_date_after'),
            'insurance_infos.required' => __('messages.insurance_infos_required'),
            'insurance_infos.array' => __('messages.insurance_infos_array'),
            'insurance_infos.required_array_keys' => __('messages.insurance_infos_required_keys'),

            // === Driver ===
            'driver_limit.required' => __('messages.driver_limit_required'),
            'driver_limit.in' => __('messages.driver_limit_in'),
            'driver_full_name.array' => __('messages.driver_full_name_array'),
            'driver_full_info.array' => __('messages.driver_full_info_array'),
            'driver_full_info.required_array_keys' => __('messages.driver_full_info_required_keys'),
            'kinship.array' => __('messages.kinship_array'),
            'kinship.*.in' => __('messages.kinship_in'),

            'driver_full_info.*.json' => __('messages.invalid_driver_json'),
        ];
    }
}
