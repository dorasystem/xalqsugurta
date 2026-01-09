<?php

declare(strict_types=1);

namespace App\DTOs;

use Carbon\Carbon;
use App\Services\OsagoPriceCalculator;
use Illuminate\Support\Facades\Log;

final class OsagoApplicationData
{
    public function __construct(
        public array $applicant,
        public array $owner,
        public array $details,
        public array $cost,
        public array $vehicle,
        public array $drivers,
    ) {}

    public static function fromRequest(array $data): self
    {
        // 1. Sessiondan ma'lumotlarni olish
        $personInfo = session('osago_person_info');
        if (!$personInfo) {
            Log::error('OSAGO: Session expired or osago_person_info missing');
            throw new \InvalidArgumentException('Sessiya muddati tugagan. Iltimos, ma’lumotlarni qaytadan kiriting.');
        }

        $isApplicantOwner = ($data['is_applicant_owner'] ?? '') === "on";

        // 2. Dokumentdan seriya va raqamni ajratish
        preg_match('/([A-Z]+)(\d+)/', $personInfo['currentDocument'], $parts);
        $activeDoc = collect($personInfo['documents'])->firstWhere('document', $personInfo['currentDocument']);

        // 3. Applicant build (Ariza beruvchi)
        $applicant = [
            'person' => [
                'passportData' => [
                    'number' => $parts[2] ?? '',
                    'seria' => $parts[1] ?? '',
                    'issuedBy' => $activeDoc['docgiveplace'] ?? '',
                    'issueDate' => $activeDoc['datebegin'] ?? '',
                    'pinfl' => $personInfo['currentPinfl']
                ],
                'phoneNumber' => ltrim($data['applicant']['phoneNumber'] ?? '', '+'), // Remove leading +
                'birthDate' => $personInfo['birthDate'],
                'fullName' => [
                    'firstname' => $personInfo['firstNameLatin'],
                    'lastname' => $personInfo['lastNameLatin'],
                    'middlename' => $personInfo['middleNameLatin'],
                ],
                'gender' => ($personInfo['gender'] == "1") ? "m" : "f",
                'districtId' => (int)$personInfo['districtId'],
                'regionId' => (int)$personInfo['regionId'],
            ],
            'organization' => ['phoneNumber' => "", 'inn' => "", 'name' => ""],
            'citizenshipId' => 1,
            'address' => $personInfo['address'],
            'email' => $data['applicant']['email'] ?? '',
            'residentOfUzb' => 1
        ];

        // 4. Owner build (Ega)
        $owner = [
            'organization' => ['inn' => null],
            'person' => [
                'passportData' => $applicant['person']['passportData'],
                'birthDate' => $applicant['person']['birthDate'],
                'fullName' => $applicant['person']['fullName']
            ],
            'applicantIsOwner' => $isApplicantOwner ? "true" : "false"
        ];

        // 5. Drivers build (Haydovchilar)
        // API format: passportData, fullName, licenseNumber, licenseSeria, licenseIssueDate, birthDate, residentOfUzb
        $drivers = [];
        if (($data['driver_limit'] ?? 'unlimited') === "limited" && isset($data['driver_full_info'])) {
            foreach ($data['driver_full_info'] as $key => $driver) {
                // Clean and format driver passport data: remove spaces, uppercase seria
                $driverSeria = strtoupper(str_replace(' ', '', trim($driver['seria'] ?? '')));
                $driverNumber = str_replace(' ', '', trim($driver['number'] ?? ''));
                $driverLicenseSeria = strtoupper(str_replace(' ', '', trim($driver['licenseSeria'] ?? '')));
                $driverLicenseNumber = str_replace(' ', '', trim($driver['licenseNumber'] ?? ''));

                $drivers[] = [
                    'passportData' => [
                        'pinfl' => str_replace(' ', '', trim($driver['pinfl'] ?? '')),
                        'seria' => $driverSeria,
                        'number' => $driverNumber,
                        'issuedBy' => $driver['issuedBy'] ?? '',
                        'issueDate' => $driver['issueDate'] ?? ''
                    ],
                    'fullName' => [
                        'firstname' => $driver['firstname'] ?? '',
                        'lastname' => $driver['lastname'] ?? '',
                        'middlename' => $driver['middlename'] ?? ''
                    ],
                    'licenseNumber' => $driverLicenseNumber,
                    'licenseSeria' => $driverLicenseSeria,
                    'licenseIssueDate' => $driver['licenseIssueDate'] ?? '',
                    'birthDate' => isset($driver['birthDate']) ? Carbon::parse($driver['birthDate'])->format('Y-m-d') : '',
                    'residentOfUzb' => $driver['residentOfUzb'] ?? 1
                ];
            }
        }

        // 6. Clean and format data
        // Clean gov_number: remove spaces, uppercase letters
        $govNumber = strtoupper(str_replace(' ', '', trim($data['gov_number'] ?? '')));
        // Clean and format tech passport: remove spaces, uppercase seria
        $techPassportSeries = strtoupper(str_replace(' ', '', trim($data['tech_passport_series'] ?? '')));
        $techPassportNumber = str_replace(' ', '', trim($data['tech_passport_number'] ?? ''));

        // 7. Narxni hisoblash
        $priceCalculator = new OsagoPriceCalculator();
        $calc = $priceCalculator->calculate(
            $govNumber,
            (int)($data['other_info']['typeId'] ?? 1),
            (int)($data['insurance_infos']['period'] ?? 1),
            $data['driver_limit'] ?? 'unlimited'
        );
        $insurancePremium = $calc['amount'] ?? 168000;

        // 8. Cost build (To'lov ma'lumotlari)
        $cost = [
            'discountId' => 1,
            'sumInsured' => 80000000,
            'contractTermConclusionId' => (int)($data['insurance_infos']['period'] ?? 1),
            'commission' => 0,
            'insurancePremium' => $insurancePremium,
            'discountSum' => 0,
            'useTerritoryId' => in_array(substr($govNumber, 0, 2), ['01', '10']) ? 1 : 2,
            'insurancePremiumPaidToInsurer' => $insurancePremium
        ];

        // 9. Vehicle build (Transport)

        $vehicle = [
            'govNumber' => $govNumber,
            'engineNumber' => str_replace(' ', '', trim($data['engine_number'] ?? '')),
            'issueYear' => (int)$data['car_year'],
            'modelCustomName' => $data['model'],
            'techPassport' => [
                'issueDate' => Carbon::parse($data['other_info']['techPassportIssueDate'])->format('Y-m-d'),
                'number' => $techPassportNumber,
                'seria' => $techPassportSeries,
            ],
            'regionId' => (int)$personInfo['regionId'],
            'bodyNumber' => $data['other_info']['bodyNumber'],
            'terrainId' => 2,
            'typeId' => (int)($data['other_info']['typeId'] ?? 1)
        ];

        // 9. Details build
        $details = [
            'specialNote' => "",
            'insuredActivityType' => "OSAGO",
            'issueDate' => Carbon::now()->format('Y-m-d'),
            'startDate' => Carbon::parse($data['policy_start_date'])->format('Y-m-d'),
            'endDate' => Carbon::parse($data['policy_end_date'])->format('Y-m-d'),
            'driverNumberRestriction' => ($data['driver_limit'] ?? 'unlimited') === "limited"
        ];

        return new self(
            applicant: $applicant,
            owner: $owner,
            details: $details,
            cost: $cost,
            vehicle: $vehicle,
            drivers: $drivers,
        );
    }

    /**
     * API uchun qat'iy formatlangan ma'lumotlarni qaytaradi.
     */
    public function toApiFormat(): array
    {
        return [
            'vehicle' => $this->vehicle,
            'owner' => $this->owner,
            'applicant' => $this->applicant,
            'details' => $this->details,
            'drivers' => $this->drivers,
            'cost' => $this->cost,
        ];
    }

    /**
     * DTO'ning barcha ichki o'zgaruvchilarini massiv ko'rinishida qaytaradi.
     */
    public function toArray(): array
    {
        return [
            'applicant' => $this->applicant,
            'owner' => $this->owner,
            'details' => $this->details,
            'cost' => $this->cost,
            'vehicle' => $this->vehicle,
            'drivers' => $this->drivers,
        ];
    }
}
