<?php

namespace App\Services;

use App\Models\Order;
use App\Traits\ConfirmPayment;

/**
 * Re-sends PerformTransactionRequest for a paid order whose policy did not arrive
 * (admin panel: order card → "Polisni qayta so'rash"). Same code path as the payment callback.
 */
final class XalqPolicyService
{
    use ConfirmPayment;

    public function retry(Order $order): bool
    {
        if (!$order->awaitsPolicy()) {
            return false;
        }

        return $this->sendXalqPerformTransactionRequest($order, $order->product_key);
    }
}
