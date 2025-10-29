<?php

declare(strict_types=1);

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

    /**
     * Build DTO from validated request payload
     */
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

    /**
     * Transform to Accident API payload
     */
    public function toApiFormat(): array
    {
        $sum = $this->insuranceAmount;
        $rate = (float) config('services.insurance.accident.rate', 0.3); // percent
        $premium = (int) round(($rate * $sum) / 100);

        $exchangeRate = (float) config('services.insurance.accident.exchange_rate', 12053.46);
        $currencyId = (int) config('services.insurance.accident.currency_id', 840);
        $foreignSum = round($sum / max($exchangeRate, 0.0001), 2);
        $foreignPremium = round($premium / max($exchangeRate, 0.0001), 2);

        $regionId = (int) ($this->applicantData['regionId'] ?? $this->insuredInfo['regionId'] ?? 21);
        $genderMap = fn(string $g) => $g === '1' ? 'm' : 'f';

        $insurant = [
            'person' => [
                'passportData' => [
                    'pinfl' => $this->applicantData['pinfl'] ?? '',
                    'seria' => $this->applicantData['seria'] ?? '',
                    'number' => $this->applicantData['number'] ?? '',
                ],
                'fullName' => [
                    'firstname' => $this->applicantData['firstName'] ?? '',
                    'lastname' => $this->applicantData['lastName'] ?? '',
                    'middlename' => $this->applicantData['middleName'] ?? '',
                ],
                'regionId' => $regionId,
                'gender' => $genderMap((string) ($this->applicantData['gender'] ?? '1')),
                'birthDate' => $this->applicantData['birthDate'] ?? '',
                'address' => $this->applicantData['address'] ?? '',
                'residentType' => 1,
                'countryId' => 210,
                'phone' => $this->phone,
            ],
        ];

        $personObject = [
            'passportData' => [
                'pinfl' => $this->insuredInfo['pinfl'] ?? '',
                'seria' => $this->insuredInfo['seria'] ?? '',
                'number' => $this->insuredInfo['number'] ?? '',
            ],
            'fullName' => [
                'firstname' => $this->insuredInfo['firstName'] ?? '',
                'lastname' => $this->insuredInfo['lastName'] ?? '',
                'middlename' => $this->insuredInfo['middleName'] ?? '',
            ],
            'regionId' => (int) ($this->insuredInfo['regionId'] ?? $regionId),
            'gender' => $genderMap((string) ($this->insuredInfo['gender'] ?? '1')),
            'birthDate' => $this->insuredInfo['birthDate'] ?? '',
            'address' => $this->insuredInfo['address'] ?? '',
            'residentType' => 1,
            'countryId' => 210,
            'phone' => $this->insuredInfo['phoneNumber'] ?? $this->phone,
        ];

        $policy = [
            'paymentConditionsId' => (int) config('services.insurance.accident.payment_conditions_id', 3),
            'startDate' => $this->paymentStartDate,
            'endDate' => $this->paymentEndDate,
            'insuranceForeignSum' => $foreignSum,
            'insuranceForeignPremium' => $foreignPremium,
            'insuranceSum' => (string) $sum,
            'insuranceRate' => $rate,
            'insurancePremium' => (string) $premium,
            'insuranceTermId' => (int) config('services.insurance.accident.insurance_term_id', 6),
            'ruleLink' => (string) config('services.insurance.accident.rule_link', 'https://kafil.uz'),
            'objects' => [
                [
                    'classes' => [(int) config('services.insurance.accident.class_id', 8)],
                    'risks' => (string) config('services.insurance.accident.risks', 'Jismoniy shaxslarni baxtsiz hodisalardan ehtiyot shart sug‘urtalash'),
                    'insuranceSum' => (string) $sum,
                    'insuranceRate' => $rate,
                    'insurancePremium' => (string) $premium,
                    'insuranceForeignSum' => $foreignSum,
                    'insuranceForeignPremium' => $foreignPremium,
                    'price' => (string) $premium,
                    'person' => $personObject,
                ],
            ],
        ];

        return [
            'number' => (string) (config('services.insurance.accident.number') ?? str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT)),
            'sum' => (string) $sum,
            'contractStartDate' => $this->paymentStartDate,
            'contractEndDate' => $this->paymentEndDate,
            'regionId' => $regionId,
            'areaTypeId' => (int) config('services.insurance.accident.area_type_id', 1),
            'agencyId' => (int) config('services.insurance.agency_id', 221),
            'comission' => (int) config('services.insurance.accident.commission', 0),
            'insuranceProductName' => (string) config('services.insurance.accident.product_name', 'Jismoniy shaxslarni baxtsiz hodisalardan ehtiyot shart sug‘urtalash'),
            'formOfInsuranceId' => (int) config('services.insurance.accident.form_of_insurance_id', 2),
            'insuranceTypeId' => (int) config('services.insurance.accident.insurance_type_id', 999),
            'contractLink' => (string) config('services.insurance.accident.contract_link', 'https://kafil.uz'),
            'foreignSum' => $foreignSum,
            'exchangeRate' => $exchangeRate,
            'currencyId' => $currencyId,
            'uprAccountingGroupId' => (int) config('services.insurance.accident.upr_accounting_group_id', 1),
            'insurant' => $insurant,
            'policies' => [$policy],
        ];
    }
}
