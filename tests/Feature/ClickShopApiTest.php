<?php

namespace Tests\Feature;

use App\Models\ApiLog;
use App\Models\ClickUz;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** The 15 scenarios of Click's SHOP API test collection (docs.click.uz → Testing → Postman) */
class ClickShopApiTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET  = 'test-secret';
    private const SERVICE = '12345';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.click.secret_key' => self::SECRET,
            'services.click.service_id' => self::SERVICE,
            'provider.xalq.base_url'    => 'http://online.xalqsugurta.uz/xs/ins/unv/gazballonsayt',
        ]);
    }

    private function order(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'product_name'             => 'Gaz ballon',
            'amount'                   => 250000,
            'status'                   => Order::STATUS_NEW,
            'insurance_id'             => 'GAS-1',
            'phone'                    => '998901234567',
            'insurances_data'          => ['_product_key' => 'gas'],
            'insurances_response_data' => ['contract_id' => 884213],
            'contractStartDate'        => '2026-10-01',
            'contractEndDate'          => '2027-09-30',
        ], $attributes));
    }

    private function params(Order|int $order, int $action, array $overrides = []): array
    {
        $p = array_merge([
            'click_trans_id'    => '134',
            'service_id'        => self::SERVICE,
            'click_paydoc_id'   => '1344',
            'merchant_trans_id' => (string) ($order instanceof Order ? $order->id : $order),
            'amount'            => '250000',
            'action'            => (string) $action,
            'error'             => '0',
            'error_note'        => 'Success',
            'sign_time'         => '2026-09-28 12:00:00',
        ], $overrides);

        $p['sign_string'] ??= md5(
            $p['click_trans_id'] . $p['service_id'] . self::SECRET . $p['merchant_trans_id']
            . ($action === 1 ? ($p['merchant_prepare_id'] ?? '') : '')
            . $p['amount'] . $p['action'] . $p['sign_time']
        );

        return $p;
    }

    private function prepare(Order|int $order, array $overrides = []): array
    {
        return $this->post('/api/prepare', $this->params($order, 0, $overrides))->assertOk()->json();
    }

    private function complete(Order $order, int $prepareId, array $overrides = []): array
    {
        return $this->post('/api/complete', $this->params($order, 1, ['merchant_prepare_id' => (string) $prepareId] + $overrides))->assertOk()->json();
    }

    // ─── Group 1: Prepare ─────────────────────────────────────────────────────

    public function test_prepare_rejects_a_bad_signature(): void
    {
        $response = $this->prepare($this->order(), ['sign_string' => md5('forged')]);

        $this->assertSame(-1, $response['error']);
        $this->assertSame(0, ClickUz::count());
    }

    public function test_prepare_rejects_an_unknown_order(): void
    {
        $this->assertSame(-5, $this->prepare(999999)['error']);
    }

    public function test_prepare_succeeds_and_is_repeatable(): void
    {
        $order = $this->order();

        $first  = $this->prepare($order);
        $second = $this->prepare($order);

        $this->assertSame(0, $first['error']);
        $this->assertSame((string) $order->id, $first['merchant_trans_id']);
        $this->assertSame($first['merchant_prepare_id'], $second['merchant_prepare_id']);
        $this->assertSame(1, ClickUz::count());
    }

    public function test_prepare_rejects_a_wrong_amount_and_a_paid_order(): void
    {
        $this->assertSame(-2, $this->prepare($this->order(), ['amount' => '300'])['error']);
        $this->assertSame(-4, $this->prepare($this->order(['status' => Order::STATUS_PAID]))['error']);
    }

    public function test_requests_are_rejected_when_the_secret_is_not_configured(): void
    {
        config(['services.click.secret_key' => null]);

        $this->assertSame(-1, $this->prepare($this->order())['error']);
    }

    // ─── Group 2: Complete validation ─────────────────────────────────────────

    public function test_complete_rejects_a_bad_signature_and_a_wrong_amount(): void
    {
        $order     = $this->order();
        $prepareId = $this->prepare($order)['merchant_prepare_id'];

        $this->assertSame(-1, $this->complete($order, $prepareId, ['sign_string' => md5('forged')])['error']);
        $this->assertSame(-2, $this->complete($order, $prepareId, ['amount' => '300'])['error']);
        $this->assertSame(Order::STATUS_NEW, $order->fresh()->status);
    }

    // ─── Group 3: full cycle ──────────────────────────────────────────────────

    public function test_complete_marks_the_order_paid_and_requests_the_policy(): void
    {
        Http::fake(['*PerformTransactionRequest' => Http::response([
            'result' => 0, 'polis_sery' => 'GB', 'polis_number' => '1', 'download_url' => 'https://example.com/p.pdf',
        ])]);
        $order     = $this->order();
        $prepareId = $this->prepare($order)['merchant_prepare_id'];

        $response = $this->complete($order, $prepareId);

        $this->assertSame(0, $response['error']);
        $this->assertSame($prepareId, $response['merchant_confirm_id']);

        $order->refresh();
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertSame(Order::PAYMENT_CLICK, $order->payment_type);
        // Sent after the response to Click
        $this->assertSame('https://example.com/p.pdf', $order->insurances_response_data['download_url']);
        $this->assertSame(1, ApiLog::where('order_id', $order->id)->count());

        // Repeat confirmation → -4; a later cancellation of the confirmed payment → -9, order stays paid
        $this->assertSame(-4, $this->complete($order, $prepareId)['error']);
        $this->assertSame(-9, $this->complete($order, $prepareId, ['error' => '-5017'])['error']);
        $this->assertSame(Order::STATUS_PAID, $order->fresh()->status);
    }

    public function test_complete_confirms_an_eshop_contract_to_the_insurer(): void
    {
        Http::fake(['*/eshop/payment' => Http::response(['result' => 0, 'result_message' => 'OK'])]);
        $order = $this->order([
            'product_name'             => 'OSGOR',
            'insurance_id'             => '553311',
            'insurances_data'          => ['_product_key' => 'osgor', 'contract_number' => '290926-1790000000'],
            'insurances_response_data' => ['result' => 0, 'contract_id' => 553311],
        ]);

        $this->complete($order, $this->prepare($order)['merchant_prepare_id']);

        $body = Http::recorded()[0][0]->data();
        $this->assertSame(553311, $body['contract_id']);
        $this->assertSame('290926-1790000000', $body['contract_number']);
        $this->assertSame(['01.10.2026', '30.09.2027'], [$body['s_date'], $body['e_date']]);
        $this->assertFalse($order->fresh()->awaitsPaymentConfirmation());
    }

    // ─── Group 4: merchant_prepare_id ─────────────────────────────────────────

    public function test_complete_rejects_an_unknown_prepare_id(): void
    {
        Http::fake();
        $order     = $this->order();
        $prepareId = $this->prepare($order)['merchant_prepare_id'];

        $this->assertSame(-6, $this->complete($order, 999999999)['error']);
        $this->assertSame(0, $this->complete($order, $prepareId)['error']);
    }

    // ─── Group 5: cancellation ────────────────────────────────────────────────

    public function test_click_cancellation(): void
    {
        Http::fake();
        $order     = $this->order();
        $prepareId = $this->prepare($order)['merchant_prepare_id'];

        $this->assertSame(-9, $this->complete($order, $prepareId, ['error' => '-5017'])['error']);
        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(ClickUz::STATUS_CANCELLED, ClickUz::find($prepareId)->status);

        $this->assertSame(-9, $this->complete($order, $prepareId, ['error' => '-5017'])['error']);
        $this->assertSame(-9, $this->complete($order, $prepareId)['error']);
        Http::assertNothingSent();
    }

    // ─── Other ────────────────────────────────────────────────────────────────

    public function test_legacy_nested_request_format_is_accepted(): void
    {
        $order = $this->order();

        $response = $this->post('/api/prepare', ['Request' => $this->params($order, 0)])->assertOk()->json();

        $this->assertSame(0, $response['error']);
    }

    public function test_products_without_a_confirmation_step_are_not_sent_to_the_insurer(): void
    {
        Http::fake();
        $order     = $this->order(['insurances_data' => ['_product_key' => 'osago']]);
        $prepareId = $this->prepare($order)['merchant_prepare_id'];

        $this->assertSame(0, $this->complete($order, $prepareId)['error']);
        Http::assertNothingSent();
    }
}
