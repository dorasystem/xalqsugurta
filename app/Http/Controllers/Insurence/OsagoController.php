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
                            'pinfl' =>  '12345678901234',
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
                        'phoneNumber' => '998901234578',
                        'gender' => 'm',
                        'birthDate' => '1990-10-30',
                        'regionId' => '1',
                        'districtId' => '1'
                    ],
                    'organization' => [
                        'inn' => '123456789',
                        'name' => 'ООО Фирма',
                        'phoneNumber' => 'string'
                    ],
                    'address' => 'ул. такая-то, дом такой-то',
                    'email' => 'example@example.com',
                    'residentOfUzb' => 1,
                    'citizenshipId' => 1
                ],
                'owner' => [
                    'person' => [
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
                        ]
                    ],
                    'organization' => [
                        'inn' => '123456789',
                        'name' => 'ООО Фирма'
                    ],
                    'applicantIsOwner' => false
                ],
                'details' => [
                    'issueDate' => '2021-01-30',
                    'startDate' => '2021-01-30',
                    'endDate' => '2021-01-30',
                    'driverNumberRestriction' => true,
                    'specialNote' => 'Перевыпуск',
                    'insuredActivityType' => 'Вид деятельности'
                ],
                'cost' => [
                    'discountId' => '1',
                    'discountSum' => '28000',
                    'insurancePremium' => '56000',
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
                        'number' => '01223456',
                        'seria' => 'AAC',
                        'issueDate' => '2015-10-30'
                    ],
                    'modelCustomName' => 'Nexia 3',
                    'engineNumber' => 'df32rfafh98sa',
                    'typeId' => '1',
                    'issueYear' => '2015',
                    'govNumber' => '01K384SO',
                    'bodyNumber' => 'jk543kj453k4',
                    'regionId' => '1',
                    'terrainId' => '1'
                ],
                'drivers' => [
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
