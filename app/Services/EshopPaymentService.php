<?php

namespace App\Services;

use App\Exceptions\ProviderException;
use App\Models\Order;
use App\Services\Provider\ProviderApiTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * eshop contracts (OSGOP, OSGOR, accident, tourist) paid through the site's own Payme / Click:
 * the insurer learns about the payment from eshop/payment. Called after the payment callback
 * and from the admin order card ("To'lovni tasdiqlash").
 */
final class EshopPaymentService
{
    use ProviderApiTrait;

    public function confirm(Order $order): bool
    {
        if (!$order->awaitsPaymentConfirmation()) {
            return false;
        }

        $body = $this->body($order);

        if ($body['contract_id'] === null) {
            Log::error('Eshop payment: contract_id missing', ['order_id' => $order->id]);

            return false;
        }

        try {
            $response = ApiLogger::forOrder($order, $order->product_key, fn () => $this->confirmEshopPayment($body));
        } catch (ProviderException $e) {
            Log::error('Eshop payment confirmation failed', ['order_id' => $order->id, 'message' => $e->getMessage()]);

            return false;
        }

        $order->update([
            'insurances_response_data' => array_merge($order->insurances_response_data ?? [], array_filter([
                'payment_confirm'      => $response,
                'payment_confirmed_at' => now()->toIso8601String(),
                // Policy fields, if the confirmation returns them (the insurer has not given a sample)
                'download_url'         => $response['download_url'] ?? null,
                'polis_sery'           => $response['polis_sery'] ?? null,
                'polis_number'         => $response['polis_number'] ?? null,
                'polis_check'          => $response['polis_check'] ?? null,
            ], fn ($v) => $v !== null)),
        ]);

        Log::info('Eshop payment confirmed', ['order_id' => $order->id]);

        return true;
    }

    /** The request body; contract_id is null when the sale response had none */
    public function body(Order $order): array
    {
        $response = $order->insurances_response_data ?? [];
        $data     = $order->insurances_data ?? [];
        $id       = $response['contract_id'] ?? $response['id'] ?? null;

        $number = $response['contract_number']
            ?? $response['number']
            ?? (isset($response['polis_sery'], $response['polis_number']) ? $response['polis_sery'] . '-' . $response['polis_number'] : null)
            ?? $data['contract_number']
            ?? $data['calculation']['contract_number']
            ?? $order->insurance_id;

        $start = $order->contractStartDate ?? now();

        return [
            'contract_date'   => ($order->created_at ?? now())->format('d.m.Y'),
            'contract_id'     => is_numeric($id) ? (int) $id : null,
            'contract_number' => (string) $number,
            'e_date'          => ($order->contractEndDate ?? Carbon::parse($start)->addYear()->subDay())->format('d.m.Y'),
            'payment_date'    => now()->format('d.m.Y'),
            's_date'          => Carbon::parse($start)->format('d.m.Y'),
        ];
    }
}
