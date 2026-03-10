<?php

namespace Tests\Unit\Traits;

use App\Models\Order;
use App\Traits\ConfirmPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Concrete class to test the ConfirmPayment trait.
 */
class ConcreteConfirmPayment
{
    use ConfirmPayment;
}

class ConfirmPaymentTest extends TestCase
{
    use RefreshDatabase;

    private ConcreteConfirmPayment $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new ConcreteConfirmPayment();
    }

    private function makeOrder(array $attrs = []): Order
    {
        return Order::create(array_merge([
            'insurance_id'             => 'test_' . uniqid(),
            'product_name'             => 'Test',
            'amount'                   => 100000,
            'status'                   => Order::STATUS_PAID,
            'contractStartDate'        => '2026-03-10',
            'contractEndDate'          => '2027-03-09',
            'insurances_data'          => null,
            'insurances_response_data' => null,
        ], $attrs));
    }

    // ─── confirmXalqSugurtaPayment — known product keys ───────────────────────

    #[\PHPUnit\Framework\Attributes\DataProvider('xalqProductKeyProvider')]
    public function test_known_xalq_product_keys_trigger_http_request(string $productKey): void
    {
        Http::fake(['*' => Http::response(['result' => 0], 200)]);

        $order = $this->makeOrder([
            'insurance_id'             => $productKey . '_abc',
            'insurances_data'          => ['_product_key' => $productKey],
            'insurances_response_data' => ['contract_id' => 12345],
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, $productKey);

        Http::assertSentCount(1);
    }

    public static function xalqProductKeyProvider(): array
    {
        return [
            'gas'      => ['gas'],
            'property' => ['property'],
            'kasko'    => ['kasko'],
        ];
    }

    // ─── confirmXalqSugurtaPayment — non-Xalq products ───────────────────────

    #[\PHPUnit\Framework\Attributes\DataProvider('nonXalqProductKeyProvider')]
    public function test_non_xalq_product_keys_do_not_send_request(string $productKey): void
    {
        Http::fake();

        $order = $this->makeOrder([
            'insurances_response_data' => ['some_field' => 'value'],
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, $productKey);

        Http::assertNothingSent();
    }

    public static function nonXalqProductKeyProvider(): array
    {
        return [
            'osgop'   => ['osgop'],
            'osgor'   => ['osgor'],
            'accident'=> ['accident'],
            'tourist' => ['tourist'],
            'unknown' => ['unknown'],
        ];
    }

    public function test_null_product_key_without_xalq_response_data_skips_request(): void
    {
        Http::fake();

        $order = $this->makeOrder([
            'insurances_response_data' => null,
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, null);

        Http::assertNothingSent();
    }

    // ─── Fallback detection ───────────────────────────────────────────────────

    public function test_null_key_with_polis_sery_in_response_triggers_request(): void
    {
        Http::fake(['*' => Http::response(['result' => 0], 200)]);

        $order = $this->makeOrder([
            'insurances_response_data' => [
                'polis_sery'   => 'XS',
                'polis_number' => '12345',
                'contract_id'  => 111,
            ],
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, null);

        Http::assertSentCount(1);
    }

    public function test_null_key_with_contract_id_and_gas_prefix_triggers_request(): void
    {
        Http::fake(['*' => Http::response(['result' => 0], 200)]);

        $order = $this->makeOrder([
            'insurance_id'             => 'gas_69af50c09e82b',
            'insurances_response_data' => ['contract_id' => 2932948, 'result' => 0],
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, null);

        Http::assertSentCount(1);
    }

    public function test_null_key_with_contract_id_and_prop_prefix_triggers_request(): void
    {
        Http::fake(['*' => Http::response(['result' => 0], 200)]);

        $order = $this->makeOrder([
            'insurance_id'             => 'prop_abc123',
            'insurances_response_data' => ['contract_id' => 555],
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, null);

        Http::assertSentCount(1);
    }

    public function test_null_key_with_contract_id_but_osgop_prefix_skips_request(): void
    {
        Http::fake();

        $order = $this->makeOrder([
            'insurance_id'             => 'osgop_abc',
            'insurances_response_data' => ['contract_id' => 777],
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, null);

        Http::assertNothingSent();
    }

    // ─── contract_id missing ──────────────────────────────────────────────────

    public function test_skips_request_when_contract_id_is_missing(): void
    {
        Http::fake();
        Log::spy();

        $order = $this->makeOrder([
            'insurances_data'          => ['_product_key' => 'gas'],
            'insurances_response_data' => ['result' => 0],  // no contract_id
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, 'gas');

        Http::assertNothingSent();
        Log::shouldHaveReceived('error')->once();
    }

    // ─── buildXalqPerformTransactionRequestData ───────────────────────────────

    public function test_build_request_data_uses_polis_as_contract_number(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $order = $this->makeOrder([
            'insurances_data'          => ['_product_key' => 'gas'],
            'insurances_response_data' => [
                'contract_id'  => 100,
                'polis_sery'   => 'XS',
                'polis_number' => '99999',
            ],
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, 'gas');

        Http::assertSent(function ($request) {
            $body = $request->data();
            return $body['contract_number'] === 'XS-99999'
                && $body['contract_id'] === 100;
        });
    }

    public function test_build_request_data_falls_back_to_xalq_contract_number(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $order = $this->makeOrder([
            'insurance_id'             => 'gas_fallback',
            'insurances_data'          => [
                '_product_key'         => 'gas',
                'xalq_contract_number' => 'GAS-contract-001',
            ],
            'insurances_response_data' => ['contract_id' => 200],
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, 'gas');

        Http::assertSent(function ($request) {
            return $request->data()['contract_number'] === 'GAS-contract-001';
        });
    }

    public function test_build_request_data_uses_contract_dates_from_order(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $order = $this->makeOrder([
            'insurances_data'          => ['_product_key' => 'property'],
            'insurances_response_data' => ['contract_id' => 300],
            'contractStartDate'        => '2026-06-01',
            'contractEndDate'          => '2027-05-31',
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, 'property');

        Http::assertSent(function ($request) {
            $body = $request->data();
            return $body['s_date'] === '01.06.2026'
                && $body['e_date'] === '31.05.2027'
                && $body['contract_date'] === '01.06.2026';
        });
    }

    public function test_build_request_data_includes_payment_date_as_today(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $order = $this->makeOrder([
            'insurances_data'          => ['_product_key' => 'kasko'],
            'insurances_response_data' => ['contract_id' => 400],
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, 'kasko');

        Http::assertSent(function ($request) {
            return $request->data()['payment_date'] === now()->format('d.m.Y');
        });
    }

    // ─── HTTP success/failure handling ────────────────────────────────────────

    public function test_logs_success_when_api_returns_200(): void
    {
        Http::fake(['*' => Http::response(['result' => 0], 200)]);
        Log::spy();

        $order = $this->makeOrder([
            'insurances_data'          => ['_product_key' => 'gas'],
            'insurances_response_data' => ['contract_id' => 500],
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, 'gas');

        Log::shouldHaveReceived('info')->withArgs(fn($msg) => str_contains($msg, 'successful'));
    }

    public function test_logs_error_when_api_returns_non_200(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Bad request'], 400)]);
        Log::spy();

        $order = $this->makeOrder([
            'insurances_data'          => ['_product_key' => 'gas'],
            'insurances_response_data' => ['contract_id' => 600],
        ]);

        $this->subject->confirmXalqSugurtaPayment($order, 'gas');

        Log::shouldHaveReceived('error')->withArgs(fn($msg) => str_contains($msg, 'failed'));
    }

    public function test_does_not_throw_on_exception(): void
    {
        Http::fake(function () {
            throw new \Exception('Network error');
        });

        $order = $this->makeOrder([
            'insurances_data'          => ['_product_key' => 'gas'],
            'insurances_response_data' => ['contract_id' => 700],
        ]);

        // Should not throw
        $this->subject->confirmXalqSugurtaPayment($order, 'gas');
        $this->assertTrue(true);
    }
}
