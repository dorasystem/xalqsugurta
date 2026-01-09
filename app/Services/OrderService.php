<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

final class OrderService
{
    /**
     * Create a new order
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
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
