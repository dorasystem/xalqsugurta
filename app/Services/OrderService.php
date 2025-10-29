<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class OrderService
{
    /**
     * Create a new order
     */
    public function createOrder(array $data): Order
    {

        try {
            return DB::transaction(function () use ($data) {
                $order = Order::create([
                    'product_name' => $data['product_name'] ?? 'MOL-MULK Sug\'urta',
                    'amount' => $data['amount'] ?? 0,
                    'state' => $data['state'] ?? 0,
                    'payment_type' => null, // Will be set when user selects payment method
                    'insurance_id' => $data['insurance_id'] ?? '',
                    'phone' => $data['phone'] ?? null,
                    'insurances_data' => $data['insurances_data'] ?? null,
                    'insurances_response_data' => $data['insurances_response_data'] ?? null,
                    'status' => Order::STATUS_NEW,
                    'contractStartDate' => $data['contractStartDate'] ?? null,
                    'contractEndDate' => $data['contractEndDate'] ?? null,
                    'insuranceProductName' => $data['insuranceProductName'] ?? null,
                    'polic_id_number' => $data['polic_id_number'],
                ]);

                $order->polic_id_number = $data['polic_id_number'];

                $order->save();

                Log::info('Order created successfully', [
                    'order_id' => $order->id,
                    'insurance_id' => $order->insurance_id,
                    'amount' => $order->amount,
                ]);

                return $order;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create order', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Get order by ID
     */
    public function getOrderById(int $orderId): ?Order
    {
        return Order::find($orderId);
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(int $orderId, string $status): bool
    {
        try {
            $order = $this->getOrderById($orderId);

            if (!$order) {
                throw new \Exception("Order not found: {$orderId}");
            }

            $order->update(['status' => $status]);

            Log::info('Order status updated', [
                'order_id' => $orderId,
                'status' => $status,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update order status', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Update order payment type
     */
    public function updateOrderPaymentType(int $orderId, string $paymentType): bool
    {
        try {
            $order = $this->getOrderById($orderId);

            if (!$order) {
                throw new \Exception("Order not found: {$orderId}");
            }

            $order->update(['payment_type' => $paymentType]);

            Log::info('Order payment type updated', [
                'order_id' => $orderId,
                'payment_type' => $paymentType,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update order payment type', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
