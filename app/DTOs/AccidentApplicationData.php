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
        $applicant = $data['applicant'];
        $client = $data['client'];

        $applicantData = [
            'lastName' => strtoupper($applicant['lastName']),
            'firstName' => strtoupper($applicant['firstName']),
            'middleName' => strtoupper($applicant['middleName'] ?? ''),
            'pinfl' => $applicant['pinfl'] ?? '',
            'seria' => strtolower($applicant['passportSeries']),
            'number' => $applicant['passportNumber'],
            'birthDate' => $applicant['birthDate'],
            'birthPlace' => strtoupper($applicant['birthPlace'] ?? ''),
            'birthCountry' => strtoupper($applicant['birthCountry'] ?? 'УЗБЕКИСТАН'),
            'gender' => $applicant['gender'] ?? '1',
            'regionId' => $applicant['regionId'] ?? null,
            'districtId' => $applicant['districtId'] ?? null,
            'address' => strtoupper($applicant['address']),
            'phoneNumber' => $applicant['phoneNumber'],
        ];

        $insuredInfo = [
            'lastName' => strtoupper($client['lastName']),
            'firstName' => strtoupper($client['firstName']),
            'middleName' => strtoupper($client['middleName'] ?? ''),
            'pinfl' => $client['pinfl'] ?? '',
            'seria' => strtolower($client['passportSeries']),
            'number' => $client['passportNumber'],
            'birthDate' => $client['birthDate'],
            'birthPlace' => strtoupper($client['birthPlace'] ?? ''),
            'birthCountry' => strtoupper($client['birthCountry'] ?? 'УЗБЕКИСТАН'),
            'gender' => $client['gender'] ?? '1',
            'regionId' => $client['regionId'] ?? null,
            'districtId' => $client['districtId'] ?? null,
            'address' => strtoupper($client['address']),
            'phoneNumber' => $client['phoneNumber'],
        ];

        return new self(
            applicantData: $applicantData,
            insuredData: null, // Can be populated if needed
            insuredInfo: $insuredInfo,
            phone: $applicant['phoneNumber'],
            insuredBirthday: $client['birthDate'] ?? null,
            agreement: $data['agreement'] ?? 'on',
            insuranceAmount: (int) $data['insurance_amount'],
            paymentStartDate: $data['payment_start_date'],
            paymentEndDate: $data['payment_end_date'],
        );
    }

    public function toArray(): array
    {
        return [
            'applicantData' => $this->applicantData,
            'insuredData' => $this->insuredData,
            'insuredInfo' => $this->insuredInfo,
            'phone' => $this->phone,
            'insuredBirthday' => $this->insuredBirthday,
            'agreement' => $this->agreement,
            'insuranceAmount' => $this->insuranceAmount,
            'paymentStartDate' => $this->paymentStartDate,
            'paymentEndDate' => $this->paymentEndDate,
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
