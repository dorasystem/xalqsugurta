<?php

namespace App\DTOs;

final class PropertyApplicationData
{
    public function __construct(
        public string $cadasterNumber,
        public array $propertyData,
        public array $ownerData,
        public array $applicantData,
        public int $insuranceAmount,
        public string $paymentStartDate,
        public string $paymentEndDate,
        public string $agreement,
    ) {}

    public static function fromRequest(array $data): self
    {
        $property = $data['property'];
        $owner = $data['owner'];
        $applicant = $data['applicant'];

        $propertyData = [
            'cadasterNumber' => $property['cadasterNumber'],
            'shortAddress' => $property['shortAddress'],
            'street' => $property['street'] ?? '',
            'tip' => $property['tip'],
            'vid' => $property['vid'],
            'tipText' => $property['tipText'],
            'vidText' => $property['vidText'],
            'objectArea' => $property['objectArea'],
            'objectAreaL' => $property['objectAreaL'] ?? '0',
            'objectAreaU' => $property['objectAreaU'] ?? '0',
            'regionId' => $property['regionId'],
            'region' => $property['region'],
            'districtId' => $property['districtId'],
            'district' => $property['district'],
            'address' => $property['address'],
            'domNum' => $property['domNum'] ?? '',
            'kvartiraNum' => $property['kvartiraNum'] ?? '',
            'neighborhood' => $property['neighborhood'] ?? '',
            'neighborhoodId' => $property['neighborhoodId'] ?? '',
            'cost' => $property['cost'],
        ];

        $ownerData = [
            'type' => $owner['type'] ?? '1',
            'name' => strtoupper(trim($owner['lastName'] . ' ' . $owner['firstName'] . ' ' . ($owner['middleName'] ?? ''))),
            'lastName' => strtoupper($owner['lastName']),
            'firstName' => strtoupper($owner['firstName']),
            'middleName' => strtoupper($owner['middleName'] ?? ''),
            'passport' => strtolower($owner['passportSeries']) . ' ' . $owner['passportNumber'],
            'inn' => $owner['inn'] ?? '',
            'pinfl' => $owner['pinfl'] ?? '',
            'percent' => $owner['percent'] ?? '',
            'address' => strtoupper($owner['address']),
            'phoneNumber' => $owner['phoneNumber'],
            'passportSeries' => strtolower($owner['passportSeries']),
            'passportNumber' => $owner['passportNumber'],
            'birthDate' => $owner['birthDate'],
            'gender' => $owner['gender'] ?? '1',
        ];

        $applicantData = [
            'lastName' => strtoupper($applicant['lastName']),
            'firstName' => strtoupper($applicant['firstName']),
            'middleName' => strtoupper($applicant['middleName'] ?? ''),
            'pinfl' => $applicant['pinfl'] ?? '',
            'seria' => strtolower($applicant['passportSeries']),
            'number' => $applicant['passportNumber'],
            'birthDate' => $applicant['birthDate'],
            'address' => strtoupper($applicant['address']),
            'phoneNumber' => $applicant['phoneNumber'],
            'inn' => $applicant['inn'] ?? '',
            'gender' => $applicant['gender'] ?? '1',
        ];

        return new self(
            cadasterNumber: $property['cadasterNumber'],
            propertyData: $propertyData,
            ownerData: $ownerData,
            applicantData: $applicantData,
            insuranceAmount: (int) $data['insurance_amount'],
            paymentStartDate: $data['payment_start_date'],
            paymentEndDate: $data['payment_end_date'],
            agreement: $data['agreement'] ?? 'on',
        );
    }

    public function toArray(): array
    {
        return [
            'cadasterNumber' => $this->cadasterNumber,
            'propertyData' => $this->propertyData,
            'ownerData' => $this->ownerData,
            'applicantData' => $this->applicantData,
            'insuranceAmount' => $this->insuranceAmount,
            'paymentStartDate' => $this->paymentStartDate,
            'paymentEndDate' => $this->paymentEndDate,
            'agreement' => $this->agreement,
        ];
    }

