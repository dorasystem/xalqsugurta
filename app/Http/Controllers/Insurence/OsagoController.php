<?php

namespace App\Http\Controllers\Insurence;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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

    public function calculation(Request $request)
    {
        dd($request->all());
        $data = $request->all();
        try {

            $infoShablon = [
                'applicant' => [
                    'person' => [
                        'passportData' => [
                            'pinfl' =>  $data->is_applicant_owner == "on" ? $data->pinfl : $data->applicant_pinfl,
                            'seria' => $data->is_applicant_owner == "on" ? $data->passport_series : $data->applicant_passport_series,
                            'number' => $data->is_applicant_owner == "on" ? $data->passport_number : $data->applicant_passport_number,
                            'issuedBy' => 'УВД Яккасарайского района',
                            'issueDate' => $data->passport_date ?? '2015-10-30' //bom garak
                        ],
                        'fullName' => [
                            'firstname' => $data->is_applicant_owner == "on" ? $data->first_name : $data->applicant_first_name,
                            'lastname' => $data->is_applicant_owner == "on" ? $data->last_name : $data->applicant_last_name,
                            'middlename' => $data->is_applicant_owner == "on" ? $data->middle_name : $data->applicant_middle_name
                        ],
                        'phoneNumber' => $data->applicant_phone_number ?? '998901234578',
                        'gender' => 'm', //b garak, 
                        'birthDate' => '1990-10-30', //bom garak
                        'regionId' => '1', //bom garak
                        'districtId' => '1' //bom garak
                    ],
                    'address' => 'ул. такая-то, дом такой-то', //
                    'email' => 'example@example.com', //yo'q b
                    'residentOfUzb' => 1,
                    'citizenshipId' => 1 //210
                ],
                'owner' => [
                    'person' => [
                        'passportData' => [
                            'pinfl' => $data->pinfl ?? '12345678901234',
                            'seria' => $data->passport_series ?? 'AA',
                            'number' => $data->passport_number ?? '1234567',
                            'issuedBy' => 'УВД Яккасарайского района',
                            'issueDate' => '2015-10-30' //bom garak
                        ],
                        'fullName' => [
                            'firstname' => $data->first_name ?? 'Иван',
                            'lastname' => $data->last_name ?? 'Иванов',
                            'middlename' => $data->middle_name ?? 'Иванович'
                        ]
                    ],
                    'applicantIsOwner' => $data->is_applicant_owner == "on" ? true : false
                ],
                'details' => [
                    'issueDate' => '2021-01-30',//tex passport sanasi
                    'startDate' => $data->policy_start_date ?? '2021-01-30',
                    'endDate' => $data->policy_start_date ?? '2021-01-30',
                    'driverNumberRestriction' => $data->is_applicant_owner == "on" ? true : false,
                    'specialNote' => 'Перевыпуск',
                    'insuredActivityType' => 'Вид деятельности'
                ],
                'cost' => [
                    'discountId' => '1',//aldinnan yozip qo'yiladi
                    'discountSum' => '0',//aldinnan 0
                    'insurancePremium' => $data->pinfl ?? '56000',
                    'sumInsured' => '40000000',
                    'contractTermConclusionId' => '1',
                    'useTerritoryId' => '1',
                    'commission' => '10000',
                    'insurancePremiumPaidToInsurer' => '28000',
                    'seasonalInsuranceId' => '1',
                    'foreignVehicleId' => '1'
                ],
                'vehicle' => [
                    'techPassport' => [
                        'number' => $data->tech_passport_number ?? '01223456',
                        'seria' => $data->tech_passport_series ?? 'AAC',
                        'issueDate' => '2015-10-30'//b garak
                    ],
                    'modelCustomName' => $data->model ?? 'Nexia 3',
                    'engineNumber' => $data->engine_number ?? 'df32rfafh98sa',
                    'typeId' => '1',//b garak
                    'issueYear' => '2015',//b garak
                    'govNumber' => $data->gov_number ?? '01K384SO',
                    'bodyNumber' => 'jk543kj453k4',//b garak
                    'regionId' => '1',//b garak
                    'terrainId' => '1'//b garak
                ],
                'drivers' => [//bilaram qo'shiladi ayar garak bo'sa
                    [
                        'passportData' => [
                            'pinfl' => '12345678901234',
                            'seria' => 'AA',
                            'number' => '1234567',
                            'issuedBy' => 'УВД Яккасарайского района',
                            'issueDate' => '2015-10-30'
                        ],
                        'fullName' => [
                            'firstname' => 'Иван',
                            'lastname' => 'Иванов',
                            'middlename' => 'Иванович'
                        ],
                        'licenseNumber' => '1546546',
                        'licenseSeria' => 'AA',
                        'relative' => 0,
                        'birthDate' => '1989-05-30',
                        'licenseIssueDate' => '2015-05-30',
                        'residentOfUzb' => 1
                    ]
                ]
            ];

            // Validate request
            $request->validate([
                'policy_start_date' => 'required|date',
                'insurance_period' => 'required|string',
                'discount_option' => 'required|string',
                'cases' => 'required|string',
                'driver_limit' => 'required|string',
                '_vehicleTypeC' => 'required|numeric|min:0',
                'regionIdC' => 'required|numeric|min:0',
            ]);

            // Get coefficients from request
            $vehicleTypeC = (float) $request->input('_vehicleTypeC', 0.1);
            $regionIdC = (float) $request->input('regionIdC', 1.0);

            // Period multipliers
            $periodMultipliers = [
                '1_year' => 1.0,
                '6_months' => 0.6,
                '3_months' => 0.3,
            ];

            // Driver limit coefficient
            $limitedC = $request->driver_limit === 'limited' ? 1.0 : 1.5;

            // Apply period multiplier
            $periodC = $periodMultipliers[$request->insurance_period] ?? 1.0;

            // Calculate discount coefficient (optimized calculation from provided code)
            $calcDiscount = $vehicleTypeC * $regionIdC * $periodC * $limitedC;

            // Insurance amount (40 million sum)
            $insuranceAmount = 40000000;

            // Calculate base amount (this gives us the premium amount)
            $baseAmount = ($calcDiscount * $insuranceAmount) / 100;

            // Ensure minimum amount
            if ($baseAmount <= 0 || is_nan($baseAmount)) {
                $baseAmount = 168000; // Minimum amount
            }

            // The baseAmount is already the premium, no additional discount needed
            // The discount_option field is not used in this calculation
            $totalPrice = $baseAmount;
            $discountAmount = 0; // No discount applied in this calculation

            // Calculate end date
            $startDate = \Carbon\Carbon::parse($request->policy_start_date);
            $endDate = $startDate->copy();

            switch ($request->insurance_period) {
                case '1_year':
                    $endDate->addYear();
                    break;
                case '6_months':
                    $endDate->addMonths(6);
                    break;
                case '3_months':
                    $endDate->addMonths(3);
                    break;
            }
            $endDate->subDay(); // Subtract one day

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
