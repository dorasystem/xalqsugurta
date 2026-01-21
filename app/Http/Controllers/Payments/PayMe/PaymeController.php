<?php

namespace App\Http\Controllers\Payments\PayMe;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\TransactionResource;
use Carbon\Carbon;

class PaymeController extends Controller
{
    public function payment(Request $request)
    {
        // dd($request->all());
        $order = Order::findOrFail($request->id);
        $amount = $order->amount;
        $transactionId = $order->id;
        // $backUrl = route('show.insurance.pdf', ['id' => $order->id]);

        $merchantId = '68f7581688f28864c066266f';
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
                $completed_order->status = Order::STATUS_PAID;

                // Call third-party API for gas balloon insurance
                // Check if this is a gas balloon order by comparing product_name in all languages
                $isGasBalloon = false;
                $gasProductNames = [
                    __('insurance.gas.product_name', [], 'uz'), // "Gaz balon sug'urtasi"
                    __('insurance.gas.product_name', [], 'ru'), // "Страхование газового баллона"
                    __('insurance.gas.product_name', [], 'en'), // "Gas Balloon Insurance"
                ];

                if (in_array($completed_order->product_name, $gasProductNames, true)) {
                    $isGasBalloon = true;
                }

                // Also check insuranceProductName field as fallback
                if (!$isGasBalloon && $completed_order->insuranceProductName) {
                    if (in_array($completed_order->insuranceProductName, $gasProductNames, true)) {
                        $isGasBalloon = true;
                    }
                }

                if ($isGasBalloon) {
                    $this->confirmGasBalloonPayment($completed_order);
                }

                $completed_order->update();

                // Return JSON-RPC 2.0 format response
                $response = [
                    'jsonrpc' => '2.0',
                    'id' => $req->id,
                    'result' => [
                        'transaction' => strval($transaction->id),
                        'perform_time' => intval($transaction->perform_time_unix),
                        'state' => intval($transaction->state)
                    ]
                ];
                return response()->json($response);
            } elseif ($transaction->state == 2) {
                $response = [
                    'jsonrpc' => '2.0',
                    'id' => $req->id,
                    'result' => [
                        'transaction' => strval($transaction->id),
                        'perform_time' => intval($transaction->perform_time_unix),
                        'state' => intval($transaction->state)
                    ]
                ];
                return response()->json($response);
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
                $order->update(['status' => Order::STATUS_CANCELLED]);
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
                $order->update(['status' => Order::STATUS_CANCELLED]);
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

    /**
     * Confirm gas balloon payment with third-party API
     */
    private function confirmGasBalloonPayment(Order $order): void
    {
        try {
            $insurancesData = $order->insurances_data ?? [];
            $insurancesResponseData = $order->insurances_response_data ?? [];

            // Extract contract data
            $loanInfo = $insurancesData['loan_info'] ?? [];

            // Get contract_date from loan_info or contractStartDate
            $contractDate = $loanInfo['contract_date']
                ?? ($order->contractStartDate ? Carbon::parse($order->contractStartDate)->format('d.m.Y') : null);

            // Get s_date (start date) from loan_info or contractStartDate
            $sDate = $loanInfo['s_date']
                ?? ($order->contractStartDate ? Carbon::parse($order->contractStartDate)->format('d.m.Y') : null);

            // Get e_date (end date) from loan_info or contractEndDate
            $eDate = $loanInfo['e_date']
                ?? ($order->contractEndDate ? Carbon::parse($order->contractEndDate)->format('d.m.Y') : null);

            // Get contract_id from response data or use order id
            $contractId = $insurancesResponseData['contract_id']
                ?? $insurancesResponseData['id']
                ?? $order->id;

            // Get contract_number from polis_sery + polis_number or insurance_id
            $contractNumber = null;
            if (isset($insurancesResponseData['polis_sery']) && isset($insurancesResponseData['polis_number'])) {
                $contractNumber = $insurancesResponseData['polis_sery'] . '-' . $insurancesResponseData['polis_number'];
            } else {
                $contractNumber = $order->insurance_id ?? (string) $order->id;
            }

            // Payment date is current date
            $paymentDate = now()->format('d.m.Y');

            // Prepare request data
            $requestData = [
                'contract_date' => $contractDate ?? now()->format('d.m.Y'),
                'contract_id' => (int) $contractId,
                'contract_number' => $contractNumber,
                'e_date' => $eDate ?? now()->addYear()->format('d.m.Y'),
                'payment_date' => $paymentDate,
                's_date' => $sDate ?? now()->format('d.m.Y'),
            ];

            Log::info('Gas balloon: Calling PerformTransactionRequest API', [
                'order_id' => $order->id,
                'request_data' => $requestData,
            ]);

            // Call third-party API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode('gazballonsayt:dorasystem1'),
            ])->timeout(60)
                ->retry(3, 1000)
                ->post('http://online.xalqsugurta.uz/xs/ins/unv/gazballonsayt/PerformTransactionRequest', $requestData);

            if ($response->successful()) {
                Log::info('Gas balloon: PerformTransactionRequest successful', [
                    'order_id' => $order->id,
                    'response' => $response->json(),
                ]);
            } else {
                Log::error('Gas balloon: PerformTransactionRequest failed', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Gas balloon: Error calling PerformTransactionRequest', [
                'order_id' => $order->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
