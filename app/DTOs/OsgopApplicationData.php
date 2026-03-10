<?php

declare(strict_types=1);

namespace App\DTOs;

use Carbon\Carbon;

final class OsgopApplicationData
{
    public function __construct(
        public array $insurant,
        public array $policies,
        public string $number,
        public string $sum,
        public string $contractStartDate,
        public string $contractEndDate,
        public string $regionId,
        public string $areaTypeId,
        public string $agencyId,
        public string $comission,
        public float $insurancePremium,
    ) {}

    public static function fromRequest(array $data): self
    {
        // Default dates: today and today+1 year (like OSGOR)
        $contractStartDate = $data['contract_start_date'] ?? Carbon::today()->format('Y-m-d');
        $contractEndDate = $data['contract_end_date'] ?? Carbon::today()->addYear()->format('Y-m-d');
        $startDate = $data['start_date'] ?? $contractStartDate;
        $endDate = $data['end_date'] ?? $contractEndDate;

        // Determine if insurant is person or organization
        $insurant = [];
        if (isset($data['insurant_type']) && $data['insurant_type'] === 'person') {
            // Person (Jismoniy shaxs)
            $insurant = [
                'person' => [
                    'passportData' => [
                        'pinfl' => $data['person']['pinfl'] ?? '',
                        'seria' => $data['person']['passport_seria'] ?? '',
                        'number' => $data['person']['passport_number'] ?? '',
                    ],
                    'fullName' => [
                        'firstname' => $data['person']['firstname'] ?? '',
                        'lastname' => $data['person']['lastname'] ?? '',
                        'middlename' => $data['person']['middlename'] ?? '',
                    ],
                    'regionId' => $data['person']['region_id'] ?? $data['region_id'] ?? '',
                    'driverLicenseSeria' => $data['person']['driver_license_seria'] ?? '',
                    'driverLicenseNumber' => (int) ($data['person']['driver_license_number'] ?? 0),
                    'gender' => $data['person']['gender'] ?? 'm',
                    'birthDate' => $data['person']['birth_date'] ?? '',
                    'address' => $data['person']['address'] ?? '',
                    'residentType' => (int) ($data['person']['resident_type'] ?? 1),
                    'countryId' => $data['person']['country_id'] ?? '210',
                    'phone' => $data['person']['phone'] ?? '',
                ],
            ];
        } else {
            // Organization (Yuridik shaxs) — per osgor_ozgop.MD (252-261), session company info for fallbacks
            $organization = $data['organization'] ?? [];
            $companyInfo = session('osgop_company_info') ?? [];
            $companyData = $companyInfo['result'] ?? $companyInfo;
            // OKED: API expects code only (e.g. "49310"); form may send "49310 Title"
            $okedRaw = $organization['oked'] ?? '';
            $oked = trim((string) (explode(' ', $okedRaw)[0] ?? $okedRaw)) ?: $okedRaw;

            $insurant = [
                'organization' => [
                    'inn' => $organization['inn'] ?? '',
                    'name' => $organization['name'] ?? '',
                    'representativeName' => $organization['representative_name']
                        ?? $companyData['representativeName']
                        ?? $companyData['representative_name']
                        ?? $organization['name']
                        ?? '',
                    'address' => $organization['address'] ?? '',
                    'oked' => $oked,
                    'position' => $organization['position']
                        ?? $companyData['position']
                        ?? '',
                    'phone' => $organization['phone'] ?? '',
                    'regionId' => $organization['region_id'] ?? $data['region_id'] ?? '',
                    'ownershipFormId' => $organization['ownership_form_id'] ?? '130',
                ],
            ];
        }

        // Calculate insurance premium if not provided
        $insuranceSum = (float) ($data['insurance_sum'] ?? $data['sum'] ?? 0);
        $insuranceRate = (float) ($data['insurance_rate'] ?? 0);
        $insurancePremium = $data['insurance_premium'] ?? null;
        
        if (!$insurancePremium && $insuranceRate > 0) {
            $insurancePremium = round($insuranceSum * ($insuranceRate / 100), 2);
        } elseif (!$insurancePremium) {
            // Default calculation if rate not provided
            $insurancePremium = round($insuranceSum * 0.01510, 2); // Default rate from example
        }

        // Build vehicle object (API: isForeign boolean; form may send "0"/"1")
        $isForeign = $data['vehicle']['is_foreign'] ?? false;
        $vehicle = [
            'isForeign' => $isForeign === true || $isForeign === '1' || $isForeign === 1,
            'techPassport' => [
                'number' => $data['vehicle']['tech_passport_number'] ?? '',
                'seria' => $data['vehicle']['tech_passport_seria'] ?? '',
            ],
            'govNumber' => $data['vehicle']['gov_number'] ?? '',
            'regionId' => $data['vehicle']['region_id'] ?? $data['region_id'] ?? '',
            'modelCustomName' => $data['vehicle']['model_custom_name'] ?? '',
            'vehicleTypeId' => (string) ($data['vehicle']['vehicle_type_id'] ?? '2'),
            'issueYear' => (string) ($data['vehicle']['issue_year'] ?? ''),
            'bodyNumber' => $data['vehicle']['body_number'] ?? '',
            'numberOfSeats' => (string) ($data['vehicle']['number_of_seats'] ?? '5'),
            'engineNumber' => $data['vehicle']['engine_number'] ?? '',
        ];

        // Add license if provided (API expects YYYY-MM-DD for dates)
        if (isset($data['vehicle']['license'])) {
            $lic = $data['vehicle']['license'];
            $vehicle['license'] = [
                'seria' => $lic['seria'] ?? '',
                'number' => $lic['number'] ?? '',
                'beginDate' => $lic['begin_date'] ? Carbon::parse($lic['begin_date'])->format('Y-m-d') : '',
                'endDate' => $lic['end_date'] ? Carbon::parse($lic['end_date'])->format('Y-m-d') : '',
                'typeCode' => $lic['type_code'] ?? '',
            ];
        }

        // Add owner (person or organization)
        if (isset($data['insurant_type']) && $data['insurant_type'] === 'person') {
            $vehicle['ownerPerson'] = $insurant['person'];
        } else {
            $vehicle['ownerOrganization'] = $insurant['organization'];
        }

        // Build policies per osgor_ozgop.MD (280-287): strings for sum/rate/premium, int for damage sums
        $healthLifeDamageSum = (int) ($data['health_life_damage_sum'] ?? 40000000);
        $propertyDamageSum = (int) ($data['property_damage_sum'] ?? 4000000);
        $insuranceRateStr = (string) ($data['insurance_rate'] ?? '0.01510');

        $policies = [
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'insuranceSum' => number_format($insuranceSum, 0, '.', ''),
                'insuranceRate' => $insuranceRateStr,
                'insurancePremium' => number_format((float) $insurancePremium, 0, '.', ''),
                'insuranceTermId' => (int) ($data['insurance_term_id'] ?? 4),
                'healthLifeDamageSum' => $healthLifeDamageSum,
                'propertyDamageSum' => $propertyDamageSum,
                'objects' => [
                    [
                        'vehicle' => $vehicle,
                    ],
                ],
            ],
        ];

