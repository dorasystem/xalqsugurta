<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\OrderResource;
use App\Filament\Admin\Resources\OrderResource\Pages\ViewOrder;
use App\Models\ClickUz;
use App\Models\Order;
use App\Models\User;
use App\Services\InsurerConfirmation;
use App\Services\OsagoPaymentService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class OsagoPaymentConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'https://erspapiv2.e-osgo.uz/api/v3/example/confirm-payment';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.insurance.osago.payment_url'   => self::URL,
            'services.insurance.osago.payment_token' => null,
            'services.insurance.osago.username'      => 'osago-user',
            'services.insurance.osago.password'      => 'osago-pass',
            'services.insurance.osago.agency_id'     => '17',
        ]);
    }

    private function paidOsago(array $response = ['result' => 0, 'UUID' => '6b4e1638-7d92-45c2-be8c-c1d650820d9c']): Order
    {
        return Order::create([
            'product_name'             => 'OSAGO',
            'amount'                   => 192000,
            'status'                   => Order::STATUS_PAID,
            'payment_type'             => Order::PAYMENT_CLICK,
            'insurance_id'             => $response['UUID'] ?? 'x',
            'phone'                    => '998901234567',
            'insurances_data'          => ['_product_key' => 'osago'],
            'insurances_response_data' => $response,
            'contractStartDate'        => '2026-10-01',
            'contractEndDate'          => '2027-09-30',
        ]);
    }

    public function test_confirmation_sends_the_ersp_body(): void
    {
        Http::fake([self::URL => Http::response(['error' => 0, 'result' => ['status' => 'ok']])]);
        $order = $this->paidOsago();
        ClickUz::create(['click_trans_id' => '1', 'merchant_trans_id' => (string) $order->id, 'amount' => 192000, 'status' => ClickUz::STATUS_PAID, 'completed_at' => '2026-09-29 10:10:10']);

        $this->assertTrue(app(InsurerConfirmation::class)->send($order));

        Http::assertSent(function (Request $request) {
            $body = $request->data();

            return $request->url() === self::URL
                && $request->hasHeader('Authorization', 'Basic ' . base64_encode('osago-user:osago-pass'))
                && $body['polisUuid'] === '6b4e1638-7d92-45c2-be8c-c1d650820d9c'
                && $body['paidAt'] === '2026-09-29 10:10:10'
                && $body['insurancePremium'] === '192000'
                && $body['startDate'] === '2026-10-01'
                && $body['endDate'] === '2027-09-30'
                && $body['agencyId'] === '17'
                && preg_match('/^[0-9a-f-]{36}$/', $body['transactionId']) === 1;
        });

        $order->refresh();
        $this->assertFalse($order->awaitsPaymentConfirmation());
        $this->assertSame('ok', OrderResource::timeline($order)[3][0]);
    }

    public function test_bearer_token_is_used_when_set(): void
    {
        config(['services.insurance.osago.payment_token' => 'secret-token']);
        Http::fake([self::URL => Http::response(['result' => 0])]);

        $this->assertTrue(app(OsagoPaymentService::class)->confirm($this->paidOsago()));

        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer secret-token'));
    }

    public function test_a_rejection_keeps_the_order_waiting_with_the_same_transaction_id(): void
    {
        Http::fake([self::URL => Http::response(['error' => 1, 'error_message' => 'Polis topilmadi'])]);
        $order = $this->paidOsago();

        $this->assertFalse(app(OsagoPaymentService::class)->confirm($order));
        $first = $order->fresh()->insurances_response_data['osago_payment']['transaction_id'];
        $this->assertFalse(app(OsagoPaymentService::class)->confirm($order->fresh()));

        $ids = collect(Http::recorded())->map(fn ($pair) => $pair[0]->data()['transactionId'])->unique();
        $this->assertSame([$first], $ids->values()->all());
        $this->assertTrue($order->fresh()->awaitsPaymentConfirmation());
    }

    public function test_nothing_is_sent_without_a_url_and_the_admin_sees_why(): void
    {
        config(['services.insurance.osago.payment_url' => null]);
        Http::fake();
        $order = $this->paidOsago();

        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertActionVisible('confirmPayment')
            ->callAction('confirmPayment')
            ->assertNotified('To\'lov tasdiqlanmadi');

        Http::assertNothingSent();
        $this->assertStringContainsString('ERSP manzili kiritilmagan', OrderResource::timeline($order)[3][2]);
        $this->assertSame(1, Order::awaitingPaymentConfirmation()->count());
    }
}
