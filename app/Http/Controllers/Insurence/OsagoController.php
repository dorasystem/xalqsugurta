<?php

namespace App\Http\Controllers\Insurence;

use Exception;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use App\Http\Requests\Insurance\Osago\InsurantInfoRequest;

class OsagoController extends Controller
{

    public function __construct(private readonly OrderService $orderService) {}

    public function main(): View
    {
        return view('pages.insurence.osago.main');
    }

    // public function calculation(Request $request)
    public function calculation(InsurantInfoRequest $request)
    {

        try {
            // dd($request->all());
            $data = $request->all();
            $region = substr($data['gov_number'], 0, 2);
            $useTerritoryId = in_array($region, ['01', '10']) ? 1 : 2;

            if ($data['is_applicant_owner'] == "on" && $data['owner_infos']['address']) {
                $address = $data['owner_infos']['address'];
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
            }
            $regionsIDForEosgouz = ["01" => 10, "10" => 11, "20" => 12, "25" => 13, "30" => 14, "40" => 15, "50" => 16, "60" => 17, "70" => 18, "75" => 19, "80" => 20, "85" => 21, "90" => 22, "95" => 23];

            if ($data['driver_limit'] == "unlimited") {
                $driver_limit = [];
            } else {
                $driver_limit = [];

                foreach ($data['driver_full_info'] as $key => $driver) {
                    $driver_limit[] =
                        [
                            'passportData' => [
                                'pinfl' => $driver['pinfl'] ?? '12345678901234',
                                'seria' => $driver['seria'] ?? 'AA',
                                'number' => $driver['number'] ?? '1234567',
                                'issuedBy' => $driver['issuedBy'] ?? 'УВД Яккасарайского района',
                                'issueDate' => $driver['issueDate'] ?? '2015-10-30'
                            ],
                            'fullName' => [
                                'firstname' => $driver['firstname'] ?? 'Иван',
                                'lastname' => $driver['lastname'] ?? 'Иванов',
                                'middlename' => $driver['middlename'] ?? 'Иванович'
                            ],
                            'licenseNumber' => $driver['licenseNumber'] ?? '1546546',
                            'licenseSeria' => $driver['licenseSeria'] ?? 'AA',
                            'relative' => $driver['kinship'] ?? 0,
                            'birthDate' => Carbon::parse($driver['birthDate'])->format('Y-m-d') ?? '1989-05-30',
                            'licenseIssueDate' => $driver['licenseIssueDate'] ?? '2015-05-30',
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
                            'issuedBy' => $data['is_applicant_owner'] == "on" ? $data['owner_infos']['issuedBy'] : $data['applicant_infos']['issuedBy'],
                            'issueDate' => $data['is_applicant_owner'] == "on" ? $data['owner_infos']['issueDate'] : $data['applicant_infos']['issueDate']
                        ],
                        'fullName' => [
                            'firstname' => $data['is_applicant_owner'] == "on" ? $data['first_name'] : $data['applicant_first_name'],
                            'lastname' => $data['is_applicant_owner'] == "on" ? $data['last_name'] : $data['applicant_last_name'],
                            'middlename' => $data['is_applicant_owner'] == "on" ? $data['middle_name'] : $data['applicant_middle_name']
                        ],
                        'phoneNumber' => $data['applicant_phone_number'] ?? '998901234578',
                        'gender' => $data['is_applicant_owner'] == "on" ? ($data['owner_infos']['gender'] == "1" ? "m" : "f") : ($data['applicant_infos']['gender'] == "1" ? "m" : "f"),
                        'birthDate' => $data['is_applicant_owner'] == "on" ? $data['owner_infos']['birthDate'] : $data['applicant_infos']['birthDate'],
                        'regionId' => $data['is_applicant_owner'] == "on" ? $data['owner_infos']['regionId'] : $data['applicant_infos']['regionId'],
                        'districtId' => $data['is_applicant_owner'] == "on" ? $data['owner_infos']['districtId'] : $data['applicant_infos']['districtId'],
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
                            'issuedBy' => $data['owner_infos']['issuedBy'] ??  'УВД Яккасарайского района',
                            'issueDate' => $data['owner_infos']['issueDate'] ?? '2015-10-30'
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
                    'issueDate' => Carbon::parse($data['other_info']['techPassportIssueDate'])->format('Y-m-d') ?? Carbon::now()->format('Y-m-d'), //tex passport sanasi
                    'startDate' => $data['policy_start_date'] ?? Carbon::now()->format('Y-m-d'),
                    'endDate' => $data['policy_end_date'] ?? Carbon::now()->addYear()->format('Y-m-d'),
                    'driverNumberRestriction' => $data['driver_limit'] == "limited" ? true : false,
                    'specialNote' => 'Перевыпуск',
                    'insuredActivityType' => 'Вид деятельности'
                ],
                'cost' => [
                    'discountId' => '1',
                    'discountSum' => 0,
                    'insurancePremium' => (int)(str_replace(',', '', $data['insurance_infos']['amount'])) ?? '56000',
                    'sumInsured' => $data['insurance_infos']['insuranceAmount'] ?? '40000000',
                    'contractTermConclusionId' => $data['insurance_infos']['period'] ?? 1,
                    'useTerritoryId' => $useTerritoryId ?? 1,
                    'commission' => 0,
                    'insurancePremiumPaidToInsurer' => (int)(str_replace(',', '', $data['insurance_infos']['amount'])) ?? '56000',
                    'seasonalInsuranceId' => 7,
                    'foreignVehicleId' => null
                ],
                'vehicle' => [
                    'techPassport' => [
                        'number' => $data['tech_passport_number'] ?? '01223456',
                        'seria' => $data['tech_passport_series'] ?? 'AAC',
                        'issueDate' => Carbon::parse($data['other_info']['techPassportIssueDate'])->format('Y-m-d') ??  '2015-10-30'
                    ],
                    'modelCustomName' => $data['model'] ?? 'Nexia 3',
                    'engineNumber' => $data['engine_number'] ?? 'df32rfafh98sa',
                    'typeId' => $data['other_info']['typeId'] == 2 ? 1 : $data['other_info']['typeId'],
                    'issueYear' => $data['car_year'] ?? '2015',
                    'govNumber' => $data['gov_number'] ?? '01K384SO',
                    'bodyNumber' => $data['other_info']['bodyNumber'] ?? 'jk543kj453k4',
                    'regionId' => $data['is_applicant_owner'] == "on" ? $data['owner_infos']['regionId'] : $data['applicant_infos']['regionId'],
                    'terrainId' => '1'
                ],
                'drivers' => $driver_limit
            ];
            // dd($infoShablon);
            Session::put('insurance_info', $infoShablon);

            return redirect()->route('osago.application', ['locale' => getCurrentLocale()]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка расчета: ' . $e->getMessage()
            ], 500);
        }
    }

    public function application(): View
    {
        $data = Session::get('insurance_info');
        return view('pages.insurence.osago.application', compact('data'));
    }

    public function prepare()
    {
        try {
            $info = Session::get('insurance_info');

            if (empty($info)) {
                return redirect()->back()->with('error', 'Ariza ma\'lumotlari topilmadi. Iltimos, qaytadan ariza to\'ldiring.');
            }

            // Send the request with timeout and retry (recommended)
            $response = Http::timeout(10)
                ->retry(3, 1000)
                ->post('https://impex-insurance.uz/api/osago/contract/add', $info);

            // Check if request failed
            if ($response->failed()) {
                return redirect()->back()->with('error', 'Xatolik yuz berdi: ' . $response->body());
            }

            // Check success
            if ($response->successful()) {
                $apiResponse = $response->json();

                // Optional: dump for testing
                // dd($apiResponse);

                $orderData = [
                    'product_name' => 'osago',
                    'amount' => $info['cost']['insurancePremium'] ?? 0,
                    'state' => 0,
                    'insurance_id' => $apiResponse['response']['result']['uuid'] ?? uniqid('acc_'),
                    'phone' => $info['applicant']['person']['phoneNumber'] ?? null,
                    'insurances_data' => $info,
                    'insurances_response_data' => $apiResponse,
                ];

                $order = $this->orderService->createOrder($orderData)->id;

                return redirect()->route('osago.payment', ['locale' => getCurrentLocale(),'order' => $order]);
            }

            return redirect()->back()->with('error', 'Xatolik yuz berdi');
        } catch (ConnectionException $e) {
            return redirect()->back()->with('error', 'Tarmoq xatosi yoki API bilan aloqa o‘rnatilmadi.');
        } catch (RequestException $e) {
            return redirect()->back()->with('error', 'So‘rovni amalga oshirishda xatolik yuz berdi: ' . $e->getMessage());
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Kutilmagan xatolik: ' . $e->getMessage());
        }
    }

    public function payment($lang, Order $order)
    {
        // dd($order);
        return view('pages.insurence.osago.payment', compact('order'));
    }
}
