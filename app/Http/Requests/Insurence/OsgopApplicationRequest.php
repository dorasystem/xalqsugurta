<?php

namespace App\Http\Requests\Insurence;

use Illuminate\Foundation\Http\FormRequest;

final class OsgopApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('insurance_sum') && !$this->has('sum')) {
            $this->merge(['sum' => $this->input('insurance_sum')]);
        }

        // Clean phone number: remove spaces and keep only digits
        $phoneFields = ['organization.phone', 'person.phone'];
        
        foreach ($phoneFields as $field) {
            if ($this->has($field)) {
                $phoneNumber = $this->input($field);
                $phoneNumber = preg_replace('/[^\d]/', '', $phoneNumber);

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

                $parts = explode('.', $field);
                if (count($parts) === 2) {
                    $parent = $parts[0];
                    $child = $parts[1];
                    $parentData = $this->input($parent, []);
                    $parentData[$child] = $phoneNumber;
                    $this->merge([$parent => $parentData]);
                }
            }
        }
    }

    public function rules(): array
    {
        $insurantType = $this->input('insurant_type', 'organization');

        $rules = [
            // Insurant type
            'insurant_type' => ['required', 'in:person,organization'],

            // Contract
            'number' => ['nullable', 'string', 'max:100'],
            'sum' => ['required', 'numeric', 'min:100000'],
            'insurance_sum' => ['required', 'numeric', 'min:100000'],
            'contract_start_date' => ['required', 'date'],
            'contract_end_date' => ['required', 'date', 'after_or_equal:contract_start_date'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'period_months' => ['nullable', 'in:3,6,12'],
            'owner_is_applicant' => ['nullable'],
            'region_id' => ['required', 'string'],
            'area_type_id' => ['required', 'string'],
            'agency_id' => ['required', 'string'],
            'comission' => ['nullable', 'string', 'default:0'],

            // Policy
            'insurance_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'insurance_premium' => ['nullable', 'numeric', 'min:0'],
            'insurance_term_id' => ['nullable', 'integer'],
            'health_life_damage_sum' => ['nullable', 'integer'],
            'property_damage_sum' => ['nullable', 'integer'],

            // Vehicle
            'vehicle.tech_passport_seria' => ['required', 'string', 'max:10'],
            'vehicle.tech_passport_number' => ['required', 'string', 'max:20'],
            'vehicle.gov_number' => ['required', 'string', 'max:20'],
            'vehicle.region_id' => ['required', 'string'],
            'vehicle.model_custom_name' => ['required', 'string', 'max:255'],
            'vehicle.vehicle_type_id' => ['required', 'string'],
            'vehicle.issue_year' => ['required', 'string', 'max:4'],
            'vehicle.body_number' => ['nullable', 'string', 'max:100'],
            'vehicle.number_of_seats' => ['required', 'string'],
            'vehicle.engine_number' => ['nullable', 'string', 'max:100'],
            'vehicle.is_foreign' => ['nullable', 'in:0,1,true,false'],
        ];

        // Organization rules
        if ($insurantType === 'organization') {
            $rules = array_merge($rules, [
                'organization.inn' => ['required', 'string', 'min:9', 'max:9'],
                'organization.name' => ['required', 'string', 'max:255'],
                'organization.representative_name' => ['required', 'string', 'max:255'],
                'organization.address' => ['required', 'string', 'max:500'],
                'organization.oked' => ['required', 'string'],
                'organization.position' => ['required', 'string', 'max:255'],
                'organization.phone' => ['required', 'string', 'regex:/^998[0-9]{9}$/'],
                'organization.region_id' => ['required', 'string'],
                'organization.ownership_form_id' => ['nullable', 'string'],
            ]);
        }

        // Person rules
        if ($insurantType === 'person') {
            $rules = array_merge($rules, [
                'person.pinfl' => ['required', 'string', 'min:14', 'max:14'],
                'person.passport_seria' => ['required', 'string', 'max:10'],
                'person.passport_number' => ['required', 'string', 'max:20'],
                'person.firstname' => ['required', 'string', 'max:255'],
                'person.lastname' => ['required', 'string', 'max:255'],
                'person.middlename' => ['nullable', 'string', 'max:255'],
                'person.region_id' => ['required', 'string'],
                'person.driver_license_seria' => ['nullable', 'string', 'max:10'],
                'person.driver_license_number' => ['nullable', 'integer'],
                'person.gender' => ['required', 'in:m,f'],
                'person.birth_date' => ['required', 'date'],
                'person.address' => ['required', 'string', 'max:500'],
                'person.resident_type' => ['nullable', 'integer'],
                'person.country_id' => ['nullable', 'string'],
                'person.phone' => ['required', 'string', 'regex:/^998[0-9]{9}$/'],
            ]);
        }

        // License (optional)
        $rules = array_merge($rules, [
            'vehicle.license.seria' => ['nullable', 'string', 'max:10'],
            'vehicle.license.number' => ['nullable', 'string', 'max:20'],
            'vehicle.license.begin_date' => ['nullable', 'date'],
            'vehicle.license.end_date' => ['nullable', 'date'],
            'vehicle.license.type_code' => ['nullable', 'string', 'max:255'],
        ]);

        return $rules;
    }

    public function messages(): array
    {
        return [
            'organization.inn.required' => __('validation.required', ['attribute' => __('messages.inn')]),
            'organization.inn.min' => __('messages.inn_invalid'),
            'organization.inn.max' => __('messages.inn_invalid'),
            'organization.name.required' => __('validation.required', ['attribute' => __('messages.organization_name')]),
            'organization.representative_name.required' => __('validation.required', ['attribute' => __('messages.representative_name')]),
            'organization.position.required' => __('validation.required', ['attribute' => __('messages.position')]),
            'organization.phone.required' => __('validation.required', ['attribute' => __('messages.phone_number')]),
            'organization.phone.regex' => __('validation.regex', ['attribute' => __('messages.phone_number')]),
            'organization.region_id.required' => __('validation.required', ['attribute' => __('messages.region')]),
            'person.pinfl.required' => __('validation.required', ['attribute' => __('messages.pinfl')]),
            'person.phone.regex' => __('validation.regex', ['attribute' => __('messages.phone_number')]),
            'contract_end_date.after' => __('validation.after', ['attribute' => __('messages.contract_end_date'), 'date' => __('messages.contract_start_date')]),
            'end_date.after' => __('validation.after', ['attribute' => __('messages.policy_end_date'), 'date' => __('messages.policy_start_date')]),
        ];
    }
}
