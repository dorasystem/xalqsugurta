<?php

namespace App\Traits;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait ConfirmPayment
{
    /**
     * Confirm payment with Xalq Sugurta PerformTransactionRequest.
     * Dispatches to product-specific methods for gas, property, kasko.
     * Falls back to response-data detection if _product_key is missing.
     */
    public function confirmXalqSugurtaPayment(Order $order, ?string $productKey): void
    {
        Log::info("ConfirmPayment: order #{$order->id}, product_key='{$productKey}'");

        // Fallback: detect Xalq Sugurta product by API response signature
        if ($productKey === null || !in_array($productKey, ['gas', 'property', 'kasko'], true)) {
            $responseData = $order->insurances_response_data ?? [];
            // Detect by API response fields (polis_sery/polis_number = Xalq Sugurta format)
            // or by contract_id + insurance_id prefix (gas_, prop_, kasko_ = uniqid fallback)
            $insuranceId = $order->insurance_id ?? '';
            $isXalqByPrefix = preg_match('/^(gas_|prop_|kasko_)/i', $insuranceId);

            $isXalq = isset($responseData['polis_sery'])
                || isset($responseData['polis_number'])
                || (isset($responseData['contract_id']) && $isXalqByPrefix);

            if ($isXalq) {
                Log::warning("ConfirmPayment: _product_key missing for order #{$order->id}, detected as Xalq Sugurta via response data.");
                $this->sendXalqPerformTransactionRequest($order, $productKey ?? 'unknown');
            } else {
                Log::info("ConfirmPayment: order #{$order->id} skipped (product_key='{$productKey}', not a Xalq Sugurta product).");
            }
            return;
        }

        match ($productKey) {
            'gas'      => $this->confirmGasPayment($order),
            'property' => $this->confirmPropertyPayment($order),
            'kasko'    => $this->confirmKaskoPayment($order),
        };
    }

    protected function confirmGasPayment(Order $order): void
    {
        $this->sendXalqPerformTransactionRequest($order, 'gas');
    }

    protected function confirmPropertyPayment(Order $order): void
    {
        $this->sendXalqPerformTransactionRequest($order, 'property');
    }

    protected function confirmKaskoPayment(Order $order): void
    {
        $this->sendXalqPerformTransactionRequest($order, 'kasko');
    }

    /**
     * Send PerformTransactionRequest to Xalq Sugurta API.
     */
    protected function sendXalqPerformTransactionRequest(Order $order, string $productKey): void
    {
        try {
            $requestData = $this->buildXalqPerformTransactionRequestData($order);

            if ($requestData['contract_id'] === null) {
                Log::error("Xalq Sugurta [{$productKey}]: contract_id missing for order #{$order->id}, skipping PerformTransactionRequest.");
                return;
            }

            Log::info("Xalq Sugurta [{$productKey}]: Calling PerformTransactionRequest", [
                'order_id'     => $order->id,
                'request_data' => $requestData,
            ]);

            $url = config('provider.xalq.base_url') . '/PerformTransactionRequest';
            $username = config('provider.xalq.username');
            $password = config('provider.xalq.password');

            $response = Http::withBasicAuth($username, $password)
                ->timeout(60)
                ->retry(3, 1000)
                ->post($url, $requestData);

            $responseData = $response->json() ?? [];

            if ($response->successful() && ($responseData['result'] ?? -1) === 0) {
                Log::info("Xalq Sugurta [{$productKey}]: PerformTransactionRequest successful", [
                    'order_id' => $order->id,
                    'response' => $responseData,
                ]);

                // Save polis data to order so user can see download link
                $existing = $order->insurances_response_data ?? [];
                $order->update([
                    'insurances_response_data' => array_merge($existing, [
                        'perform_response' => $responseData,
                        'download_url'     => $responseData['download_url'] ?? null,
                        'polis_sery'       => $responseData['polis_sery']   ?? null,
                        'polis_number'     => $responseData['polis_number'] ?? null,
                        'polis_check'      => $responseData['polis_check']  ?? null,
                    ]),
                ]);
            } else {
                Log::error("Xalq Sugurta [{$productKey}]: PerformTransactionRequest failed", [
                    'order_id' => $order->id,
                    'status'   => $response->status(),
                    'response' => $responseData,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Xalq Sugurta [{$productKey}]: Error calling PerformTransactionRequest", [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build request payload for PerformTransactionRequest.
     */
    protected function buildXalqPerformTransactionRequestData(Order $order): array
    {
        $responseData  = $order->insurances_response_data ?? [];
        $insurancesData = $order->insurances_data ?? [];

        // contract_id: prefer API response, no reliable fallback if missing
        $contractId = $responseData['contract_id']
            ?? $responseData['id']
            ?? null;

        // contract_number: prefer polis_sery-polis_number from response,
        // then xalq_contract_number saved from API request body,
        // then insurance_id (which may hold the polis data too)
        $contractNumber = (isset($responseData['polis_sery'], $responseData['polis_number']))
            ? $responseData['polis_sery'] . '-' . $responseData['polis_number']
            : ($insurancesData['xalq_contract_number'] ?? $order->insurance_id ?? (string) $order->id);

        $startDate = $order->contractStartDate
            ? Carbon::parse($order->contractStartDate)->format('d.m.Y')
            : now()->format('d.m.Y');

        $endDate = $order->contractEndDate
            ? Carbon::parse($order->contractEndDate)->format('d.m.Y')
            : now()->addYear()->format('d.m.Y');

        return [
            'contract_date' => $startDate,
            'contract_id' => (int) $contractId,
            'contract_number' => $contractNumber,
            'e_date' => $endDate,
            'payment_date' => now()->format('d.m.Y'),
            's_date' => $startDate,
        ];
    }
}
