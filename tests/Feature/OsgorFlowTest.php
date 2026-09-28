<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OsgorFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'provider.base_url'     => 'http://online.xalqsugurta.uz/xs/ins/osago/proxy',
            'provider.calc.osgor'   => 'http://online.xalqsugurta.uz/xs/ins/eshop/osgorcalc',
            'provider.submit.osgor' => 'http://online.xalqsugurta.uz/xs/ins/eshop/osgor',
        ]);

        Http::fake([
            '*/osago/proxy'     => Http::response(['error' => 0, 'result' => [
                'name' => '"NAMUNA" MCHJ', 'gdFullName' => 'ALIYEV VALI', 'address' => 'Toshkent', 'oked' => '86230',
            ]]),
            '*/eshop/osgorcalc' => Http::response(['result' => 0, 'policies' => [[
                'insurancePremium' => 285500, 'insuranceSum' => 500000000, 'insuranceRate' => 0.0571,
                'funeralExpensesSum' => 1020000, 'insuranceTermId' => 4,
            ]]]),
            '*/eshop/osgor'     => Http::response(['result' => 0, 'contract_id' => 777, 'payme_url' => 'https://pay.example/7']),
        ]);
    }

    public function test_full_flow(): void
    {
        $this->get('/uz/osgor')->assertOk()->assertSee(__t('messages.flow.org_title'));

        $this->post('/uz/osgor/applicant', ['inn' => '123456789', 'phone' => '+998 90 123 45 67'])
            ->assertRedirect(route('osgor.getCalculator', ['locale' => 'uz']));

        $this->get('/uz/osgor/calculator')->assertOk()->assertSee('NAMUNA', false);

        $this->postJson('/uz/osgor/calculate', ['fot' => 500000000, 'start_date' => now()->addDay()->format('Y-m-d')])
            ->assertOk()
            ->assertJsonPath('data.insurance_premium', 285500);

        $start = now()->addDay()->format('Y-m-d');
        $this->post('/uz/osgor/calculator', ['fot' => 500000000, 'start_date' => $start])
            ->assertRedirect(route('osgor.getConfirm', ['locale' => 'uz']));

        $this->get('/uz/osgor/confirm')->assertOk()->assertSee('123456789')->assertSee('285 500');

        $this->post('/uz/osgor/confirm')->assertSessionHasErrors('offerta_agreed');
        $this->post('/uz/osgor/confirm', ['offerta_agreed' => '1'])->assertRedirect();

        $order = Order::sole();
        $this->assertSame('777', $order->insurance_id);
        $this->assertSame('998901234567', $order->phone);
        $this->assertEquals(285500, $order->amount);

        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/eshop/osgor')
            && $r['policies'][0]['insurancePremium'] === '285500'
            && $r['contractStartDate'] === $start);
    }

    public function test_the_premium_is_never_taken_from_the_browser(): void
    {
        $this->post('/uz/osgor/applicant', ['inn' => '123456789', 'phone' => '998901234567']);

        $this->post('/uz/osgor/calculator', [
            'fot' => 500000000, 'start_date' => now()->addDay()->format('Y-m-d'),
            'insurance_premium' => 1, 'insurance_rate' => 0,
        ]);

        $this->assertEquals(285500, session('osgor.calculation.insurance_premium'));
    }

    public function test_insurer_outage_on_submit_keeps_the_user_on_confirm(): void
    {
        $this->post('/uz/osgor/applicant', ['inn' => '123456789', 'phone' => '998901234567']);
        $this->post('/uz/osgor/calculator', ['fot' => 500000000, 'start_date' => now()->addDay()->format('Y-m-d')]);

        foreach ([Http::failedConnection(), Http::response('Bad Gateway', 502)] as $failure) {
            Http::swap(new Factory($this->app['events']));
            Http::fake(['*/eshop/osgor' => $failure]);

            $this->post('/uz/osgor/confirm', ['offerta_agreed' => '1'])
                ->assertRedirect(route('osgor.getConfirm', ['locale' => 'uz']))
                ->assertSessionHasErrors(['error' => __t('messages.flow.insurer_unavailable')]);
        }

        $this->assertSame(0, Order::count());
    }

    public function test_step_two_needs_an_organization(): void
    {
        $this->get('/uz/osgor/calculator')->assertRedirect(route('osgor.index', ['locale' => 'uz']));
        $this->post('/uz/osgor/applicant', ['inn' => '12', 'phone' => '1'])->assertSessionHasErrors(['inn', 'phone']);
    }
}
