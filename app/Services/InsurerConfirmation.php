<?php

namespace App\Services;

use App\Models\Order;

/**
 * What the insurer must hear after a payment through the site's own Payme / Click:
 * gas / property / KASKO → PerformTransactionRequest (issues the policy),
 * OSGOP / OSGOR / accident / tourist → eshop/payment, OSAGO → ERSP payment confirmation.
 */
final class InsurerConfirmation
{
    public function __construct(
        private readonly XalqPolicyService $policies,
        private readonly EshopPaymentService $eshop,
        private readonly OsagoPaymentService $osago,
    ) {}

    public function send(Order $order): bool
    {
        return match (true) {
            $order->awaitsPolicy()              => $this->policies->retry($order),
            $order->awaitsPaymentConfirmation() => $order->product_key === 'osago' ? $this->osago->confirm($order) : $this->eshop->confirm($order),
            default                             => false,
        };
    }

    /** Runs send() after the HTTP response: the insurer can take a minute, longer than Payme / Click wait */
    public static function afterResponse(Order $order): void
    {
        if (!$order->awaitsInsurer()) {
            return;
        }

        $orderId = $order->id;
        dispatch(fn () => app(self::class)->send(Order::findOrFail($orderId)))->afterResponse();
    }
}
