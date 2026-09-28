<?php

namespace App\Services\Payments;

use App\Models\ClickUz;
use App\Models\Order;
use App\Services\XalqPolicyService;
use Illuminate\Support\Facades\Log;

/**
 * Click SHOP API (https://docs.click.uz/en/shop-api/requests): Prepare (action 0) and
 * Complete (action 1) callbacks. Every request is authenticated by
 *   md5(click_trans_id + service_id + SECRET_KEY + merchant_trans_id [+ merchant_prepare_id] + amount + action + sign_time)
 * merchant_trans_id is our order id; merchant_prepare_id is the click_uzs row id.
 */
final class ClickShopApi
{
    public const OK                    = 0;
    public const SIGN_CHECK_FAILED     = -1;
    public const INCORRECT_AMOUNT      = -2;
    public const ACTION_NOT_FOUND      = -3;
    public const ALREADY_PAID          = -4;
    public const ORDER_NOT_FOUND       = -5;
    public const TRANSACTION_NOT_FOUND = -6;
    public const REQUEST_ERROR         = -8;
    public const TRANSACTION_CANCELLED = -9;

    private const NOTES = [
        self::OK                    => 'Success',
        self::SIGN_CHECK_FAILED     => 'SIGN CHECK FAILED!',
        self::INCORRECT_AMOUNT      => 'Incorrect parameter amount',
        self::ACTION_NOT_FOUND      => 'Action not found',
        self::ALREADY_PAID          => 'Already paid',
        self::ORDER_NOT_FOUND       => 'User does not exist',
        self::TRANSACTION_NOT_FOUND => 'Transaction does not exist',
        self::REQUEST_ERROR         => 'Error in request from click',
        self::TRANSACTION_CANCELLED => 'Transaction cancelled',
    ];

    // ─── Prepare ──────────────────────────────────────────────────────────────

    public function prepare(array $p): array
    {
        $base = ['click_trans_id' => $p['click_trans_id'] ?? null, 'merchant_trans_id' => $p['merchant_trans_id'] ?? null];

        if ($error = $this->rejectRequest($p, 0)) {
            return $base + $this->error($error);
        }

        $order = Order::find($p['merchant_trans_id']);
        if (!$order) {
            return $base + $this->error(self::ORDER_NOT_FOUND);
        }
        if ($order->status === Order::STATUS_PAID) {
            return $base + $this->error(self::ALREADY_PAID);
        }
        if ($order->status === Order::STATUS_CANCELLED) {
            return $base + $this->error(self::TRANSACTION_CANCELLED);
        }
        if (!$this->sameAmount($p['amount'], $order->amount)) {
            return $base + $this->error(self::INCORRECT_AMOUNT);
        }

        // Click may repeat Prepare for the same transaction: answer with the same prepare id
        $transaction = ClickUz::firstOrCreate(
            ['click_trans_id' => (string) $p['click_trans_id'], 'merchant_trans_id' => (string) $order->id],
            [
                'click_paydoc_id' => $p['click_paydoc_id'] ?? null,
                'amount'          => $p['amount'],
                'sign_time'       => $p['sign_time'],
                'situation'       => $p['error'] ?? 0,
                'status'          => ClickUz::STATUS_PREPARED,
            ],
        );

        if ($transaction->status === ClickUz::STATUS_CANCELLED) {
            return $base + $this->error(self::TRANSACTION_CANCELLED);
        }

        return $base + ['merchant_prepare_id' => $transaction->id] + $this->error(self::OK);
    }

    // ─── Complete ─────────────────────────────────────────────────────────────

