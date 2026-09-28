<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

final class OrderService
{
    /** Session key with the ids of orders created in this browser */
    public const SESSION_ORDERS = 'my_orders';

    /**
     * Create a new order
     */
    public function createOrder(array $data): Order
    {
        $order = DB::transaction(function () use ($data) {
            return Order::create([
                'product_name' => $data['product_name'] ?? 'MOL-MULK Sug\'urta',
                'amount' => $data['amount'] ?? 0,
                'state' => $data['state'] ?? 0,
                'payment_type' => null,
                'insurance_id' => $data['insurance_id'] ?? '',
                'phone' => $data['phone'] ?? null,
                'insurances_data' => $data['insurances_data'] ?? null,
                'insurances_response_data' => $data['insurances_response_data'] ?? null,
                'payme_url' => $data['payme_url'] ?? null,
                'click_url' => $data['click_url'] ?? null,
                'status' => Order::STATUS_NEW,
                'contractStartDate' => $data['contractStartDate'] ?? null,
                'contractEndDate' => $data['contractEndDate'] ?? null,
                'insuranceProductName' => $data['insuranceProductName'] ?? null,
            ]);
        });

        // The payment page shows the phone and the policy links only to the browser that made the order
        if (request()->hasSession()) {
            request()->session()->push(self::SESSION_ORDERS, $order->id);
        }

        return $order;
    }

    /** True when this visitor may see the order's personal data (its creator or a signed-in admin) */
    public function canSeeDetails(Order $order): bool
    {
        if (auth()->check()) {
            return true;
        }

        return request()->hasSession()
            && in_array($order->id, (array) request()->session()->get(self::SESSION_ORDERS, []), false);
    }

    /**
     * Get order by ID
     */
    public function getOrderById(int $orderId): ?Order
    {
        return Order::find($orderId);
    }

    public function updateOrderStatus(int $orderId, string $status): bool
    {
        $order = $this->getOrderById($orderId);
        
        if (!$order) {
            return false;
        }

        $order->update(['status' => $status]);
        
        return true;
    }

    public function updateOrderPaymentType(int $orderId, string $paymentType): bool
    {
        $order = $this->getOrderById($orderId);
        
        if (!$order) {
            return false;
        }

        $order->update(['payment_type' => $paymentType]);
        
        return true;
    }
}
