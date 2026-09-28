<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentPageTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attributes = []): Order
    {
        return app(OrderService::class)->createOrder(array_merge([
            'product_name'             => 'Gaz ballon',
            'insuranceProductName'     => 'Gaz ballon sug\'urtasi',
            'amount'                   => 250000,
            'insurance_id'             => 'GAS-1',
            'phone'                    => '998901234567',
            'insurances_data'          => ['_product_key' => 'gas'],
            'insurances_response_data' => ['contract_id' => 1],
            'contractStartDate'        => '2026-10-01',
            'contractEndDate'          => '2027-09-30',
        ], $attributes));
    }

    public function test_new_order_shows_payment_methods_and_details_to_its_creator(): void
    {
        $order = $this->order();

        $this->withSession([OrderService::SESSION_ORDERS => [$order->id]])
            ->get("/uz/payment/{$order->id}")
            ->assertOk()
            ->assertSee('Payme')
            ->assertSee(route('payment.payme', ['id' => $order->id]), false)
            ->assertSee('+998 90 123 45 67')
            ->assertSee('01.10.2026');
    }

    public function test_click_is_offered_only_with_a_click_link(): void
    {
        $order = $this->order(['click_url' => 'https://my.click.uz/pay/1']);

        $this->get("/uz/payment/{$order->id}")->assertSee('https://my.click.uz/pay/1', false);
        $this->get('/uz/payment/' . $this->order()->id)->assertDontSee('click.svg', false);
    }

    public function test_strangers_do_not_see_personal_data_or_policy_links(): void
    {
        $order = $this->order(['insurances_response_data' => ['download_url' => 'https://example.com/secret.pdf']]);
        $order->update(['status' => Order::STATUS_PAID]);

        $this->get("/uz/payment/{$order->id}")
            ->assertOk()
            ->assertDontSee('+998 90 123 45 67')
            ->assertDontSee('secret.pdf')
            ->assertSee('№' . $order->id);
    }

    public function test_paid_order_shows_the_policy_to_its_creator_and_to_admins(): void
    {
        $order = $this->order(['insurances_response_data' => [
            'download_url' => 'https://example.com/p.pdf', 'polis_sery' => 'GB', 'polis_number' => '0041287',
        ]]);
        $order->update(['status' => Order::STATUS_PAID]);

        $this->withSession([OrderService::SESSION_ORDERS => [$order->id]])
            ->get("/uz/payment/{$order->id}")
            ->assertSee('https://example.com/p.pdf', false)
            ->assertSee('GB 0041287');

        $this->actingAs(User::factory()->create())
            ->get("/uz/payment/{$order->id}")
            ->assertSee('https://example.com/p.pdf', false);
    }

    public function test_paid_order_waiting_for_its_policy_refreshes_itself(): void
    {
        $order = $this->order();
        $order->update(['status' => Order::STATUS_PAID]);

        $this->get("/uz/payment/{$order->id}")
            ->assertOk()
            ->assertSee('location.reload()', false)
            ->assertDontSee('payme.svg', false);
    }

    public function test_cancelled_order(): void
    {
        $order = $this->order();
        $order->update(['status' => Order::STATUS_CANCELLED]);

        $this->get("/uz/payment/{$order->id}")->assertOk()->assertDontSee('payme.svg', false);
    }

    public function test_creating_an_order_remembers_it_in_the_session(): void
    {
        $this->get('/uz');
        $order = $this->order();

        $this->assertContains($order->id, session(OrderService::SESSION_ORDERS));
    }
}
