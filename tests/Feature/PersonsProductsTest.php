<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Accident (202) and tourist (203) share PersonsInsuranceController */
class PersonsProductsTest extends TestCase
{
    use RefreshDatabase;

    private const APPLICANT = [
        'lastname' => 'ALIYEV', 'firstname' => 'VALI', 'middlename' => 'ANVAR OGLI',
        'pinfl' => '31501991234567', 'passport_seria' => 'AB', 'passport_number' => '1234567',
        'birth_date' => '1999-01-15', 'gender' => '1', 'address' => 'Toshkent', 'phone' => '998901234567',
        'region_id' => 10, 'district_id' => 1009,
    ];

    public static function products(): array
    {
        return [
            'accident' => ['accident', '202'],
            'tourist'  => ['tourist', '203'],
        ];
    }

    #[DataProvider('products')]
    public function test_full_flow_uses_the_product_code(string $key, string $code): void
    {
        config(['provider.submit.' . $key => 'http://online.xalqsugurta.uz/xs/ins/website/' . $key . '/sale']);
        Http::fake([
            '*/website/accident/calc' => Http::response(['result' => 0, 'persons' => [['insurancePremium' => 1500]]]),
            '*/sale'                  => Http::response(['result' => 0, 'contract_id' => 555, 'amount' => 1500, 'payme_url' => 'https://pay.example/1']),
        ]);

        $this->withSession([$key . '.applicant' => self::APPLICANT]);

        $this->get("/uz/{$key}")->assertOk()->assertSee(__('insurance.' . $key . '.page_title', [], 'uz'));
        $this->get("/uz/{$key}/persons")->assertOk();

        $this->post("/uz/{$key}/persons/add", self::APPLICANT + ['sum_insured' => 500_000])
            ->assertRedirect(route($key . '.getPersons', ['locale' => 'uz']));
        $this->assertSame(1500, session($key . '.persons.0.insurance_premium'));

        $this->post("/uz/{$key}/persons/confirm")->assertRedirect(route($key . '.getCalculator', ['locale' => 'uz']));
        $this->get("/uz/{$key}/calculator")->assertOk();

        $start = now()->addDay()->format('Y-m-d');
        $this->post("/uz/{$key}/calculator", ['start_date' => $start])
            ->assertRedirect(route($key . '.getConfirm', ['locale' => 'uz']));
        $this->get("/uz/{$key}/confirm")->assertOk()->assertSee('ALIYEV');

        $this->post("/uz/{$key}/confirm", ['offerta_agreed' => '1'])->assertRedirect();

        $order = Order::sole();
        $this->assertSame('555', $order->insurance_id);
        $this->assertSame($key, $order->product_key);
        $this->assertSame('https://pay.example/1', $order->payme_url);

        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/calc') && $r['details']['productCode'] === $code);
        Http::assertSent(fn ($r) => $r->url() === "http://online.xalqsugurta.uz/xs/ins/website/{$key}/sale"
            && $r['details']['productCode'] === $code
            && $r['details']['startDate'] === $start
            && $r['cost']['insurancePremium'] === 1500);
    }

    public function test_tourist_limits_come_from_its_own_settings(): void
    {
        Product::create([
            'name_uz' => 'Turist', 'name_ru' => 'Турист', 'name_en' => 'Tourist', 'route' => 'tourist',
            'is_active' => true, 'settings' => ['max' => 2_000_000, 'presets' => [2_000_000]],
        ]);
        Http::fake(['*' => Http::response(['result' => 0, 'persons' => [['insurancePremium' => 6000]]])]);

        $this->withSession(['tourist.applicant' => self::APPLICANT])
            ->get('/uz/tourist/persons')
            ->assertOk()
            ->assertSee('data-sum="2000000"', false);

        $this->withSession(['tourist.applicant' => self::APPLICANT])
            ->post('/uz/tourist/persons/add', self::APPLICANT + ['sum_insured' => 2_000_000])
            ->assertSessionHasNoErrors();

        // Accident keeps its built-in maximum
        $this->withSession(['accident.applicant' => self::APPLICANT])
            ->post('/uz/accident/persons/add', self::APPLICANT + ['sum_insured' => 2_000_000])
            ->assertSessionHasErrors('sum_insured');
    }
}
