<?php

namespace App\DTOs;

final class AccidentApplicationData
{
    public function __construct(
        public array $applicantData,
        public ?array $insuredData,
        public array $insuredInfo,
        public string $phone,
        public ?string $insuredBirthday,
        public string $agreement,
        public int $insuranceAmount,
        public string $paymentStartDate,
        public string $paymentEndDate,
    ) {}

    public static function fromRequest(array $data): self
    {
        $applicantData = [
            'lastName' => strtoupper($data['applicant_last_name']),
            'firstName' => strtoupper($data['applicant_first_name']),
            'middleName' => strtoupper($data['applicant_middle_name'] ?? ''),
            'pinfl' => $data['applicant_pinfl'] ?? '',
            'seria' => strtolower($data['applicant_passport_series']),
            'number' => $data['applicant_passport_number'],
            'birthDate' => $data['applicant_birthDate'],
            'birthPlace' => strtoupper($data['applicant_birth_place'] ?? ''),
            'birthCountry' => strtoupper($data['applicant_birth_country'] ?? 'УЗБЕКИСТАН'),
            'gender' => $data['applicant_gender'] ?? '1',
            'regionId' => $data['applicant_region_id'] ?? null,
            'districtId' => $data['applicant_district_id'] ?? null,
            'address' => strtoupper($data['applicant_address']),
            'phoneNumber' => $data['applicant_phone_number'],
        ];

        $insuredInfo = [
            'lastName' => strtoupper($data['client_last_name']),
            'firstName' => strtoupper($data['client_first_name']),
            'middleName' => strtoupper($data['client_middle_name'] ?? ''),
            'pinfl' => $data['client_pinfl'] ?? '',
            'seria' => strtolower($data['client_passport_series']),
            'number' => $data['client_passport_number'],
            'birthDate' => $data['client_birthDate'],
            'birthPlace' => strtoupper($data['client_birth_place'] ?? ''),
            'birthCountry' => strtoupper($data['client_birth_country'] ?? 'УЗБЕКИСТАН'),
            'gender' => $data['client_gender'] ?? '1',
            'regionId' => $data['client_region_id'] ?? null,
            'districtId' => $data['client_district_id'] ?? null,
            'address' => strtoupper($data['client_address']),
            'phoneNumber' => $data['client_phone_number'],
        ];

        return new self(
            applicantData: $applicantData,
            insuredData: null, // Can be populated if needed
            insuredInfo: $insuredInfo,
            phone: $data['applicant_phone_number'],
            insuredBirthday: $data['client_birthDate'] ?? null,
            agreement: $data['agreement'] ?? 'on',
            insuranceAmount: (int) $data['insurance_amount'],
            paymentStartDate: $data['payment_start_date'],
            paymentEndDate: $data['payment_end_date'],
        );
    }

    public function toArray(): array
    {
        return [
            'contractData' => null,
            'contractResponseData' => null,
            'child_person_info' => [
                '_token' => csrf_token(),
                'applicantData' => $this->applicantData,
                'insuredData' => $this->insuredData,
                'insuredInfo' => $this->insuredInfo,
                'phone' => $this->phone,
                'insured_birthday' => $this->insuredBirthday,
            ],
            'agreement' => $this->agreement,
            'insurance_amount' => $this->insuranceAmount,
            'payment_start_date' => $this->paymentStartDate,
            'payment_end_date' => $this->paymentEndDate,
        ];
    }

    public function toApiFormat(): array
    {
        return [
            'contractData' => null,
            'contractResponseData' => null,
            'child_person_info' => [
                '_token' => csrf_token(),
                'applicantData' => $this->applicantData,
                'insuredData' => $this->insuredData,
                'insuredInfo' => $this->insuredInfo,
                'phone' => $this->phone,
                'insured_birthday' => $this->insuredBirthday,
            ],
            'agreement' => $this->agreement,
        ];
    }
}
