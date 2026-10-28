<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\PaymentSettings;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TravelFlowTest extends TestCase
{
    use RefreshDatabase;

    /** travel/sale answers [status, body], one per call (the last one repeats) */
    private array $saleQueue = [];

    /** get answer */
    private array $policies = ['result' => 0, 'found_polis' => 0];

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-28 10:00');
        Cache::flush();

        config([
            'provider.base_url'              => 'http://online.xalqsugurta.uz/xs/ins/osago/proxy',
            'provider.travel.base_url'       => 'https://online.xalqsugurta.uz/xs/ins/website/',
            'provider.travel.username'       => 'site',
            'provider.travel.password'       => 'secret',
            'provider.travel.get_username'   => 'get-user',
            'provider.travel.get_password'   => 'get-secret',
        ]);

        $this->saleQueue = [[200, $this->saleOk()]];

        Http::fake(function (Request $request) {
            $url = $request->url();

            return match (true) {
                str_contains($url, 'reference/countries') => Http::response(['items' => [
                    ['id' => 217, 'name1' => 'ТУРЦИЯ', 'name3' => 'TURKIYA', 'ename' => 'TURKEY', 'kod' => 'TUR', 'schengen' => 0, 'pr1' => 1, 'pr2' => 1, 'pr3' => 1],
                    ['id' => 38, 'name1' => 'ГРЕЦИЯ', 'name3' => 'GRESIYA', 'ename' => 'SCHENGEN - GREECE', 'kod' => 'GRC', 'schengen' => 1, 'pr1' => 1, 'pr3' => 0],
                ], 'hasMore' => false, 'limit' => 1000, 'offset' => 0]),
                str_contains($url, 'reference/regions') => Http::response(['items' => [
                    ['id' => 10, 'name_ru' => 'г. Ташкент', 'name_lat' => 'Toshkent sh.'],
                    ['id' => 11, 'name_ru' => 'Ташкентская обл.', 'name_lat' => 'Toshkent vil.'],
                ], 'hasMore' => false, 'limit' => 25, 'offset' => 0]),
                str_contains($url, 'osago/proxy') => Http::response(['error' => 0, 'result' => $this->person($request->data()['pinfl'] ?? '')]),
                str_contains($url, 'travel/calc') => Http::response($this->calcAnswer($request->data())),
                str_contains($url, 'travel/sale') => $this->nextSale(),
                str_contains($url, 'website/get') => Http::response($this->policies),
                default => Http::response([], 404),
            };
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ─── Fakes ────────────────────────────────────────────────────────────────

    private function nextSale()
    {
        [$status, $body] = count($this->saleQueue) > 1 ? array_shift($this->saleQueue) : $this->saleQueue[0];

        return Http::response($body, $status);
    }

    private function person(string $pinfl): array
    {
        return match ($pinfl) {
            '31505900000011' => ['currentPinfl' => $pinfl, 'lastNameLatin' => 'ALIYEV', 'firstNameLatin' => 'ALI', 'middleNameLatin' => 'VALIYEVICH',
                'birthDate' => '1990-05-15', 'gender' => '1', 'address' => 'Toshkent sh., Chilonzor 1'],
            '42003920000022' => ['currentPinfl' => $pinfl, 'lastNameLatin' => 'ALIYEVA', 'firstNameLatin' => 'MALIKA',
                'birthDate' => '1992-03-20', 'gender' => '2', 'address' => 'Toshkent sh., Chilonzor 1'],
            '51001150000033' => ['currentPinfl' => $pinfl, 'lastNameLatin' => 'ALIYEV', 'firstNameLatin' => 'BOBUR',
                'birthDate' => '2015-01-10', 'gender' => '1', 'address' => 'Toshkent sh.'],
            default => [],
        };
    }

    private function calcAnswer(array $body): array
    {
        $travelers = array_map(fn (array $t) => ['age' => 30, 'coefficient' => 1, 'liability' => 50000, 'premium' => 18, 'premium_uzs' => 214541.64], $body['travelers']);
        $base = [
            'result' => 0, 'result_message' => 'OK', 'policy_type' => 0, 'start_date' => '01.11.2026', 'end_date' => '30.11.2026',
            'policy_end_date' => '30.11.2026', 'days' => 30, 'insured_days' => 30, 'country' => '217', 'is_schengen' => 0,
            'exchange_rate' => 11918.98, 'exchange_rate_eur' => 13283.7,
            'programs' => [
                ['id' => 1, 'name' => 'Mini', 'liability' => 20000, 'premium_usd' => 21.6, 'insurance_premium' => 257449.97, 'available' => 1],
                ['id' => 3, 'name' => 'Basic', 'liability' => 50000, 'premium_usd' => 32.4, 'insurance_premium' => 386174.95, 'available' => 1],
                ['id' => 5, 'name' => 'Premium', 'liability' => 100000, 'premium_usd' => 48.6, 'insurance_premium' => 579262.43, 'available' => 0, 'reason' => 'Тариф вне диапазона'],
            ],
        ];

        if (!empty($body['policy']['program'])) {
            $base += [
                'program' => 3, 'program_name' => 'Basic', 'program_liability' => 50000, 'program_premium' => 18,
                'insurance_premium' => round(214541.64 * count($travelers), 2), 'premium_usd' => 18 * count($travelers),
                'travelers' => $travelers, 'available' => 1,
            ];
        }

        return $base;
    }

    private function saleOk(): array
    {
        return [
            'result' => 0, 'result_message' => 'OK', 'repeated' => false, 'status' => 'REGISTERED',
            'contract_id' => 3172714, 'contract_number' => '10/01/608794', 'invoice_id' => 1422,
            'amount' => 429083.28, 'currency' => 'UZS', 'payment_deadline' => '01.11.2026',
            'payme_url' => 'https://checkout.paycom.uz/abc', 'click_url' => 'https://my.click.uz/services/pay?x=1',
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function trip(array $override = []): array
    {
        return array_merge([
            'policy_type'    => 0,
            'start_date'     => '2026-11-01',
            'end_date'       => '2026-11-30',
            'countries'      => [217],
            'trip_purpose'   => 0,
            'travelers_type' => 0,
            'birthdates'     => ['1990-05-15', '1992-03-20'],
        ], $override);
    }

    /** Trip with programme 3, applicant who travels, second traveller: ready for the confirm page */
    private function readyToConfirm(): void
    {
        $this->post('/uz/travel/trip', $this->trip(['program' => 3]))->assertRedirect(route('travel.getApplicant', ['locale' => 'uz']));
        $this->post('/uz/travel/applicant', [
            'passport_seria' => 'aa', 'passport_number' => '1234567', 'pinfl' => '31505900000011',
            'phone' => '+998 90 123 45 67', 'region_id' => 10, 'travels' => '1',
        ])->assertRedirect(route('travel.getTravelers', ['locale' => 'uz']));
        $this->post('/uz/travel/travelers/add', ['passport_seria' => 'AB', 'passport_number' => '7654321', 'pinfl' => '42003920000022'])
            ->assertRedirect(route('travel.getTravelers', ['locale' => 'uz']));
        $this->post('/uz/travel/travelers/confirm')->assertRedirect(route('travel.getConfirm', ['locale' => 'uz']));
    }

    private function sales(): array
    {
        return Http::recorded(fn (Request $r) => str_contains($r->url(), 'travel/sale'))->map(fn ($pair) => $pair[0]->data())->values()->all();
    }

    private function fakeSales(array ...$answers): void
    {
        $this->saleQueue = $answers;
    }

    // ─── Tests ────────────────────────────────────────────────────────────────

    public function test_trip_page_lists_countries_and_calculate_prices_every_programme(): void
    {
        $this->get('/uz/travel')->assertOk()->assertSee('Turkiya')->assertSee('Gresiya')->assertSee(__t('messages.travel.trip_title'));
        $this->get('/en/travel')->assertOk()->assertSee('Greece')->assertDontSee('SCHENGEN - GREECE');

        $this->postJson('/uz/travel/calculate', $this->trip())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'programs')
            ->assertJsonPath('programs.1.name', 'Basic')
            ->assertJsonPath('programs.1.price', '386 175 so\'m')
            ->assertJsonPath('programs.2.available', false)
            ->assertJsonPath('trip.days', 30);

        $calc = Http::recorded(fn (Request $r) => str_contains($r->url(), 'travel/calc'))->first()[0];
        $this->assertSame(['policy_type' => 0, 'start_date' => '01.11.2026', 'country' => '217', 'trip_purpose' => 0, 'travelers_type' => 0, 'end_date' => '30.11.2026'], $calc->data()['policy']);
        $this->assertSame([['birthdate' => '15.05.1990'], ['birthdate' => '20.03.1992']], $calc->data()['travelers']);
        $this->assertTrue($calc->hasHeader('Authorization', 'Basic ' . base64_encode('site:secret')));
    }

    public function test_trip_validation(): void
    {
        $this->postJson('/uz/travel/calculate', $this->trip(['countries' => []]))->assertStatus(422)->assertJsonValidationErrors('countries');
        $this->postJson('/uz/travel/calculate', $this->trip(['countries' => [999]]))->assertStatus(422)->assertJsonValidationErrors('countries.0');
        $this->postJson('/uz/travel/calculate', $this->trip(['start_date' => '2026-10-01']))->assertStatus(422)->assertJsonValidationErrors('start_date');
        $this->postJson('/uz/travel/calculate', $this->trip(['policy_type' => 1]))->assertStatus(422)->assertJsonValidationErrors('multi_days');
        $this->postJson('/uz/travel/calculate', $this->trip(['policy_type' => 1, 'multi_days' => 2, 'end_date' => null]))->assertOk();
    }

    public function test_steps_need_the_earlier_ones(): void
    {
        $this->get('/uz/travel/applicant')->assertRedirect(route('travel.index', ['locale' => 'uz']));
        $this->get('/uz/travel/travelers')->assertRedirect(route('travel.index', ['locale' => 'uz']));
        $this->get('/uz/travel/confirm')->assertRedirect(route('travel.index', ['locale' => 'uz']));
    }

    public function test_full_flow_sells_through_the_insurer_and_opens_its_payment_links(): void
    {
        $this->readyToConfirm();

        $this->get('/uz/travel/confirm')->assertOk()
            ->assertSee('ALIYEV ALI')
            ->assertSee('ALIYEVA MALIKA')
            ->assertSee('Basic')
            ->assertSee('429 083');

        $this->post('/uz/travel/confirm')->assertSessionHasErrors('offerta_agreed');
        $this->post('/uz/travel/confirm', ['offerta_agreed' => '1'])->assertRedirect();

        $sale = $this->sales()[0];
        $this->assertMatchesRegularExpression('/^XS-261028-[A-Z0-9]{10}$/', $sale['order_id']);
        $this->assertSame(3, $sale['policy']['program']);
        $this->assertArrayNotHasKey('return_url', $sale['policy'], 'APP_URL is not https in tests');
        $this->assertSame(['insurance_premium' => 429083.28], $sale['cost']);
        $this->assertSame(10, $sale['applicant']['region']);
        $this->assertSame('998901234567', $sale['applicant']['phone']);
        $this->assertSame('AA', $sale['applicant']['seria']);
        $this->assertSame('15.05.1990', $sale['applicant']['birthdate']);
        $this->assertCount(2, $sale['travelers']);
        $this->assertSame([1, 0], array_column($sale['travelers'], 'gender'));
        $this->assertSame('ALIYEVA', $sale['travelers'][1]['lastname']);

        $order = Order::sole();
        $this->assertSame('travel', $order->product_key);
        $this->assertSame('3172714', $order->insurance_id);
        $this->assertSame('429083.28', $order->amount);
        $this->assertSame($sale['order_id'], $order->insurances_data['order_key']);
        $this->assertSame('2026-11-30', $order->contractEndDate->format('Y-m-d'));

        // Only the insurer's links: its Payme / Click issue the policy, the site's cashbox would not
        PaymentSettings::save(['payme_enabled' => true]);
        $this->get(route('payment.show', ['locale' => 'uz', 'orderId' => $order->id]))->assertOk()
            ->assertSee('https://checkout.paycom.uz/abc', false)
            ->assertSee('https://my.click.uz/services/pay?x=1', false)
            ->assertDontSee(route('payment.payme', ['id' => $order->id]), false);

        $this->get(route('payment.payme', ['id' => $order->id]))->assertNotFound();
    }

    public function test_applicant_must_be_an_adult_and_have_a_region(): void
    {
        $this->post('/uz/travel/trip', $this->trip(['program' => 3]));

        $this->post('/uz/travel/applicant', ['passport_seria' => 'AA', 'passport_number' => '1234567', 'pinfl' => '31505900000011', 'phone' => '901234567'])
            ->assertSessionHasErrors('region_id');

        $this->post('/uz/travel/applicant', ['passport_seria' => 'AC', 'passport_number' => '1111111', 'pinfl' => '51001150000033', 'phone' => '901234567', 'region_id' => 10])
            ->assertSessionHasErrors(['pinfl' => __t('messages.travel.errors.58')]);
    }

    public function test_the_number_of_travellers_must_match_the_trip(): void
    {
        $this->post('/uz/travel/trip', $this->trip(['program' => 3]));
        $this->post('/uz/travel/applicant', ['passport_seria' => 'AA', 'passport_number' => '1234567', 'pinfl' => '31505900000011', 'phone' => '901234567', 'region_id' => 10, 'travels' => '1']);

        $this->post('/uz/travel/travelers/confirm')->assertRedirect(route('travel.getTravelers', ['locale' => 'uz']))->assertSessionHasErrors('error');
        $this->get('/uz/travel/confirm')->assertRedirect(route('travel.index', ['locale' => 'uz']));

        // The same person twice
        $this->post('/uz/travel/travelers/add', ['passport_seria' => 'AA', 'passport_number' => '1234567', 'pinfl' => '31505900000011'])
            ->assertSessionHasErrors('pinfl');
    }

    public function test_a_changed_price_is_recalculated_and_shown(): void
    {
        $this->readyToConfirm();
        $this->fakeSales([400, ['result' => 40, 'result_message' => 'Цена изменилась: 430000.00']]);

        $this->post('/uz/travel/confirm', ['offerta_agreed' => '1'])
            ->assertRedirect(route('travel.getConfirm', ['locale' => 'uz']))
            ->assertSessionHasErrors(['error' => __t('messages.travel.errors.40')]);

        $this->assertSame(0, Order::count());
    }

    public function test_a_retry_after_registration_in_progress_reuses_the_order_id(): void
    {
        $this->readyToConfirm();
        $this->fakeSales(
            [409, ['result' => 59, 'result_message' => 'Договор регистрируется', 'order_id' => 'x', 'status' => 'PENDING', 'contract_id' => 3172714]],
            [200, $this->saleOk() + ['repeated' => true]],
        );

        $this->post('/uz/travel/confirm', ['offerta_agreed' => '1'])
            ->assertSessionHasErrors(['error' => __t('messages.travel.errors.59')]);
        $this->post('/uz/travel/confirm', ['offerta_agreed' => '1'])->assertRedirect();

        $sales = $this->sales();
        $this->assertCount(2, $sales);
        $this->assertSame($sales[0]['order_id'], $sales[1]['order_id']);
        $this->assertSame(1, Order::count());
    }

    public function test_a_final_refusal_starts_a_new_order_id(): void
    {
        $this->readyToConfirm();
        $this->fakeSales(
            [422, ['result' => 51, 'result_message' => 'НАПП отказал', 'status' => 'NOT_REGISTERED']],
            [200, $this->saleOk()],
        );

        $this->post('/uz/travel/confirm', ['offerta_agreed' => '1'])->assertSessionHasErrors(['error' => __t('messages.travel.errors.51')]);
        $this->post('/uz/travel/confirm', ['offerta_agreed' => '1'])->assertRedirect();

        $sales = $this->sales();
        $this->assertNotSame($sales[0]['order_id'], $sales[1]['order_id']);
    }

    public function test_the_insurer_being_down_keeps_the_application(): void
    {
        $this->readyToConfirm();
        $this->fakeSales([502, '<html>Bad gateway</html>']);

        $this->post('/uz/travel/confirm', ['offerta_agreed' => '1'])
            ->assertSessionHasErrors(['error' => __t('messages.flow.insurer_unavailable')]);
        $this->get('/uz/travel/confirm')->assertOk();
    }

    public function test_the_policy_is_picked_up_after_payment_on_the_insurers_side(): void
    {
        $this->readyToConfirm();
        $this->post('/uz/travel/confirm', ['offerta_agreed' => '1']);
        $order = Order::sole();

        // Not paid yet
        $this->get(route('travel.paid', ['locale' => 'uz', 'order' => $order->insurances_data['order_key']]))
            ->assertRedirect(route('payment.show', ['locale' => 'uz', 'orderId' => $order->id]));
        $this->assertSame(Order::STATUS_NEW, $order->fresh()->status);

        $this->policies = ['result' => 0, 'found_polis' => 1, 'polis' => [[
            'polis_sery' => 'VZR', 'polis_number' => 123456, 'polis_status_id' => 2, 'polis_url' => 'https://online.xalqsugurta.uz/polis/123.pdf',
        ]]];

        $this->get(route('travel.paid', ['locale' => 'uz', 'order' => $order->insurances_data['order_key']]))
            ->assertRedirect(route('payment.show', ['locale' => 'uz', 'orderId' => $order->id]));

        $order->refresh();
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertSame('https://online.xalqsugurta.uz/polis/123.pdf', $order->insurances_response_data['download_url']);

        $this->get(route('payment.show', ['locale' => 'uz', 'orderId' => $order->id]))->assertOk()
            ->assertSee('VZR 123456')
            ->assertSee('https://online.xalqsugurta.uz/polis/123.pdf', false);

        $get = Http::recorded(fn (Request $r) => str_contains($r->url(), 'website/get'))->last()[0];
        $this->assertSame(['contract_id' => 3172714], $get->data());
        $this->assertTrue($get->hasHeader('Authorization', 'Basic ' . base64_encode('get-user:get-secret')));
    }

    public function test_sync_command_marks_paid_orders(): void
    {
        $this->readyToConfirm();
        $this->post('/uz/travel/confirm', ['offerta_agreed' => '1']);

        $this->policies = ['result' => 0, 'found_polis' => 1, 'polis' => [['polis_sery' => 'VZR', 'polis_number' => 1, 'polis_status_id' => 2, 'polis_url' => 'https://x/1.pdf']]];

        $this->artisan('travel:sync-policies')->expectsOutput('Policies found: 1')->assertSuccessful();
        $this->assertSame(Order::STATUS_PAID, Order::sole()->status);
    }

    public function test_unknown_return_key_goes_home(): void
    {
        $this->get('/uz/travel/paid?order=nope')->assertRedirect(route('home', ['locale' => 'uz']));
        $this->get('/uz/travel/paid?order=' . urlencode('a b&c'))->assertRedirect(route('home', ['locale' => 'uz']));
    }
}
