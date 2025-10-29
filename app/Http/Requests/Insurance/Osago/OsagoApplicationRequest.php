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
        // Decode nested JSON payloads if provided as strings
        if ($this->has('owner.infos') && is_string($this->input('owner.infos'))) {
            $this->merge(['owner' => array_merge($this->input('owner', []), [
                'infos' => json_decode($this->input('owner.infos'), true),
            ])]);
        }

        if ($this->has('applicant.infos') && is_string($this->input('applicant.infos'))) {
            $this->merge(['applicant' => array_merge($this->input('applicant', []), [
                'infos' => json_decode($this->input('applicant.infos'), true),
            ])]);
        }

        if ($this->has('other_info') && is_string($this->other_info)) {
            $this->merge(['other_info' => json_decode($this->other_info, true)]);
        }

        if ($this->has('insurance_infos') && is_string($this->insurance_infos)) {
            $this->merge(['insurance_infos' => json_decode($this->insurance_infos, true)]);
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

    public function rules(): array
    {
        return [
            // Vehicle basics
            'gov_number' => ['required', 'string', 'max:15'],
            'tech_passport_series' => ['required', 'string', 'max:5'],
            'tech_passport_number' => ['required', 'string', 'max:15'],
            'model' => ['required', 'string', 'max:100'],
            'car_year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'registration_region' => ['required', 'string', 'max:255'],
            'engine_number' => ['required', 'string', 'max:50'],
            'other_info' => ['required', 'array', 'required_array_keys:techPassportIssueDate,typeId,bodyNumber'],

            // Owner (nested)
            'owner.passportSeries' => ['required', 'string', 'size:2'],
            'owner.passportNumber' => ['required', 'string', 'size:7'],
            'owner.pinfl' => ['required', 'digits:14'],
            'owner.lastName' => ['required', 'string', 'max:100'],
            'owner.firstName' => ['required', 'string', 'max:100'],
            'owner.middleName' => ['required', 'string', 'max:100'],
            'owner.infos' => ['required', 'array', 'required_array_keys:regionId,districtId,issuedBy,issueDate,gender,birthDate,address'],

            // Applicant (nested, optional if owner is applicant)
            'is_applicant_owner' => ['nullable'],
            'applicant.passportSeries' => ['nullable', 'string', 'size:2'],
            'applicant.passportNumber' => ['nullable', 'string', 'size:7'],
            'applicant.pinfl' => ['nullable', 'digits:14'],
            'applicant.lastName' => ['nullable', 'string', 'max:100'],
            'applicant.firstName' => ['nullable', 'string', 'max:100'],
            'applicant.middleName' => ['nullable', 'string', 'max:100'],
            'applicant.address' => ['nullable', 'string', 'max:500'],
            'applicant.phoneNumber' => ['nullable', 'regex:/^\+?[0-9]{9,15}$/'],
            'applicant.infos' => ['nullable', 'array', 'required_array_keys:regionId,districtId,issuedBy,issueDate,gender,birthDate,address'],

            // Insurance details
            'policy_start_date' => ['required', 'date'],
            'policy_end_date' => ['required', 'date', 'after:policy_start_date'],
            'insurance_infos' => ['required', 'array', 'required_array_keys:amount,period,insuranceAmount'],

            // Driver info
            'driver_limit' => ['required', 'string', 'in:limited,unlimited'],
            'driver_full_name' => ['nullable', 'array', 'min:1'],
            'driver_full_info' => ['nullable', 'array', 'min:1'],
            'driver_full_info.*' => ['nullable', 'array', 'min:1', 'required_array_keys:pinfl,seria,number,issuedBy,issueDate,firstname,lastname,middlename,licenseNumber,licenseSeria,birthDate,birthPlace,licenseIssueDate'],
            'kinship' => ['nullable', 'array', 'min:1'],
            'kinship.*' => ['required', 'in:0,1,2,3,4,5,6,7,8,9,10'],
        ];
    }
}
