<?php

namespace App\Http\Controllers\Payments\PayMe;

use App\Models\Order;
use App\Models\Country;
use App\Models\TravelPdf;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\TransactionResource;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PaymeController extends Controller
{
    public function payment(Request $request)
    {
        // dd($request->all());
        $order = Order::findOrFail($request->id);
        $amount = $order->amount;
        $transactionId = $order->id;
        // $backUrl = route('show.insurance.pdf', ['id' => $order->id]);

        $merchantId = '67287714e51de1c6a3a79636';
        $tiyinAmount = intval($amount * 100);
        $payload = "m={$merchantId};ac.order_id={$transactionId};a={$tiyinAmount};";
        $encoded = base64_encode($payload);
        $redirectUrl = "https://checkout.paycom.uz/{$encoded}";
        // dd($redirectUrl);
        return redirect($redirectUrl);
    }

    public function handleCallback(Request $req)
    {
        if ($req->method == "CheckPerformTransaction") {
            Log::info("CheckPerformTransaction chaqirildi", ['request' => $req]);

            if (!isset($req->params['account']['order_id'])) {
                $response = [
                    'id' => $req->id,
                    'error' => [
                        'code' => -31050,
                        'message' => [
                            "uz" => "Buyurtma topilmadi",
                            "ru" => "Заказ не найден",
                            "en" => "Order not found"
                        ]
                    ]
                ];
                return json_encode($response);
            }
            if (empty($req->params['account']['order_id'])) {
                Log::error("Order ID kelmadi");
                return json_encode([
                    'id' => $req->id,
                    'error' => [
                        'code' => -31050,
                        'message' => [
                            "uz" => "Buyurtma topilmadi",
                            "ru" => "Заказ не найден",
                            "en" => "Order not found"
                        ]
                    ]
                ]);
            }

            $orderId = (int)$req->params['account']['order_id']; // Order IDni integerga o‘giramiz
            Log::info("Tekshirilayotgan Order ID: " . $orderId);

            $order = Order::where('id', $orderId)->first();

            if (!$order) {
                Log::error("Order ID {$orderId} topilmadi");
                return json_encode([
                    'id' => $req->id,
                    'error' => [
                        'code' => -31050,
                        'message' => [
                            "uz" => "Buyurtma topilmadi",
                            "ru" => "Заказ не найден",
                            "en" => "Order not found"
                        ]
                    ]
                ]);
            }

            if (intval($order->amount * 100) != intval($req->params['amount'])) {
                Log::error("Summalar mos kelmadi", [
                    'expected' => intval($order->amount * 100),
                    'received' => intval($req->params['amount'])
                ]);

                return json_encode([
                    'id' => $req->id,
                    'error' => [
                        'code' => -31001,
                        'message' => [
                            "uz" => "Noto‘g‘ri summa",
                            "ru" => "Неверная сумма",
                            "en" => "Incorrect amount"
                        ]
                    ]
                ]);
            }



            Log::info("To‘lov tasdiqlandi, allow: true");

            return json_encode([
                'result' => [
                    'allow' => true
                ]
            ]);
        } elseif ($req->method == "CreateTransaction") {
            if (!isset($req->params['account'])) {
                return response()->json([
                    'id' => $req->id,
                    'error' => [
                        'code' => -32504,
                        'message' => "Bajarish usuli uchun imtiyozlar etarli emas."
                    ]
                ]);
            }

            $account = $req->params['account'];
            $order_id = $account['order_id'] ?? null;

            if (!$order_id) {
                return response()->json([
                    'id' => $req->id,
                    'error' => [
                        'code' => -31050,
                        'message' => [
                            "uz" => "Buyurtma ID kiritilmagan",
                            "ru" => "ID заказа не указан",
                            "en" => "Order ID is not specified"
                        ]
                    ]
                ]);
            }

            $order = Order::find($order_id);
            $transaction = Transaction::where('order_id', $order_id)->where('state', 1)->get();


            if (!$order) {
                return response()->json([
                    'id' => $req->id,
                    'error' => [
                        'code' => -31050,
                        'message' => [
                            "uz" => "Buyurtma topilmadi",
                            "ru" => "Заказ не найден",
                            "en" => "Order not found"
                        ]
                    ]
                ]);
            }


            if (intval($order->amount * 100) != intval($req->params['amount'])) {
                Log::error("Summalar mos kelmadi", [
                    'expected' => intval($order->amount * 100),
                    'received' => intval($req->params['amount'])
                ]);

                return json_encode([
                    'id' => $req->id,
                    'error' => [
                        'code' => -31001,
                        'message' => [
                            "uz" => "Noto‘g‘ri summa",
                            "ru" => "Неверная сумма",
                            "en" => "Incorrect amount"
                        ]
                    ]
                ]);
            }


            if ($transaction->isEmpty()) {
                $transaction = DB::transaction(function () use ($req, $account) {
                    $transaction = new Transaction();
                    $transaction->paycom_transaction_id = $req->params['id'];
                    $transaction->paycom_time = $req->params['time'];
                    $transaction->paycom_time_datetime = now();
                    $transaction->amount = $req->params['amount'];
                    $transaction->state = 1;
                    $transaction->order_id = $account['order_id'];
                    $transaction->save();
                    return $transaction; // Qo'shildi
                });

                return response()->json([
                    "result" => [
                        'create_time' => $req->params['time'],
                        'transaction' => strval($transaction->id), // Endi null bo‘lmaydi
                        'state' => $transaction->state
                    ]
                ]);
            } elseif ($transaction->isNotEmpty() && $transaction->first()->paycom_time == $req->params['time'] && $transaction->first()->paycom_transaction_id == $req->params['id']) {
                return response()->json([
                    'result' => [
                        "create_time" => $req->params['time'],
                        "transaction" => "{$transaction[0]->id}",
                        "state" => intval($transaction[0]->state)
                    ]
                ]);
            } else {
                return response()->json([
                    'id' => $req->id,
                    'error' => [
                        'code' => -31099,
                        'message' => [
                            "uz" => "Buyurtma tolovi hozirda amalga oshirilmoqda",
                            "ru" => "Оплата заказа в данный момент обрабатывается",
                            "en" => "Order payment is currently being processed"
                        ]
                    ]
                ]);
            }
        } elseif ($req->method == "CheckTransaction") {
            $transaction = Transaction::where('paycom_transaction_id', $req->params['id'])->first();

            if (!$transaction) {
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $req->id,
                    'error' => [
                        'code' => -31003,
                        'message' => "Транзакция не найдена."
                    ]
                ]);
            }

            $response = [
                'jsonrpc' => '2.0',
                'id' => $req->id,
                'result' => [
                    'create_time' => intval($transaction->paycom_time),
                    'perform_time' => !empty($transaction->perform_time_unix) ? intval($transaction->perform_time_unix) : 0,
                    'cancel_time' => in_array($transaction->state, [-1, -2]) ? intval($transaction->cancel_time) : 0,
                    'transaction' => strval($transaction->id), // STRING format
                    'state' => intval($transaction->state),
                    'reason' => ($transaction->state < 0) ? intval($transaction->reason) : null // null bo‘lishi kerak
                ]
            ];

            return response()->json($response);
        } elseif ($req->method == "PerformTransaction") {
            $ldate = date('Y-m-d H:i:s');
            $transaction = Transaction::where('paycom_transaction_id', $req->params['id'])->first();
            if (empty($transaction)) {
                // Log::info('Transaction');
                $response = [
                    'id' => $req->id,
                    'error' => [
                        'code' => -31003,
                        'message' => "Транзакция не найдена "
                    ]
                ];
                return json_encode($response);
            } elseif ($transaction->state == 1) {
                $currentMillis = intval(microtime(true) * 1000);
                $transaction = Transaction::where('paycom_transaction_id', $req->params['id'])->first();
                $transaction->state = 2;
                $transaction->perform_time = $ldate;
                $transaction->perform_time_unix = str_replace('.', '', $currentMillis);
                $transaction->update();
                $completed_order = Order::where('id', $transaction->order_id)->first();
                $completed_order->status = 'yakunlandi';


                Log::info("📦 Boshqa product uchun confirmPayed chaqirildi. Order ID: {$completed_order->id}");
                $this->confirmPayed($completed_order);


                $completed_order->update();
                $response = [
                    'result' => [
                        'transaction' => "{$transaction->id}",
                        'perform_time' => intval($transaction->perform_time_unix),
                        'state' => intval($transaction->state)
                    ]
                ];
                return json_encode($response);
            } elseif ($transaction->state == 2) {
                $response = [
                    'result' => [
                        'transaction' => strval($transaction->id),
                        'perform_time' => intval($transaction->perform_time_unix),
                        'state' => intval($transaction->state)
                    ]
                ];
                return json_encode($response);
            }
        } elseif ($req->method == "CancelTransaction") {
            $ldate = date('Y-m-d H:i:s');
            $transaction = Transaction::where('paycom_transaction_id', $req->params['id'])->first();
            if (empty($transaction)) {
                $response = [
                    'id' => $req->id,
                    'error' => [
                        "code" => -31003,
                        "message" => "Транзакция не найдена"
                    ]
                ];
                return json_encode($response);
            } elseif ($transaction->state == 1) {
                $currentMillis = intval(microtime(true) * 1000);
                $transaction = Transaction::where('paycom_transaction_id', $req->params['id'])->first();
                $transaction->reason = $req->params['reason'];
                $transaction->cancel_time = str_replace('.', '', $currentMillis);
                $transaction->state = -1;
                $transaction->update();

                $order = Order::find($transaction->order_id);
                $order->update(['status' => 'bekor qilindi']);
                $response = [
                    'result' => [
                        "state" => intval($transaction->state),
                        "cancel_time" => intval($transaction->cancel_time),
                        "transaction" => strval($transaction->id)
                    ]
                ];
                return $response;
            } elseif ($transaction->state == 2) {
                $currentMillis = intval(microtime(true) * 1000);
                $transaction = Transaction::where('paycom_transaction_id', $req->params['id'])->first();
                $transaction->reason = $req->params['reason'];
                $transaction->cancel_time = str_replace('.', '', $currentMillis);
                $transaction->state = -2;
                $transaction->update();

                $order = Order::find($transaction->order_id);
                $order->update(['status' => 'bekor qilindi']);
                $response = [
                    'result' => [
                        "state" => intval($transaction->state),
                        "cancel_time" => intval($transaction->cancel_time),
                        "transaction" => strval($transaction->id)
                    ]
                ];
                return $response;
            } elseif (($transaction->state == -1) or ($transaction->state == -2)) {
                $response = [
                    'result' => [
                        "state" => intval($transaction->state),
                        "cancel_time" => intval($transaction->cancel_time),
                        "transaction" => strval($transaction->id)
                    ]
                ];

                return $response;
            }
        } elseif ($req->method == "GetStatement") {
            $from = $req->params['from'];
            $to = $req->params['to'];
            $transactions = Transaction::getTransactionsByTimeRange($from, $to);

            return response()->json([
                'result' => [
                    'transactions' => TransactionResource::collection($transactions),
                ],
            ]);
        } elseif ($req->method == "ChangePassword") {
            $response = [
                'id' => $req->id,
                'error' => [
                    'code' => -32504,
                    'message' => "Недостаточно привилегий для выполнения метода"
                ]
            ];
            return json_encode($response);
        }
    }

    public function confirmPayed($completed_order)
    {
        try {
            if ($completed_order->travelCalcRequestData !== null) {
                $travelInfo = $completed_order->confirm_payed_data;
                $travelResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post('https://impexonline.uz/ords/ins/travel/sale', $travelInfo);
                $responseTravelData = $travelResponse->json(); // Javobni array sifatida olamiz

                $completed_order->update([
                    'confirm_payed_response_data' => $responseTravelData ?? 'bu pagega kirmadi',
                ]);
                $orderId = $completed_order->id;
                $existingPdf = TravelPdf::where('order_id', $orderId)->first();
                $order = Order::findOrFail($orderId);

                $countryId = $order->travelCalcRequestData['policy']['country'] ?? null;
                $country = $countryId ? Country::where('id', $countryId)->first() : null;

                $order->applicant = $order->confirm_payed_data['applicant'] ?? null;

                if (!$existingPdf) {
                    // QR-kodlarni yaratish va base64 formatga o'tkazish
                    $svgContent1 = QrCode::format('svg')->size(120)->generate('https://impex-insurance.uz/insurance-pdf/' . $orderId);
                    $svgContent2 = QrCode::format('svg')->size(120)->generate('https://play.google.com/store/apps/details?id=com.impex_insurance.ionline&pcampaignid=web_share');

                    $qrBase641 = 'data:image/svg+xml;base64,' . base64_encode($svgContent1);
                    $qrBase642 = 'data:image/svg+xml;base64,' . base64_encode($svgContent2);

                    // Logoni base64 formatga o‘tkazish
                    $logoPath = public_path('page/assets/images/logo.svg');
                    $logoBase64 = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));

                    $policyProgramm = match ($order->confirm_payed_data['policy']['program']) {
                        1 => 'STANDARD',
                        2 => 'VOYAGE',
                        3 => 'MAXIMUM',
                        4 => 'STANDARD / COVID-19',
                        5 => 'VOYAGE / COVID-19',
                        6 => 'MAXIMUM / COVID-19',
                        default => 'OTHER',
                    };
                    // PDF yaratish
                    $htmlContent = view('travel.pdf', compact('order', 'country', 'qrBase641', 'qrBase642', 'logoBase64', 'policyProgramm'))->render();
                    $pdf = Pdf::loadHTML($htmlContent)->output();

                    $pdfFileName = 'travel_insurance_' . $orderId . '_' . time() . '.pdf';

                    Storage::disk('public')->put('pdfs/' . $pdfFileName, $pdf);

                    // Ma’lumotlarni DBga saqlash (endi QR fayl yo'q, base64 orqali ishlatyapsiz)
                    $travelPdf = new TravelPdf();
                    $travelPdf->order_id = $orderId;
                    $travelPdf->pdf = $pdfFileName;
                    $travelPdf->qr_code1 = $qrBase641; // yoki base64 stringni saqlamoqchi bo‘lsangiz: $qrBase641
                    $travelPdf->qr_code2 = $qrBase642; // yoki $qrBase642
                    $travelPdf->save();
                }


                return response()->json([
                    'success' => true,
                    'message' => 'Travel uchun maxsus tasdiq. /page/assets/images/logo.svg',
                    'data' => $responseTravelData,
                ]);
            } elseif ($completed_order->product_name === 'osago') {
                // ✅ OSAGO bo'lsa confirm_payed_data ni olib yuboramiz
                $osagoInfo = $completed_order->confirm_payed_data; // to'g'ridan-to'g'ri array

                $osagoResponse = Http::post('https://impex-insurance.uz/api/osago/confirm-payed', $osagoInfo);

                $responseOsagoData = $osagoResponse->json(); // Javobni array sifatida olamiz
                // 2. Javobdagi seria va number ni olish
                $click_service_id = $completed_order->id ?? 'AD';
                $newSeria = $responseOsagoData['response']['result']['seria'] ?? 'AD';
                $newNumber = $responseOsagoData['response']['result']['number'] ?? '12345378';

                // 3. Eski insurance_id ni olish va massivga aylantirish
                $ais = json_decode($completed_order->insurance_id, true);

                // 4. Agar yangi qiymatlar mavjud bo‘lsa, yangilash
                if ($newSeria && $newNumber) {
                    $ais['app']['click_service_id'] = $click_service_id;
                    $ais['details']['seria'] = $newSeria;
                    $ais['details']['number'] = $newNumber;
                }

                $aisResponse = Http::withBasicAuth('ESHOP', 'Fg5tVo0M5wv8')
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post('http://impexonline.uz/ords/ins/eshop/osago', $ais);

                $aisData = $aisResponse->json(); // javobni json formatda olish

                $completed_order->update([
                    'insurance_id' => json_encode($aisData),
                    'confirm_payed_response_data' => $responseOsagoData,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'OSAGO uchun maxsus tasdiq.',
                    'data' => $responseOsagoData,
                ]);
            } else {
                // 👇 Boshqa product_name bo'lsa mana endi senga kerakli kod kiradi
                $polisUuid = $completed_order->insurances_response_data['response']['result']['policies'][0]['uuid'] ?? null;
                $insurancesData = $completed_order->insurances_data;

                $startDate = $insurancesData[0]['policies'][0]['startDate'] ?? null;
                $endDate = $insurancesData[0]['policies'][0]['endDate'] ?? null;
                $info = [
                    "polisUuid" => $polisUuid,
                    "paidAt" => now()->toDateTimeString(),
                    "insurancePremium" => $completed_order->amount,
                    "startDate" => $startDate,
                    "endDate" => $endDate,
                    "comission" => 0,
                    "agencyId" => "28"
                ];

                $response = Http::post('https://impex-insurance.uz/api/confirm-payed', $info);
                $responseData = $response->json();

                $completed_order->update([
                    'confirm_payed_data' => $info,
                    'confirm_payed_response_data' => $responseData,
                ]);

                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment confirmed successfully',
                        'data' => $responseData,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'API request failed',
                        'status' => $response->status(),
                        'error' => $responseData,
                    ], $response->status());
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while confirming payment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