    public function toApiFormat(): array
    {
        $insuranceStartDate = $this->paymentStartDate;
        $insuranceEndDate = $this->paymentEndDate;

        // Calculate insurance premium (0.2% rate)
        $insuranceRate = 0.2;
        $insurancePremium = ($this->insuranceAmount * $insuranceRate) / 100;

        // Format phone number
        $phoneNumber = preg_replace('/[^\d]/', '', $this->applicantData['phoneNumber']);
        if (!empty($phoneNumber) && !str_starts_with($phoneNumber, '998')) {
            $phoneNumber = '998' . $phoneNumber;
        }

        return [
            "number" => "269116",
            "sum" => $this->insuranceAmount,
            "contractStartDate" => $insuranceStartDate,
            "contractEndDate" => $insuranceEndDate,
            "regionId" => !empty($this->propertyData['regionId']) ? (int)$this->propertyData['regionId'] : 18,
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
                        "pinfl" => $this->applicantData['pinfl'] ?? '',
                        "seria" => $this->applicantData['seria'] ?? '',
                        "number" => $this->applicantData['number'] ?? '',
                    ],
                    "fullName" => [
                        "firstname" => $this->applicantData['firstName'] ?? '',
                        "lastname" => $this->applicantData['lastName'] ?? '',
                        "middlename" => $this->applicantData['middleName'] ?? '',
                    ],
                    "regionId" => !empty($this->propertyData['regionId']) ? (int)$this->propertyData['regionId'] : 18,
                    "gender" => ($this->applicantData['gender'] ?? "1") == "1" ? "m" : "f",
                    "birthDate" => $this->applicantData['birthDate'] ?? '',
                    "address" => !empty($this->applicantData['address']) ? $this->applicantData['address'] : "Ko'rsatilmagan",
                    "residentType" => 1,
                    "countryId" => 210,
                    "phone" => $phoneNumber ?: '998910739373',
                ]
            ],
            "policies" => [
                [
                    "paymentConditionsId" => 3,
                    "startDate" => $insuranceStartDate,
                    "endDate" => $insuranceEndDate,
                    "insuranceSum" => $this->insuranceAmount,
                    "insuranceRate" => $insuranceRate,
                    "insurancePremium" => $insurancePremium,
                    "insuranceTermId" => 6,
                    "ruleLink" => "https://impexonline.uz/trules/18-molmulk.pdf",
                    "objects" => [
                        [
                            "classes" => [15, 16],
                            "risks" => "МОЛ-МУЛКНИ СУҒУРТА ҚИЛИШ",
                            "insuranceSum" => $this->insuranceAmount,
                            "insuranceRate" => $insuranceRate,
                            "insurancePremium" => $insurancePremium,
                            "price" => $this->insuranceAmount,
                            "others" => [
                                "ownerPerson" => [
                                    "fullName" => [
                                        "firstname" => $this->ownerData['firstName'] ?? '',
                                        "lastname" => $this->ownerData['lastName'] ?? '',
                                        "middlename" => $this->ownerData['middleName'] ?? '',
                                    ],
                                    "passportData" => [
                                        "pinfl" => $this->ownerData['pinfl'] ?? '',
                                        "seria" => $this->ownerData['passportSeries'] ?? '',
                                        "number" => $this->ownerData['passportNumber'] ?? '',
                                    ],
                                    "regionId" => !empty($this->propertyData['regionId']) ? (int)$this->propertyData['regionId'] : 18,
                                    "gender" => ($this->ownerData['gender'] ?? "1") == "1" ? "m" : "f",
                                    "birthDate" => $this->ownerData['birthDate'] ?? '',
                                    "address" => !empty($this->ownerData['address']) ? $this->ownerData['address'] : "Ko'rsatilmagan",
                                    "residentType" => 1,
                                    "countryId" => 210,
                                    "phone" => $phoneNumber ?: '998910739373',
                                ],
                                "isForeign" => false,
                                "otherObjectTypeId" => 1,
                                "name" => trim(($this->ownerData['firstName'] ?? '') . " " . ($this->ownerData['lastName'] ?? '')),
                                "measureTypeId" => 12,
                                "volume" => 1,
                                "address" => $this->propertyData['address'] ?? '',
                                "regionId" => !empty($this->propertyData['regionId']) ? (int)$this->propertyData['regionId'] : 18,
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
