<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Resources\ApiLogResource;
use App\Filament\Admin\Resources\OrderResource;
use App\Filament\Admin\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Admin\Widgets\AttentionWidget;
use App\Models\ApiLog;
use App\Models\Order;
use App\Models\User;
use App\Services\ApiLogger;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ApiJournalTest extends TestCase
{
    use RefreshDatabase;

    private const PERFORM_URL = 'online.xalqsugurta.uz/xs/ins/unv/gazballonsayt/PerformTransactionRequest';

    protected function setUp(): void
    {
        parent::setUp();

        config(['provider.xalq.base_url' => 'http://online.xalqsugurta.uz/xs/ins/unv/gazballonsayt']);
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function paidGasOrder(array $response = ['contract_id' => 884213]): Order
    {
        return Order::create([
            'product_name'             => 'Gaz ballon',
            'amount'                   => 250000,
            'status'                   => Order::STATUS_PAID,
            'payment_type'             => Order::PAYMENT_PAYME,
            'insurance_id'             => 'GAS-1',
            'phone'                    => '998901234567',
            'insurances_data'          => ['_product_key' => 'gas', 'applicant' => ['lastname' => 'ALIYEV', 'firstname' => 'VALI']],
            'insurances_response_data' => $response,
            'contractStartDate'        => '2026-10-01',
            'contractEndDate'          => '2027-09-30',
        ]);
    }

    // ─── Recording ────────────────────────────────────────────────────────────

    public function test_insurer_requests_are_recorded(): void
    {
        Http::fake([
            '*/website/accident/calc' => Http::response(['result' => 0, 'cost' => ['insurancePremium' => 1500]]),
            '*/website/accident/sale' => Http::response('<!DOCTYPE html><html>404</html>', 404),
            'example.com/*'           => Http::response('ok'),
        ]);

        Http::post('http://online.xalqsugurta.uz/xs/ins/website/accident/calc', ['persons' => [['sumInsured' => '500000']]]);
        Http::post('http://online.xalqsugurta.uz/xs/ins/website/accident/sale', ['details' => ['productCode' => '202']]);
        Http::get('https://example.com/other');

        $this->assertSame(2, ApiLog::count());

        $calc = ApiLog::where('endpoint', 'website/accident/calc')->sole();
        $this->assertTrue($calc->success);
        $this->assertSame('0', $calc->result);
        $this->assertSame(['persons' => [['sumInsured' => '500000']]], $calc->request);

        $sale = ApiLog::where('endpoint', 'website/accident/sale')->sole();
        $this->assertFalse($sale->success);
        $this->assertSame('HTTP 404 · HTML', $sale->outcome());
    }

    public function test_proxy_requests_show_the_service_name_and_business_errors(): void
    {
        Http::fake(['*' => Http::response(['error' => 1, 'error_message' => 'Not found'])]);

        Http::withHeaders(['param' => '/api/provider/pinfl-v2'])
            ->post('http://online.xalqsugurta.uz/xs/ins/osago/proxy', ['pinfl' => '31501991234567']);

        $log = ApiLog::sole();
        $this->assertSame('osago/proxy · pinfl-v2', $log->endpoint);
        $this->assertFalse($log->success);
        $this->assertSame('result 1', $log->outcome());
    }

    public function test_rows_are_linked_to_the_order(): void
    {
        Http::fake(['*' => Http::response(['result' => 0, 'contract_id' => 1])]);
        $order = $this->paidGasOrder();

        Http::post('http://online.xalqsugurta.uz/xs/ins/unv/gazballonsayt/InitiateTransactionRequest', []);
        ApiLogger::attachToOrder($order);

        ApiLogger::forOrder($order, 'gas', fn () => Http::post('http://' . self::PERFORM_URL, []));

        $this->assertSame(2, $order->apiLogs()->count());
        $this->assertSame('gas', $order->apiLogs()->first()->product);
    }

    // ─── Order card ───────────────────────────────────────────────────────────

    public function test_retry_policy_saves_the_policy(): void
    {
        Http::fake([self::PERFORM_URL => Http::response([
            'result' => 0, 'polis_sery' => 'GB', 'polis_number' => '0041287', 'download_url' => 'https://example.com/p.pdf',
        ])]);
        $order = $this->paidGasOrder();

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertActionVisible('retryPolicy')
            ->callAction('retryPolicy')
            ->assertNotified('Polis chiqarildi');

        $order->refresh();
        $this->assertSame('https://example.com/p.pdf', $order->insurances_response_data['download_url']);
        $this->assertSame(884213, Http::recorded()[0][0]->data()['contract_id']);
        $this->assertSame(1, $order->apiLogs()->count());
        $this->assertFalse($order->awaitsPolicy());
    }

    public function test_retry_policy_reports_a_failure(): void
    {
        Http::fake([self::PERFORM_URL => Http::response(['result' => 5, 'result_message' => 'Error'], 500)]);
        $order = $this->paidGasOrder();

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('retryPolicy')
            ->assertNotified('Polis chiqmadi');

        $this->assertTrue($order->fresh()->awaitsPolicy());
        $this->assertSame(3, $order->apiLogs()->count(), 'Each retry attempt is its own row');
    }

    public function test_retry_is_hidden_once_the_policy_exists(): void
    {
        $order = $this->paidGasOrder(['contract_id' => 1, 'download_url' => 'https://example.com/p.pdf']);

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertActionHidden('retryPolicy');
    }

    public function test_order_card_shows_the_timeline_and_api_rows(): void
    {
        $order = $this->paidGasOrder();
        ApiLog::create([
            'order_id' => $order->id, 'product' => 'gas', 'method' => 'POST',
            'endpoint' => 'unv/PerformTransactionRequest', 'url' => 'http://' . self::PERFORM_URL,
            'status' => 500, 'success' => false, 'response' => '{"result":5}',
        ]);

        $this->get(OrderResource::getUrl('view', ['record' => $order]))
            ->assertOk()
            ->assertSee('Jarayon')
            ->assertSee('To\'lov qabul qilindi')
            ->assertSee('Polis chiqmadi')
            ->assertSee('PerformTransaction · HTTP 500');
    }

    // ─── Journal + dashboard ──────────────────────────────────────────────────

    public function test_journal_pages_render(): void
    {
        $log = ApiLog::create([
            'method' => 'POST', 'endpoint' => 'website/accident/sale', 'url' => 'http://online.xalqsugurta.uz/xs/ins/website/accident/sale',
            'status' => 404, 'success' => false, 'request' => ['details' => ['productCode' => '202']], 'response' => '<!DOCTYPE html>',
        ]);

        $this->get(ApiLogResource::getUrl('index'))->assertOk()->assertSee('website/accident/sale');
        $this->get(ApiLogResource::getUrl('view', ['record' => $log]))->assertOk()->assertSee('productCode');
    }

    public function test_flat_request_bodies_keep_their_keys(): void
    {
        $log = ApiLog::create([
            'method' => 'POST', 'endpoint' => 'osago/proxy · pinfl-v2', 'url' => 'http://online.xalqsugurta.uz/xs/ins/osago/proxy',
            'status' => 200, 'result' => '503', 'success' => false,
            'request' => ['pinfl' => '11111111111111', 'document' => 'AA0000000', 'isConsent' => 'Y'],
            'response' => '{"error":503,"error_message":"down"}',
        ]);

        $this->get(ApiLogResource::getUrl('view', ['record' => $log]))
            ->assertOk()
            ->assertSee('&quot;pinfl&quot;: &quot;11111111111111&quot;', false)
            ->assertSee('&quot;isConsent&quot;: &quot;Y&quot;', false)
            ->assertSee('<pre class="xs-json">', false);
    }

    public function test_attention_widget_lists_problems(): void
    {
        $this->paidGasOrder();
        ApiLog::create(['method' => 'POST', 'endpoint' => 'website/accident/sale', 'url' => 'x', 'status' => 404, 'success' => false]);

        Livewire::test(AttentionWidget::class)
            ->assertSee('To\'langan, polis chiqmagan')
            ->assertSee('Bugun API xatolari');
    }

    public function test_dashboard_and_order_list_render(): void
    {
        $this->paidGasOrder();

        $this->get('/admin')->assertOk()->assertSee('Diqqat talab qiladi');
        $this->get(OrderResource::getUrl('index', ['tab' => 'no_policy']))->assertOk()->assertSee('Polis chiqmagan');
    }

    public function test_attention_widget_is_calm_when_nothing_is_wrong(): void
    {
        Livewire::test(AttentionWidget::class)->assertSee('Hammasi joyida');
    }

    public function test_old_rows_are_pruned(): void
    {
        $old = ApiLog::create(['method' => 'GET', 'endpoint' => 'a', 'url' => 'x', 'success' => true]);
        $old->forceFill(['created_at' => now()->subDays(ApiLog::KEEP_DAYS + 1)])->save();
        ApiLog::create(['method' => 'GET', 'endpoint' => 'b', 'url' => 'x', 'success' => true]);

        $this->artisan('model:prune', ['--model' => [ApiLog::class]])->assertSuccessful();

        $this->assertSame(['b'], ApiLog::pluck('endpoint')->all());
    }
}
