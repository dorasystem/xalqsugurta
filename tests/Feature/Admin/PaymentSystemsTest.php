<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Pages\PaymentSystems;
use App\Models\AppSetting;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentSystemsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.click' => [], 'services.payme.merchant_id' => null]);
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function saveClick(array $overrides = []): void
    {
        Livewire::test(PaymentSystems::class)
            ->fillForm(array_merge([
                'click.enabled'     => true,
                'click.service_id'  => '12345',
                'click.merchant_id' => '67890',
                'click.secret_key'  => 'click-secret',
            ], $overrides))
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Saqlandi');
    }

    public function test_page_renders(): void
    {
        $this->get(PaymentSystems::getUrl())->assertOk()->assertSee('Click')->assertSee('/api/complete');
    }

    public function test_saving_overrides_config_and_encrypts_secrets(): void
    {
        $this->saveClick();

        $this->assertSame('12345', config('services.click.service_id'));
        $this->assertSame('click-secret', config('services.click.secret_key'));
        $this->assertTrue(PaymentSettings::clickReady());

        $stored = AppSetting::find('click.secret_key')->value;
        $this->assertNotSame('click-secret', $stored);
        $this->assertStringNotContainsString('click-secret', $stored);
    }

    public function test_an_empty_secret_keeps_the_saved_one(): void
    {
        $this->saveClick();
        $this->saveClick(['click.secret_key' => '', 'click.service_id' => '555']);

        $this->assertSame('555', config('services.click.service_id'));
        $this->assertSame('click-secret', config('services.click.secret_key'));
    }

    public function test_the_form_never_returns_a_secret(): void
    {
        $this->saveClick();

        Livewire::test(PaymentSystems::class)
            ->assertFormSet(['click.secret_key' => null, 'click.service_id' => '12345'])
            ->assertDontSee('click-secret');
    }

    public function test_payment_page_follows_the_switches(): void
    {
        $order = app(OrderService::class)->createOrder([
            'product_name' => 'Gaz ballon', 'amount' => 250000, 'insurance_id' => 'GAS-1', 'phone' => '998901234567',
            'insurances_data' => ['_product_key' => 'gas'],
        ]);

        $this->saveClick();
        $this->get("/uz/payment/{$order->id}")->assertSee(route('payment.click', ['id' => $order->id]), false);

        $this->saveClick(['click.enabled' => false]);
        Livewire::test(PaymentSystems::class)->fillForm(['payme.enabled' => false])->call('save');

        $this->get("/uz/payment/{$order->id}")
            ->assertDontSee('click.svg', false)
            ->assertDontSee('payme.svg', false)
            ->assertSee(__t('messages.flow.pay_unavailable'));
    }

    public function test_click_callbacks_use_the_saved_secret(): void
    {
        $this->saveClick();
        $order = Order::create(['product_name' => 'X', 'amount' => 1000, 'insurance_id' => 'X', 'status' => Order::STATUS_NEW]);

        $p = ['click_trans_id' => '1', 'service_id' => '12345', 'click_paydoc_id' => '1', 'merchant_trans_id' => (string) $order->id,
              'amount' => '1000', 'action' => '0', 'error' => '0', 'error_note' => '', 'sign_time' => '2026-09-29 10:00:00'];
        $p['sign_string'] = md5($p['click_trans_id'] . $p['service_id'] . 'click-secret' . $p['merchant_trans_id'] . $p['amount'] . $p['action'] . $p['sign_time']);

        $this->post('/api/prepare', $p)->assertJsonPath('error', 0);
    }
}
