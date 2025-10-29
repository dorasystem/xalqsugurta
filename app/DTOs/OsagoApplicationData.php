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
        public string $govNumber,
        public int $insurancePremium,
    ) {}

    public static function fromRequest(array $data): self
    {
        // Validate required top-level fields before transformation
        self::validateRequiredFields($data, [
            'gov_number',
            'tech_passport_series',
            'tech_passport_number',
            'model',
            'car_year',
            'other_info',
            'policy_start_date',
            'policy_end_date',
            'insurance_infos',
            'driver_limit',
            'owner',
        ]);
        self::validateRequiredFields($data['other_info'] ?? [], ['techPassportIssueDate', 'typeId', 'bodyNumber']);
        self::validateRequiredFields($data['owner'] ?? [], ['pinfl', 'passportSeries', 'passportNumber', 'firstName', 'lastName', 'middleName', 'infos']);
        self::validateRequiredFields($data['owner']['infos'] ?? [], ['issuedBy', 'issueDate', 'regionId', 'districtId', 'gender', 'birthDate']);

        $region = substr($data['gov_number'], 0, 2);
        $useTerritoryId = in_array($region, ['01', '10']) ? 1 : 2;

        // Determine address
        if (($data['is_applicant_owner'] ?? false) && isset($data['owner']['infos']['address'])) {
            $address = $data['owner']['infos']['address'];
        } elseif (!empty($data['applicant']['address'] ?? null)) {
            $address = $data['applicant']['address'];
        } else {
            $regions = [
                "01" => 'Toshkent shahri',
                "10" => 'Toshkent viloyati',
                "20" => 'Sirdaryo viloyati',
                "25" => 'Jizzax viloyati',
                "30" => 'Samarqand viloyati',
                "40" => 'Farg\'ona viloyati',
                "50" => 'Namangan viloyati',
                "60" => 'Andijon viloyati',
                "70" => 'Qashqadaryo viloyati',
                "75" => 'Surxondaryo viloyati',
                "80" => 'Buxoro viloyati',
                "85" => 'Navoiy viloyati',
                "90" => 'Xorazm viloyati',
                "95" => 'Qoraqalpog\'iston Respublikasi'
            ];
            $address = $regions[$region] ?? '';
            if ($address === '') {
                Log::warning('OSAGO address resolution failed');
                throw new \InvalidArgumentException('Required field missing: address');
            }
        }

        $isApplicantOwner = ($data['is_applicant_owner'] ?? false) == "on";

        // Process drivers
        $driverLimit = [];
        if (($data['driver_limit'] ?? 'unlimited') == "limited" && isset($data['driver_full_info'])) {
            foreach ($data['driver_full_info'] as $key => $driver) {
                // Validate driver structure
                self::validateRequiredFields($driver, [
                    'pinfl',
                    'seria',
                    'number',
                    'issuedBy',
                    'issueDate',
                    'firstname',
                    'lastname',
                    'middlename',
                    'licenseNumber',
                    'licenseSeria',
                    'birthDate',
                    'birthPlace',
                    'licenseIssueDate'
                ]);

                $relative = $data['kinship'][$key] ?? null;
                if ($relative === null) {
                    Log::error('OSAGO driver relative missing', ['driver_index' => $key]);
                    throw new \InvalidArgumentException("Required field missing: kinship[{$key}]");
                }

                $driverLimit[] = [
                    'passportData' => [
                        'pinfl' => $driver['pinfl'],
                        'seria' => $driver['seria'],
                        'number' => $driver['number'],
                        'issuedBy' => $driver['issuedBy'],
                        'issueDate' => $driver['issueDate']
                    ],
                    'fullName' => [
                        'firstname' => $driver['firstname'],
                        'lastname' => $driver['lastname'],
                        'middlename' => $driver['middlename']
                    ],
                    'licenseNumber' => $driver['licenseNumber'],
                    'licenseSeria' => $driver['licenseSeria'],
                    'relative' => (int) $relative,
                    'birthDate' => Carbon::parse($driver['birthDate'])->format('Y-m-d'),
                    'licenseIssueDate' => $driver['licenseIssueDate'],
                    'residentOfUzb' => 1
                ];
            }
        }

        // Build applicant data
        $applicant = [
            'person' => [
                'passportData' => [
                    'pinfl' => $isApplicantOwner ? ($data['owner']['pinfl'] ?? '') : ($data['applicant']['pinfl'] ?? ''),
                    'seria' => $isApplicantOwner ? ($data['owner']['passportSeries'] ?? '') : ($data['applicant']['passportSeries'] ?? ''),
                    'number' => $isApplicantOwner ? ($data['owner']['passportNumber'] ?? '') : ($data['applicant']['passportNumber'] ?? ''),
                    'issuedBy' => $isApplicantOwner ? ($data['owner']['infos']['issuedBy'] ?? '') : ($data['applicant']['infos']['issuedBy'] ?? ''),
                    'issueDate' => $isApplicantOwner ? ($data['owner']['infos']['issueDate'] ?? '') : ($data['applicant']['infos']['issueDate'] ?? '')
                ],
                'fullName' => [
                    'firstname' => $isApplicantOwner ? ($data['owner']['firstName'] ?? '') : ($data['applicant']['firstName'] ?? ''),
                    'lastname' => $isApplicantOwner ? ($data['owner']['lastName'] ?? '') : ($data['applicant']['lastName'] ?? ''),
                    'middlename' => $isApplicantOwner ? ($data['owner']['middleName'] ?? '') : ($data['applicant']['middleName'] ?? '')
                ],
                'phoneNumber' => $data['applicant']['phoneNumber'] ?? '998901234578',
                'gender' => $isApplicantOwner
                    ? ((($data['owner']['infos']['gender'] ?? '1') == "1") ? "m" : "f")
                    : ((($data['applicant']['infos']['gender'] ?? '1') == "1") ? "m" : "f"),
                'birthDate' => $isApplicantOwner ? ($data['owner']['infos']['birthDate'] ?? '') : ($data['applicant']['infos']['birthDate'] ?? ''),
                'regionId' => $isApplicantOwner ? ($data['owner']['infos']['regionId'] ?? null) : ($data['applicant']['infos']['regionId'] ?? null),
                'districtId' => $isApplicantOwner ? ($data['owner']['infos']['districtId'] ?? null) : ($data['applicant']['infos']['districtId'] ?? null),
            ],
            'address' => $address,
            'email' => 'example@example.com',
            'residentOfUzb' => 1,
            'citizenshipId' => 210
        ];

        // Build owner data
        $owner = [
            'person' => [
                'passportData' => [
                    'pinfl' => $data['owner']['pinfl'],
                    'seria' => $data['owner']['passportSeries'],
                    'number' => $data['owner']['passportNumber'],
                    'issuedBy' => $data['owner']['infos']['issuedBy'],
                    'issueDate' => $data['owner']['infos']['issueDate']
                ],
                'fullName' => [
                    'firstname' => $data['owner']['firstName'],
                    'lastname' => $data['owner']['lastName'],
                    'middlename' => $data['owner']['middleName']
                ]
            ],
            'applicantIsOwner' => $isApplicantOwner
        ];

        // Build details
        $details = [
            'issueDate' => isset($data['other_info']['techPassportIssueDate'])
                ? Carbon::parse($data['other_info']['techPassportIssueDate'])->format('Y-m-d')
                : Carbon::now()->format('Y-m-d'),
            'startDate' => $data['policy_start_date'] ?? Carbon::now()->format('Y-m-d'),
            'endDate' => $data['policy_end_date'] ?? Carbon::now()->addYear()->format('Y-m-d'),
            'driverNumberRestriction' => ($data['driver_limit'] ?? 'unlimited') == "limited",
            'specialNote' => 'Перевыпуск',
            'insuredActivityType' => 'Вид деятельности'
        ];

        // Calculate price on server-side (SECURITY: Never trust client-side calculations)
        $priceCalculator = new OsagoPriceCalculator();

        try {
            $calculatedPrice = $priceCalculator->calculate(
                govNumber: $data['gov_number'],
                vehicleTypeId: $data['other_info']['typeId'] ?? 2,
                period: $data['insurance_infos']['period'] ?? 1,
                driverLimit: $data['driver_limit'] ?? 'unlimited'
            );

            $insurancePremium = $calculatedPrice['amount'];

            // SECURITY: Verify client-submitted price if exists
            if (isset($data['insurance_infos']['amount'])) {
                $submittedAmount = (int)(str_replace(',', '', $data['insurance_infos']['amount']));

                // Log price mismatch (potential tampering)
                if (!$priceCalculator->verifyPrice(
                    $submittedAmount,
                    $data['gov_number'],
                    $data['other_info']['typeId'] ?? 2,
                    $data['insurance_infos']['period'] ?? 1,
                    $data['driver_limit'] ?? 'unlimited'
                )) {
                    Log::warning('OSAGO price mismatch detected', [
                        'submitted' => $submittedAmount,
                        'calculated' => $insurancePremium,
                        'gov_number' => $data['gov_number'],
                        'ip' => request()->ip(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('OSAGO price calculation failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            $insurancePremium = 168000; // Fallback
        }

        $cost = [
            'discountId' => '1',
            'discountSum' => 0,
            'insurancePremium' => $insurancePremium, // Server-calculated price
            'sumInsured' => 40000000, // Fixed amount
            'contractTermConclusionId' => $data['insurance_infos']['period'] ?? 1,
            'useTerritoryId' => $useTerritoryId,
            'commission' => 0,
            'insurancePremiumPaidToInsurer' => $insurancePremium, // Server-calculated price
            'seasonalInsuranceId' => 7,
            'foreignVehicleId' => null
        ];

        // Build vehicle
        $vehicle = [
            'techPassport' => [
                'number' => $data['tech_passport_number'],
                'seria' => $data['tech_passport_series'],
                'issueDate' => isset($data['other_info']['techPassportIssueDate'])
                    ? Carbon::parse($data['other_info']['techPassportIssueDate'])->format('Y-m-d')
                    : Carbon::now()->format('Y-m-d')
            ],
            'modelCustomName' => $data['model'],
            'engineNumber' => $data['engine_number'],
            'typeId' => ($data['other_info']['typeId'] ?? 2) == 2 ? 1 : ($data['other_info']['typeId'] ?? 1),
            'issueYear' => $data['car_year'],
            'govNumber' => $data['gov_number'],
            'bodyNumber' => $data['other_info']['bodyNumber'],
            'regionId' => $isApplicantOwner
                ? ($data['owner']['infos']['regionId'] ?? null)
                : ($data['applicant']['infos']['regionId'] ?? null),
            'terrainId' => '1'
        ];

        return new self(
            applicant: $applicant,
            owner: $owner,
            details: $details,
            cost: $cost,
            vehicle: $vehicle,
            drivers: $driverLimit,
            govNumber: $data['gov_number'] ?? '',
            insurancePremium: $insurancePremium,
        );
    }

    /**
     * Validate presence of required fields in an array.
     *
     * @param array $data
     * @param array $fields
     */
    private static function validateRequiredFields(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data) || ($data[$field] === null) || ($data[$field] === '')) {
                Log::error('OSAGO DTO: Required field missing', ['field' => $field]);
                throw new \InvalidArgumentException("Required field missing: {$field}");
            }
        }
    }

    public function toArray(): array
    {
        return [
            'applicant' => $this->applicant,
            'owner' => $this->owner,
            'details' => $this->details,
            'cost' => $this->cost,
            'vehicle' => $this->vehicle,
            'drivers' => $this->drivers,
            'govNumber' => $this->govNumber,
            'insurancePremium' => $this->insurancePremium,
        ];
    }

    public function toApiFormat(): array
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
