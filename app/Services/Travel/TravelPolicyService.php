<?php

namespace App\Services\Travel;

use App\Exceptions\ProviderException;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Travel orders are paid through the insurer's own Payme / Click links, so the site hears about
 * the payment only from `get`: once it lists an issued policy the order is paid and gets the PDF.
 * Called from the payment page, the return address and `travel:sync-policies`.
 */
final class TravelPolicyService
{
    /** policy status "issued" in `get` */
    private const ISSUED = 2;

    public function __construct(private readonly TravelApi $api) {}

    /** True when the order now has its policy */
    public function refresh(Order $order): bool
    {
        $contractId = (int) ($order->insurances_response_data['contract_id'] ?? 0);

        if ($order->product_key !== 'travel' || $contractId <= 0 || !$this->api->canReadPolicies()) {
            return false;
        }

        if (!empty($order->insurances_response_data['download_url'])) {
            return true;
        }

        try {
            $answer = $this->api->policies($contractId);
        } catch (ProviderException $e) {
            Log::warning('Travel policy lookup failed', ['order_id' => $order->id, 'code' => $e->getCode()]);

            return false;
        }

        $policy = collect($answer['polis'] ?? [])->first(fn ($p) => is_array($p) && (int) ($p['polis_status_id'] ?? 0) === self::ISSUED);

        if (!$policy) {
            return false;
        }

        $order->insurances_response_data = array_merge($order->insurances_response_data ?? [], array_filter([
            'polis_sery'      => $policy['polis_sery'] ?? null,
            'polis_number'    => isset($policy['polis_number']) ? (string) $policy['polis_number'] : null,
            'download_url'    => $policy['polis_url'] ?? null,
            'policy_found_at' => now()->toDateTimeString(),
        ]));
        $order->status = Order::STATUS_PAID;
        $order->save();

        Log::info('Travel policy issued', ['order_id' => $order->id, 'contract_id' => $contractId]);

        return true;
    }
}
