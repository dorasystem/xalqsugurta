<?php

namespace App\Http\Controllers\Insurence;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\Osago\InsurantInfoRequest;

class OsagoController extends Controller
{
    public function main(): View
    {
        return view('pages.insurence.osago.main');
    }


    public function application(): View
    {
        return view('pages.insurence.application');
    }

    public function payment(): View
    {
        return view('pages.insurence.payment');
    }

    // public function calculation(InsurantInfoRequest $request)
    public function calculation(Request $request)
    {

        try {
            // dd($request->all());
            $data = $request->all();
            $region = substr($data['gov_number'], 0, 2);
            $vehicleOtherInfo = json_decode($data['other_info'], true);
            $ownerInfo = json_decode($data['owner_infos'], true);
            $applicantInfo = json_decode($data['applicant_infos'], true);
            $insuranceInfos = json_decode($data['insurance_infos'], true);
            $useTerritoryId = in_array($region, ['01', '10']) ? 1 : 2;

            if ($data['is_applicant_owner'] == "on" && $ownerInfo['address']) {
                $address = $ownerInfo['address'];
            } elseif (isset($data['applicant_address']) && $data['applicant_address']) {
                $address = $data['applicant_address'];
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

                $address = $regions[$region];
                $regionsIDForEosgouz = ["01" => 10, "10" => 11, "20" => 12, "25" => 13, "30" => 14, "40" => 15, "50" => 16, "60" => 17, "70" => 18, "75" => 19, "80" => 20, "85" => 21, "90" => 22, "95" => 23];
            }

            if ($data['driver_limit'] == "unlimited") {
                $driver_limit = [];
            } else {
                $driver_limit = [];
                foreach ($data['driver_full_name'] as $key => $value) {
                    $fullInfoOfDriver = json_decode($data['driver_full_info'][$key], true);
                    $driver_limit[] =
                        [
                            'passportData' => [
                                'pinfl' => $fullInfoOfDriver['pinfl'] ?? '12345678901234',
                                'seria' => $fullInfoOfDriver['seria'] ?? 'AA',
                                'number' => $fullInfoOfDriver['number'] ?? '1234567',
                                'issuedBy' => $fullInfoOfDriver['issuedBy'] ?? 'УВД Яккасарайского района',
                                'issueDate' => $fullInfoOfDriver['issueDate'] ?? '2015-10-30'
                            ],
                            'fullName' => [
                                'firstname' => $fullInfoOfDriver['firstname'] ?? 'Иван',
                                'lastname' => $fullInfoOfDriver['lastname'] ?? 'Иванов',
                                'middlename' => $fullInfoOfDriver['middlename'] ?? 'Иванович'
                            ],
                            'licenseNumber' => $fullInfoOfDriver['licenseNumber'] ?? '1546546',
                            'licenseSeria' => $fullInfoOfDriver['licenseSeria'] ?? 'AA',
                            'relative' => $fullInfoOfDriver['kinship'] ?? 0,
                            'birthDate' => $fullInfoOfDriver['birthDate'] ?? '1989-05-30',
                            'licenseIssueDate' => $fullInfoOfDriver['licenseIssueDate'] ?? '2015-05-30',
                            'residentOfUzb' => 1
                        ];
                }
            }

            $infoShablon = [
                'applicant' => [
                    'person' => [
                        'passportData' => [
                            'pinfl' =>  $data['is_applicant_owner'] == "on" ? $data['pinfl'] : $data['applicant_pinfl'],
                            'seria' => $data['is_applicant_owner'] == "on" ? $data['passport_series'] : $data['applicant_passport_series'],
                            'number' => $data['is_applicant_owner'] == "on" ? $data['passport_number'] : $data['applicant_passport_number'],
                            'issuedBy' => $data['is_applicant_owner'] == "on" ? $ownerInfo['issuedBy'] : $applicantInfo['issuedBy'],
                            'issueDate' => $data['is_applicant_owner'] == "on" ? $ownerInfo['issueDate'] : $applicantInfo['issueDate']
                        ],
                        'fullName' => [
                            'firstname' => $data['is_applicant_owner'] == "on" ? $data['first_name'] : $data['applicant_first_name'],
                            'lastname' => $data['is_applicant_owner'] == "on" ? $data['last_name'] : $data['applicant_last_name'],
                            'middlename' => $data['is_applicant_owner'] == "on" ? $data['middle_name'] : $data['applicant_middle_name']
                        ],
                        'phoneNumber' => $data['applicant_phone_number'] ?? '998901234578',
                        'gender' => $data['is_applicant_owner'] == "on" ? ($ownerInfo['gender'] == "1" ? "m" : "f") : ($applicantInfo['gender'] == "1" ? "m" : "f"),
                        'birthDate' => $data['is_applicant_owner'] == "on" ? $ownerInfo['birthDate'] : $applicantInfo['birthDate'],
                        'regionId' => $data['is_applicant_owner'] == "on" ? $ownerInfo['regionId'] : $applicantInfo['regionId'],
                        'districtId' => $data['is_applicant_owner'] == "on" ? $ownerInfo['districtId'] : $applicantInfo['districtId']
                    ],
                    'address' => $address,
                    'email' => 'example@example.com',
                    'residentOfUzb' => 1,
                    'citizenshipId' => 210
                ],
                'owner' => [
                    'person' => [
                        'passportData' => [
                            'pinfl' => $data['pinfl'] ?? '12345678901234',
                            'seria' => $data['passport_series'] ?? 'AA',
                            'number' => $data['passport_number'] ?? '1234567',
                            'issuedBy' => $ownerInfo['issuedBy'] ??  'УВД Яккасарайского района',
                            'issueDate' => $ownerInfo['issueDate'] ?? '2015-10-30'
                        ],
                        'fullName' => [
                            'firstname' => $data['first_name'] ?? 'Иван',
                            'lastname' => $data['last_name'] ?? 'Иванов',
                            'middlename' => $data['middle_name'] ?? 'Иванович'
                        ]
                    ],
                    'applicantIsOwner' => $data['is_applicant_owner'] == "on" ? true : false
                ],
                'details' => [
                    'issueDate' => Carbon::parse($vehicleOtherInfo['techPassportIssueDate'])->format('Y-m-d'), //tex passport sanasi
                    'startDate' => $data['policy_start_date'] ?? Carbon::now()->format('Y-m-d'),
                    'endDate' => $data['policy_end_date'] ?? Carbon::now()->addYear()->format('Y-m-d'),
                    'driverNumberRestriction' => $data['is_applicant_owner'] == "on" ? true : false,
                    'specialNote' => 'Перевыпуск',
                    'insuredActivityType' => 'Вид деятельности'
                ],
                'cost' => [
                    'discountId' => '1',
                    'discountSum' => 0,
                    'insurancePremium' => (int)(str_replace(',', '', $insuranceInfos['amount'])) ?? '56000', //b garak
                    'sumInsured' => $insuranceInfos['insuranceAmount'] ?? '40000000', //b garak
                    'contractTermConclusionId' => $insuranceInfos['period'] ?? 1, //bom garak
                    'useTerritoryId' => $useTerritoryId ?? 1,
                    'commission' => 0,
                    'insurancePremiumPaidToInsurer' => $insuranceInfos['amount'] ?? '56000', //bom garak
                    'seasonalInsuranceId' => 7,
                    'foreignVehicleId' => null
                ],
                'vehicle' => [
                    'techPassport' => [
                        'number' => $data['tech_passport_number'] ?? '01223456',
                        'seria' => $data['tech_passport_series'] ?? 'AAC',
                        'issueDate' => Carbon::parse($vehicleOtherInfo['techPassportIssueDate'])->format('Y-m-d') ??  '2015-10-30'
                    ],
                    'modelCustomName' => $data['model'] ?? 'Nexia 3',
                    'engineNumber' => $data['engine_number'] ?? 'df32rfafh98sa',
                    'typeId' => $vehicleOtherInfo['typeId'] == 2 ? 1 : $vehicleOtherInfo['typeId'],
                    'issueYear' => $data['car_year'] ?? '2015',
                    'govNumber' => $data['gov_number'] ?? '01K384SO',
                    'bodyNumber' => $vehicleOtherInfo['bodyNumber'] ?? 'jk543kj453k4',
                    'regionId' => '1',
                    'terrainId' => '1'
                ],
                'drivers' => $driver_limit
            ];
            dd($infoShablon);
            return response()->json([
                'success' => true,
                'data' => [
                    'base_price' => round($baseAmount),
                    'adjusted_base_price' => round($baseAmount),
                    'incident_surcharge' => 0, // No incident surcharge in optimized calculation
                    'discount_amount' => round($discountAmount),
                    'total_price' => round($totalPrice),
                    'vehicle_type' => 'Легковой автомобиль',
                    'insurance_period' => $request->insurance_period,
                    'policy_start_date' => $request->policy_start_date,
                    'policy_end_date' => $endDate->format('Y-m-d'),
                    'discount_option' => $request->discount_option,
                    'cases' => $request->cases,
                    'driver_limit' => $request->driver_limit,
                    'vehicle_type_coefficient' => $vehicleTypeC,
                    'region_coefficient' => $regionIdC,
                    'period_coefficient' => $periodC,
                    'driver_limit_coefficient' => $limitedC,
                    'calc_discount' => $calcDiscount,
                    'insurance_amount' => $insuranceAmount,
                    'calculation_date' => now()->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка расчета: ' . $e->getMessage()
            ], 500);
        }
    }
}
