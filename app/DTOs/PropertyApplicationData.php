<?php

namespace App\DTOs;

final class PropertyApplicationData
{
    public function __construct(
        public array $apiData,
        public array $legacyPropertyData,
        public int $insuranceAmount,
        public string $paymentStartDate,
        public string $paymentEndDate,
        public string $agreement,
    ) {}

    public static function fromRequest(array $data): self
    {
        // Calculate insurance premium (0.2% rate)
        $insuranceRate = 0.2;
        $insuranceAmount = (int) ($data['insurance_amount'] ?? 100000000);
        $insurancePremium = ($insuranceAmount * $insuranceRate) / 100;

        // Format applicant phone number
        $applicantPhoneNumber = preg_replace('/[^\d]/', '', $data['applicant']['phoneNumber'] ?? '');
        if (!empty($applicantPhoneNumber) && !str_starts_with($applicantPhoneNumber, '998')) {
            $applicantPhoneNumber = '998' . $applicantPhoneNumber;
        }

        // Format owner phone number
        $ownerPhoneNumber = preg_replace('/[^\d]/', '', $data['owner']['phoneNumber'] ?? '');
        if (!empty($ownerPhoneNumber) && !str_starts_with($ownerPhoneNumber, '998')) {
            $ownerPhoneNumber = '998' . $ownerPhoneNumber;
        }

        // Convert gender from form format (1,2) to API format (m,f)
        $applicantGender = ($data['applicant']['gender'] ?? '1') === '2' ? 'f' : 'm';
        $ownerGender = ($data['owner']['gender'] ?? '1') === '2' ? 'f' : 'm';

        // Build API data structure that matches the required format
        $apiData = [
            "number" => "269116",
            "sum" => $insuranceAmount,
            "contractStartDate" => $data['payment_start_date'],
            "contractEndDate" => $data['payment_end_date'],
            "regionId" => (int) ($data['applicant']['regionId'] ?? 18),
            "areaTypeId" => 1,
            "agencyId" => 28,
            "comission" => 0,
            "insuranceProductName" => "МОЛ-МУЛКНИ СУҒУРТА ҚИЛИШ",
            "formOfInsuranceId" => 2,
            "insuranceTypeId" => 999,
            "contractLink" => "https://impexonline.uz/trules/18-molmulk.pdf",
            "exchangeRate" => 12827.9,
            "uprAccountingGroupId" => 1,
            "insurant" => [
                "person" => [
                    "passportData" => [
                        "pinfl" => $data['applicant']['pinfl'] ?? '',
                        "seria" => $data['applicant']['passportSeries'] ?? '',
                        "number" => $data['applicant']['passportNumber'] ?? '',
                    ],
                    "fullName" => [
                        "firstname" => $data['applicant']['firstName'] ?? '',
                        "lastname" => $data['applicant']['lastName'] ?? '',
                        "middlename" => $data['applicant']['middleName'] ?? '',
                    ],
                    "regionId" => (int) ($data['applicant']['regionId'] ?? 18),
                    "gender" => $applicantGender,
                    "birthDate" => $data['applicant']['birthDate'] ?? '',
                    "address" => $data['applicant']['address'] ?? "Ko'rsatilmagan",
                    "residentType" => 1,
                    "countryId" => 210,
                    "phone" => $applicantPhoneNumber ?: '998910739373',
                ]
            ],
            "policies" => [
                [
                    "paymentConditionsId" => 3,
                    "startDate" => $data['payment_start_date'],
                    "endDate" => $data['payment_end_date'],
                    "insuranceSum" => $insuranceAmount,
                    "insuranceRate" => $insuranceRate,
                    "insurancePremium" => $insurancePremium,
                    "insuranceTermId" => 6,
                    "ruleLink" => "https://impexonline.uz/trules/18-molmulk.pdf",
                    "objects" => [
                        [
                            "classes" => [15, 16],
                            "risks" => "МОЛ-МУЛКНИ СУҒУРТА ҚИЛИШ",
                            "insuranceSum" => $insuranceAmount,
                            "insuranceRate" => $insuranceRate,
                            "insurancePremium" => $insurancePremium,
                            "price" => $insuranceAmount,
                            "others" => [
                                "ownerPerson" => [
                                    "fullName" => [
                                        "firstname" => $data['owner']['firstName'] ?? '',
                                        "lastname" => $data['owner']['lastName'] ?? '',
                                        "middlename" => $data['owner']['middleName'] ?? '',
                                    ],
                                    "passportData" => [
                                        "pinfl" => $data['owner']['pinfl'] ?? '',
                                        "seria" => $data['owner']['passportSeries'] ?? '',
                                        "number" => $data['owner']['passportNumber'] ?? '',
                                    ],
                                    "regionId" => (int) ($data['owner']['regionId'] ?? 18),
                                    "gender" => $ownerGender,
                                    "birthDate" => $data['owner']['birthDate'] ?? '',
                                    "address" => $data['owner']['address'] ?? "Ko'rsatilmagan",
                                    "residentType" => 1,
                                    "countryId" => 210,
                                    "phone" => $ownerPhoneNumber ?: $applicantPhoneNumber ?: '998910739373',
                                ],
                                "isForeign" => false,
                                "otherObjectTypeId" => 1,
                                "name" => $data['property']['tipText'] ?? '',
                                "measureTypeId" => 12,
                                "volume" => 1,
                                "address" => $data['property']['shortAddress'] ?? '',
                                "regionId" => (int) ($data['owner']['regionId'] ?? 18),
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Keep legacy property data for backward compatibility
        $legacyPropertyData = $data['property'] ?? [];

        return new self(
            apiData: $apiData,
            legacyPropertyData: $legacyPropertyData,
            insuranceAmount: $insuranceAmount,
            paymentStartDate: $data['payment_start_date'],
            paymentEndDate: $data['payment_end_date'],
            agreement: $data['agreement'] ?? 'on',
        );
    }

    public function toArray(): array
    {
        // Calculate insurance premium (0.2% rate)
        $insuranceRate = 0.2;
        $insurancePremium = ($this->insuranceAmount * $insuranceRate) / 100;

        return [
            'apiData' => $this->apiData,
            'legacyPropertyData' => $this->legacyPropertyData,
            'insuranceAmount' => $this->insuranceAmount,
            'insurancePremium' => $insurancePremium,
            'paymentStartDate' => $this->paymentStartDate,
            'paymentEndDate' => $this->paymentEndDate,
            'agreement' => $this->agreement,
            
            // Backward compatibility fields
            'applicant' => [
                'phoneNumber' => $this->apiData['insurant']['person']['phone'] ?? '',
                'firstName' => $this->apiData['insurant']['person']['fullName']['firstname'] ?? '',
                'lastName' => $this->apiData['insurant']['person']['fullName']['lastname'] ?? '',
            ],
        ];
    }

    public function toApiFormat(): array
    {
        // Return the pre-built API data structure
        return $this->apiData;
    }
}