    public function complete(array $p): array
    {
        $base = ['click_trans_id' => $p['click_trans_id'] ?? null, 'merchant_trans_id' => $p['merchant_trans_id'] ?? null];

        if ($error = $this->rejectRequest($p, 1)) {
            return $base + $this->error($error);
        }

        $order = Order::find($p['merchant_trans_id']);
        if (!$order) {
            return $base + $this->error(self::ORDER_NOT_FOUND);
        }

        $transaction = ClickUz::whereKey($p['merchant_prepare_id'] ?? 0)
            ->where('click_trans_id', (string) $p['click_trans_id'])
            ->where('merchant_trans_id', (string) $order->id)
            ->first();
        if (!$transaction) {
            return $base + $this->error(self::TRANSACTION_NOT_FOUND);
        }

        $confirm = ['merchant_confirm_id' => $transaction->id];

        if ($transaction->status === ClickUz::STATUS_CANCELLED) {
            return $base + $confirm + $this->error(self::TRANSACTION_CANCELLED);
        }

        // A negative error from Click means the payment failed: cancel it and answer -9
        if ((int) ($p['error'] ?? 0) < 0) {
            return $base + $confirm + $this->cancel($transaction, $order, (int) $p['error']);
        }

        if ($transaction->status === ClickUz::STATUS_PAID || $order->status === Order::STATUS_PAID) {
            return $base + $confirm + $this->error(self::ALREADY_PAID);
        }
        if (!$this->sameAmount($p['amount'], $order->amount)) {
            return $base + $confirm + $this->error(self::INCORRECT_AMOUNT);
        }

        $transaction->update(['status' => ClickUz::STATUS_PAID, 'situation' => 1, 'completed_at' => now()]);
        $order->update(['status' => Order::STATUS_PAID, 'payment_type' => Order::PAYMENT_CLICK]);

        Log::info('Click payment completed', ['order_id' => $order->id, 'click_trans_id' => $transaction->click_trans_id]);

        // Ask the insurer for the policy after Click has its answer: PerformTransactionRequest
        // can take up to a minute with retries, longer than Click waits for Complete
        if ($order->awaitsPolicy()) {
            $orderId = $order->id;
            dispatch(fn () => app(XalqPolicyService::class)->retry(Order::findOrFail($orderId)))->afterResponse();
        }

        return $base + $confirm + $this->error(self::OK);
    }

    // ─── Internals ────────────────────────────────────────────────────────────

    /** Error code for a request that fails the signature, service or action check; null when it passes */
    private function rejectRequest(array $p, int $action): ?int
    {
        foreach (['click_trans_id', 'service_id', 'merchant_trans_id', 'amount', 'action', 'sign_time', 'sign_string'] as $field) {
            if (!isset($p[$field]) || $p[$field] === '') {
                return self::REQUEST_ERROR;
            }
        }

        $secret = (string) config('services.click.secret_key');
        if ($secret === '') {
            Log::error('Click callback rejected: CLICK_SECRET_KEY is not set');

            return self::SIGN_CHECK_FAILED;
        }

        $expected = md5(
            $p['click_trans_id'] . $p['service_id'] . $secret . $p['merchant_trans_id']
            . ($action === 1 ? ($p['merchant_prepare_id'] ?? '') : '')
            . $p['amount'] . $p['action'] . $p['sign_time']
        );

        if (!hash_equals($expected, strtolower((string) $p['sign_string']))) {
            Log::warning('Click callback with a bad signature', ['click_trans_id' => $p['click_trans_id'], 'merchant_trans_id' => $p['merchant_trans_id']]);

            return self::SIGN_CHECK_FAILED;
        }

        $serviceId = (string) config('services.click.service_id');
        if ($serviceId !== '' && (string) $p['service_id'] !== $serviceId) {
            return self::SIGN_CHECK_FAILED;
        }

        return (int) $p['action'] === $action ? null : self::ACTION_NOT_FOUND;
    }

    private function cancel(ClickUz $transaction, Order $order, int $clickError): array
    {
        if ($transaction->status === ClickUz::STATUS_PAID) {
            // Click reverses a payment we already confirmed: needs a person (the policy may be issued)
            Log::warning('Click cancelled an already completed payment', ['order_id' => $order->id, 'click_error' => $clickError]);
        } else {
            $transaction->update(['status' => ClickUz::STATUS_CANCELLED, 'situation' => $clickError]);

            if ($order->status !== Order::STATUS_PAID) {
                $order->update(['status' => Order::STATUS_CANCELLED]);
            }
        }

        return $this->error(self::TRANSACTION_CANCELLED);
    }

    private function sameAmount(mixed $clickAmount, mixed $orderAmount): bool
    {
        return is_numeric($clickAmount) && abs((float) $clickAmount - (float) $orderAmount) < 0.01;
    }

    private function error(int $code): array
    {
        return ['error' => $code, 'error_note' => self::NOTES[$code]];
    }
}
