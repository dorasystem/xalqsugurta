<?php

namespace App\Services;

use App\Models\ClickUz;
use App\Models\Order;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * OSAGO paid through the site's own Payme / Click: the payment is confirmed to the ERSP
 * (e-osgo) by polisUuid. The address is set in the admin panel (Tizim → Sug'urtachi API) or
 * INSURANCE_OSAGO_PAYMENT_URL; while it is empty nothing is sent and the order waits.
 */
final class OsagoPaymentService
{
    public function configured(): bool
    {
        return filled(config('services.insurance.osago.payment_url'));
    }

    public function confirm(Order $order): bool
    {
        if (!$order->awaitsPaymentConfirmation() || $order->product_key !== 'osago') {
            return false;
        }

        if (!$this->configured()) {
            Log::warning('OSAGO payment confirmation: URL not configured', ['order_id' => $order->id]);

            return false;
        }

        $body = $this->body($order);

        if ($body['polisUuid'] === '') {
            Log::error('OSAGO payment confirmation: polisUuid missing', ['order_id' => $order->id]);

            return false;
        }

        // The transaction UUID stays the same across retries, so a repeat is recognisable
        $this->remember($order, ['transaction_id' => $body['transactionId']]);

        try {
            $response = ApiLogger::forOrder($order, 'osago', fn () => $this->request()
                ->withBody(json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'application/json')
                ->post((string) config('services.insurance.osago.payment_url')));
        } catch (ConnectionException $e) {
            Log::error('OSAGO payment confirmation: connection error', ['order_id' => $order->id, 'message' => $e->getMessage()]);

            return false;
        }

        $data = $response->json() ?? [];

        if (!$response->successful() || !self::accepted($data)) {
            Log::error('OSAGO payment confirmation rejected', ['order_id' => $order->id, 'status' => $response->status()]);

            return false;
        }

        $this->remember($order, ['response' => $data], confirmed: true);

        Log::info('OSAGO payment confirmed', ['order_id' => $order->id]);

        return true;
    }

    /** Body of the ERSP payment confirmation (strings, dates Y-m-d, paidAt Y-m-d H:i:s) */
    public function body(Order $order): array
    {
        $response = $order->insurances_response_data ?? [];
        $start    = Carbon::parse($order->contractStartDate ?? now());

        return array_filter([
            'polisUuid'        => (string) ($response['UUID'] ?? $response['uuid'] ?? $order->insurance_id ?? ''),
            'paidAt'           => $this->paidAt($order)->format('Y-m-d H:i:s'),
            'insurancePremium' => (string) (int) $order->amount,
            'startDate'        => $start->format('Y-m-d'),
            'endDate'          => Carbon::parse($order->contractEndDate ?? $start->copy()->addYear()->subDay())->format('Y-m-d'),
            'agencyId'         => filled(config('services.insurance.osago.agency_id')) ? (string) config('services.insurance.osago.agency_id') : null,
            'transactionId'    => $response['osago_payment']['transaction_id'] ?? (string) Str::uuid(),
        ], fn ($v) => $v !== null);
    }

    /** error / result 0 (ERSP answers {error: 0, result: {...}}, the insurer's own API {result: 0}) */
    private static function accepted(array $data): bool
    {
        $code = $data['error'] ?? (is_scalar($data['result'] ?? null) ? $data['result'] : 0);

        return (string) $code === '0';
    }

    /** When the customer paid: the Click / Payme record, else now */
    private function paidAt(Order $order): Carbon
    {
        $click = ClickUz::where('merchant_trans_id', (string) $order->id)->where('status', ClickUz::STATUS_PAID)->value('completed_at');
        if ($click) {
            return Carbon::parse($click);
        }

        $payme = Transaction::where('order_id', $order->id)->where('state', 2)->value('perform_time');

        return $payme ? Carbon::parse($payme) : now();
    }

    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        $request = Http::timeout(30)->retry(2, 1000, throw: false)->acceptJson();
        $token   = config('services.insurance.osago.payment_token');

        return filled($token)
            ? $request->withToken((string) $token)
            : $request->withBasicAuth((string) config('services.insurance.osago.username'), (string) config('services.insurance.osago.password'));
    }

    private function remember(Order $order, array $values, bool $confirmed = false): void
    {
        $data = $order->insurances_response_data ?? [];
        $data['osago_payment'] = array_merge($data['osago_payment'] ?? [], $values);

        if ($confirmed) {
            $data['payment_confirmed_at'] = now()->toIso8601String();
        }

        $order->update(['insurances_response_data' => $data]);
    }
}
