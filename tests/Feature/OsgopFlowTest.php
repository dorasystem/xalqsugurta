<?php

namespace Tests\Feature;

use App\Models\InsuranceTerm;
use App\Models\Order;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OsgopFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'provider.base_url'     => 'http://online.xalqsugurta.uz/xs/ins/osago/proxy',
            'provider.calc.osgop'   => 'http://online.xalqsugurta.uz/xs/ins/eshop/osgopcalc',
            'provider.submit.osgop' => 'http://online.xalqsugurta.uz/xs/ins/eshop/osgop',
        ]);

        InsuranceTerm::create(['product_code' => 'osgop', 'provider_term_id' => 4, 'name_uz' => '1 yil', 'name_ru' => '1 год', 'name_en' => '1 year', 'months' => 12, 'is_active' => true]);
        VehicleType::create(['provider_vehicle_type_id' => 2, 'name_uz' => 'Avtobus', 'name_ru' => 'Автобус', 'name_en' => 'Bus', 'is_active' => true]);

        Http::fake(function (Request $request) {
            $param = $request->header('param')[0] ?? '';

            return match (true) {
                str_contains($param, 'pinfl-v2')      => Http::response(['error' => 0, 'result' => [
                    'currentPinfl' => '31501991234567', 'lastNameLatin' => 'ALIYEV', 'firstNameLatin' => 'VALI', 'gender' => '1', 'regionId' => 10,
                ]]),
                str_contains($param, 'inn')           => Http::response(['error' => 0, 'result' => ['name' => '"YO\'LOVCHI" MCHJ', 'regionId' => 10]]),
                str_contains($param, 'osago/vehicle') => Http::response(['error' => 0, 'result' => [
                    'modelName' => 'ISUZU', 'vehicleTypeId' => 2, 'seats' => 30, 'issueYear' => 2020,
                ]]),
                str_ends_with($request->url(), '/osgopcalc') => Http::response(['result' => 0, 'policies' => [[
                    'insurancePremium' => 30 * 10_000, 'insuranceSum' => 40_000_000,
                ]]]),
                str_ends_with($request->url(), '/eshop/osgop') => Http::response(['result' => 0, 'contract_id' => 901]),
                default => Http::response('unexpected', 500),
            };
        });
    }

    public function test_full_flow_for_a_person(): void
    {
        $this->get('/uz/osgop')->assertOk()->assertSee(__t('messages.flow.company_tab'));

        $this->post('/uz/osgop/store-applicant-individual', [
            'passport_seria' => 'ab', 'passport_number' => '1234567', 'pinfl' => '31501991234567', 'phone' => '90 123 45 67',
        ])->assertRedirect(route('osgop.getVehicle', ['locale' => 'uz']));
        $this->assertSame('1999-01-15', session('osgop.applicant.person.birth_date'));
        $this->assertSame('m', session('osgop.applicant.person.gender'));

        $this->post('/uz/osgop/store-vehicle', ['vehicle' => ['gov_number' => '01A123BC', 'tech_passport_seria' => 'AAF', 'tech_passport_number' => '1234567']])
            ->assertRedirect(route('osgop.getCalculator', ['locale' => 'uz']));

        $this->get('/uz/osgop/get-calculator')->assertOk()->assertSee('ISUZU')->assertSee('Avtobus');

        $start = now()->addDay()->format('Y-m-d');
        $this->post('/uz/osgop/calculator', ['insurance_term_id' => 4, 'start_date' => $start])
            ->assertRedirect(route('osgop.getConfirm', ['locale' => 'uz']));

        $this->get('/uz/osgop/confirm')->assertOk()->assertSee('ALIYEV')->assertSee('01A123BC')->assertSee('300 000');

        $this->post('/uz/osgop/store-application')->assertSessionHasErrors('offerta_agreed');
        $this->post('/uz/osgop/store-application', ['offerta_agreed' => '1'])->assertRedirect();

        $order = Order::sole();
        $this->assertSame('901', $order->insurance_id);
        $this->assertSame('998901234567', $order->phone);
        $this->assertEquals(300_000, $order->amount);
    }

    public function test_seats_and_type_from_the_request_are_ignored(): void
    {
        $this->post('/uz/osgop/store-applicant-company', ['inn' => '123456789', 'phone' => '998901234567'])
            ->assertRedirect(route('osgop.getVehicle', ['locale' => 'uz']));
        $this->post('/uz/osgop/store-vehicle', ['vehicle' => ['gov_number' => '01A123BC', 'tech_passport_seria' => 'AAF', 'tech_passport_number' => '1234567']]);

        $this->postJson('/uz/osgop/calculate', [
            'insurance_term_id' => 4, 'start_date' => now()->format('Y-m-d'), 'vehicle_type_id' => 1, 'number_of_seats' => 1,
        ])->assertOk()->assertJsonPath('data.insurance_premium', 300_000);

        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/osgopcalc')
            && $r['policies'][0]['objects'][0]['vehicle']['numberOfSeats'] === 30
            && $r['policies'][0]['objects'][0]['vehicle']['vehicleTypeId'] === 2);
    }

    public function test_company_needs_a_phone_and_steps_are_guarded(): void
    {
        $this->post('/uz/osgop/store-applicant-company', ['inn' => '123456789'])->assertSessionHasErrors('phone');
        $this->get('/uz/osgop/get-vehicle')->assertRedirect(route('osgop.index', ['locale' => 'uz']));
        $this->get('/uz/osgop/confirm')->assertRedirect(route('osgop.index', ['locale' => 'uz']));
    }
}