        $regionId = $data['region_id'] ?? ($insurant['organization']['regionId'] ?? $insurant['person']['regionId'] ?? '');

        return new self(
            insurant: $insurant,
            policies: $policies,
            number: $data['number'] ?? '',
            sum: number_format($insuranceSum, 0, '.', ''),
            contractStartDate: $contractStartDate,
            contractEndDate: $contractEndDate,
            regionId: $regionId,
            areaTypeId: $data['area_type_id'] ?? '1',
            agencyId: $data['agency_id'] ?? '546',
            comission: $data['comission'] ?? '0',
            insurancePremium: (float) $insurancePremium,
        );
    }

    public function toArray(): array
    {
        return [
            'insurant' => $this->insurant,
            'policies' => $this->policies,
            'number' => $this->number,
            'sum' => $this->sum,
            'contractStartDate' => $this->contractStartDate,
            'contractEndDate' => $this->contractEndDate,
            'regionId' => $this->regionId,
            'areaTypeId' => $this->areaTypeId,
            'agencyId' => $this->agencyId,
            'comission' => $this->comission,
            'insurancePremium' => $this->insurancePremium,
        ];
    }

    public function toApiFormat(): array
    {
        return [
            'number' => $this->number,
            'sum' => $this->sum,
            'contractStartDate' => $this->contractStartDate,
            'contractEndDate' => $this->contractEndDate,
            'regionId' => $this->regionId,
            'areaTypeId' => $this->areaTypeId,
            'agencyId' => $this->agencyId,
            'comission' => $this->comission,
            'insurant' => $this->insurant,
            'policies' => $this->policies,
        ];
    }
}
