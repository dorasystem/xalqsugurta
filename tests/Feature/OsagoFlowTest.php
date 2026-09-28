<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OsagoFlowTest extends TestCase
{
    use RefreshDatabase;

    private const OWNER     = '31501991234567';
    private const APPLICANT = '42002881234567';
    private const DRIVER    = '31203851234567';

    /** Vehicle returned by the registry; tests change it before the lookup */
    private array $vehicle = [
        'modelName' => 'CHEVROLET COBALT', 'vehicleTypeId' => 2, 'issueYear' => 2021, 'bodyNumber' => 'XWB0000000001',
        'engineNumber' => 'B15D2000001', 'techPassportIssueDate' => '2021-03-04T00:00:00', 'owner' => 'ALIYEV VALI', 'pinfl' => self::OWNER,
    ];

    private bool $submitFails = false;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'provider.base_url'                => 'http://online.xalqsugurta.uz/xs/ins/osago/proxy',
            'services.insurance.osago.endpoint' => 'http://online.xalqsugurta.uz/xs/ins/doraosago/create',
            'services.insurance.osago.retries'  => 1,
        ]);

        $people = [
            self::OWNER     => ['lastNameLatin' => 'ALIYEV', 'firstNameLatin' => 'VALI', 'middleNameLatin' => 'SOBIROVICH', 'gender' => '1', 'regionId' => 10, 'districtId' => 1003,
                                'address' => 'Test ko\'chasi 1', 'documents' => [['document' => 'AB1234567', 'docgiveplace' => 'IIB', 'datebegin' => '2019-02-01']]],
            self::APPLICANT => ['lastNameLatin' => 'KARIMOVA', 'firstNameLatin' => 'NODIRA', 'middleNameLatin' => 'ALIYEVNA', 'gender' => '2', 'regionId' => 10, 'districtId' => 1003],
            self::DRIVER    => ['lastNameLatin' => 'TOSHEV', 'firstNameLatin' => 'ANVAR', 'middleNameLatin' => 'ALIYEVICH', 'gender' => '1', 'regionId' => 10, 'districtId' => 1003],
        ];

        Http::fake(function (Request $request) use ($people) {
            $param = $request->header('param')[0] ?? '';
            $pinfl = $request->data()['pinfl'] ?? null;

            return match (true) {
                str_contains($param, 'pinfl-v2')       => isset($people[$pinfl])
                    ? Http::response(['error' => 0, 'result' => $people[$pinfl] + ['currentPinfl' => $pinfl]])
                    : Http::response(['error' => 1, 'error_message' => 'not found']),
                str_contains($param, 'driver-summary') => $pinfl === self::DRIVER
                    ? Http::response(['error' => 0, 'result' => ['DriverInfo' => ['licenseSeria' => 'AF', 'licenseNumber' => '7654321', 'issueDate' => '2015-05-10T00:00:00']]])
                    : Http::response(['error' => 1, 'error_message' => 'no licence']),
                str_contains($param, 'osago/vehicle')  => Http::response(['error' => 0, 'result' => $this->vehicle]),
                str_ends_with($request->url(), '/doraosago/create') => $this->submitFails
                    ? Http::response(['result' => 1, 'result_message' => 'Polis allaqachon mavjud'])
                    : Http::response(['result' => 0, 'UUID' => 'osago-uuid-1', 'amount' => $request->data()['cost']['insurancePremium'], 'payme_url' => 'https://checkout.example/pay']),
                default => Http::response('unexpected', 500),
            };
        });
    }

    private function vehicleStep(string $gov = '01A123BC'): void
    {
        $this->post('/uz/osago/vehicle', ['gov_number' => strtolower($gov), 'tech_passport_seria' => 'aaf', 'tech_passport_number' => '1234567'])
            ->assertRedirect(route('osago.getOwner', ['locale' => 'uz']));
    }

    private function ownerStep(array $extra = []): void
    {
        $this->post('/uz/osago/owner', array_merge([
            'owner_seria' => 'ab', 'owner_number' => '1234567', 'owner_pinfl' => self::OWNER,
            'applicant_is_owner' => '1', 'phone' => '90 123 45 67',
        ], $extra))->assertRedirect(route('osago.getTerms', ['locale' => 'uz']));
    }

    private function submittedBody(): array
    {
        $sent = Http::recorded(fn (Request $r) => str_ends_with($r->url(), '/doraosago/create'))->values();
        $this->assertCount(1, $sent);

        return $sent[0][0]->data();
    }

    public function test_full_flow_with_unlimited_drivers(): void
    {
        $this->get('/uz/osago')->assertOk()->assertSee(__t('messages.flow.vehicle_title'));

        $this->vehicleStep();
        $this->get('/uz/osago/owner')->assertOk()->assertSee('ALIYEV VALI')->assertSee(self::OWNER);

        $this->ownerStep();

        // Tashkent plate, passenger car: 80 mln × 0.2 × 1.2 × 2 / 100
        $this->get('/uz/osago/terms')->assertOk()->assertSee('384 000')->assertSee('192 000');

        $start = now()->addDay();
        $this->post('/uz/osago/terms', ['start_date' => $start->format('Y-m-d'), 'driver_limit' => 'unlimited'])
            ->assertRedirect(route('osago.getConfirm', ['locale' => 'uz']));

        $this->get('/uz/osago/confirm')->assertOk()->assertSee('ALIYEV')->assertSee('01A123BC')->assertSee('384 000');

        $this->post('/uz/osago/store-application')->assertSessionHasErrors('offerta_agreed');
        $this->post('/uz/osago/store-application', ['offerta_agreed' => '1'])->assertRedirect();

        $body = $this->submittedBody();
        $this->assertSame(384_000, $body['cost']['insurancePremium']);
        $this->assertSame(1, $body['cost']['useTerritoryId']);
        $this->assertSame(2, $body['vehicle']['typeId']);
        $this->assertSame('01A123BC', $body['vehicle']['govNumber']);
        $this->assertSame('AAF', $body['vehicle']['techPassport']['seria']);
        $this->assertSame('2021-03-04', $body['vehicle']['techPassport']['issueDate']);
        $this->assertSame('true', $body['owner']['applicantIsOwner']);
        $this->assertSame('IIB', $body['owner']['person']['passportData']['issuedBy']);
        $this->assertSame('2019-02-01', $body['owner']['person']['passportData']['issueDate']);
        $this->assertSame('1999-01-15', $body['applicant']['person']['birthDate']);
        $this->assertSame('998901234567', $body['applicant']['person']['phoneNumber']);
        $this->assertSame($start->format('Y-m-d'), $body['details']['startDate']);
        $this->assertSame($start->copy()->addYear()->subDay()->format('Y-m-d'), $body['details']['endDate']);
        $this->assertFalse($body['details']['driverNumberRestriction']);
        $this->assertSame([], $body['drivers']);

        $order = Order::sole();
        $this->assertSame('osago-uuid-1', $order->insurance_id);
        $this->assertEquals(384_000, $order->amount);
        $this->assertSame('https://checkout.example/pay', $order->payme_url);
        $this->assertSame('ALIYEV VALI SOBIROVICH', $order->client_name);
        $this->assertNull(session('osago'));
    }

    public function test_limited_drivers_and_a_separate_applicant(): void
    {
        $this->vehicleStep('40A123BC');
        $this->ownerStep(['applicant_is_owner' => '0', 'applicant_seria' => 'AC', 'applicant_number' => '7654321', 'applicant_pinfl' => self::APPLICANT]);

        // Limited without drivers is refused
        $this->post('/uz/osago/terms', ['start_date' => now()->format('Y-m-d'), 'driver_limit' => 'limited'])
            ->assertSessionHasErrors('drivers');

        // Somebody without a licence
        $this->post('/uz/osago/drivers', ['driver_seria' => 'AC', 'driver_number' => '7654321', 'driver_pinfl' => self::APPLICANT])
            ->assertSessionHasErrors('driver_pinfl');

        $this->post('/uz/osago/drivers', ['driver_seria' => 'ad', 'driver_number' => '1112223', 'driver_pinfl' => self::DRIVER])
            ->assertRedirect(route('osago.getTerms', ['locale' => 'uz']));
        $this->post('/uz/osago/drivers', ['driver_seria' => 'AD', 'driver_number' => '1112223', 'driver_pinfl' => self::DRIVER])
            ->assertSessionHasErrors('driver_pinfl');

        $this->get('/uz/osago/terms')->assertOk()->assertSee('TOSHEV ANVAR')->assertSee('AF 7654321');

        $this->post('/uz/osago/terms', ['start_date' => now()->format('Y-m-d'), 'driver_limit' => 'limited'])
            ->assertRedirect(route('osago.getConfirm', ['locale' => 'uz']));
        $this->post('/uz/osago/store-application', ['offerta_agreed' => '1'])->assertRedirect();

        $body = $this->submittedBody();
        // Other region, named drivers: 80 mln × 0.2 × 1.0 × 1 / 100
        $this->assertSame(160_000, $body['cost']['insurancePremium']);
        $this->assertSame(2, $body['cost']['useTerritoryId']);
        $this->assertTrue($body['details']['driverNumberRestriction']);
        $this->assertSame('false', $body['owner']['applicantIsOwner']);
        $this->assertSame('ALIYEV', $body['owner']['person']['fullName']['lastname']);
        $this->assertSame('KARIMOVA', $body['applicant']['person']['fullName']['lastname']);
        $this->assertSame('f', $body['applicant']['person']['gender']);
        $this->assertCount(1, $body['drivers']);
        $this->assertSame('7654321', $body['drivers'][0]['licenseNumber']);
        $this->assertSame('2015-05-10', $body['drivers'][0]['licenseIssueDate']);
        $this->assertSame('AD', $body['drivers'][0]['passportData']['seria']);
    }

    public function test_removing_a_driver(): void
    {
        $this->vehicleStep();
        $this->ownerStep();
        $this->post('/uz/osago/drivers', ['driver_seria' => 'AD', 'driver_number' => '1112223', 'driver_pinfl' => self::DRIVER]);
        $this->assertCount(1, session('osago.drivers'));

        $this->post('/uz/osago/drivers/remove/0')->assertRedirect(route('osago.getTerms', ['locale' => 'uz']));
        $this->assertSame([], session('osago.drivers'));
    }

    public function test_price_comes_from_the_registry_vehicle_not_the_request(): void
    {
        $this->vehicle['vehicleTypeId'] = 6; // truck
        $this->vehicleStep();
        $this->ownerStep();

        $this->post('/uz/osago/terms', [
            'start_date' => now()->format('Y-m-d'), 'driver_limit' => 'unlimited',
            'premium' => 1000, 'type_id' => 15, 'other_info' => ['typeId' => 15], 'gov_number' => '40A123BC',
        ])->assertRedirect(route('osago.getConfirm', ['locale' => 'uz']));
        $this->post('/uz/osago/store-application', ['offerta_agreed' => '1', 'premium' => 1000]);

        // 80 mln × 0.35 × 1.2 × 2 / 100
        $body = $this->submittedBody();
        $this->assertSame(672_000, $body['cost']['insurancePremium']);
        $this->assertSame(6, $body['vehicle']['typeId']);
        $this->assertEquals(672_000, Order::sole()->amount);
    }

    public function test_insurer_rejection_keeps_the_user_on_confirm(): void
    {
        $this->submitFails = true;
        $this->vehicleStep();
        $this->ownerStep();
        $this->post('/uz/osago/terms', ['start_date' => now()->format('Y-m-d'), 'driver_limit' => 'unlimited']);

        $this->post('/uz/osago/store-application', ['offerta_agreed' => '1'])
            ->assertRedirect(route('osago.getConfirm', ['locale' => 'uz']))
            ->assertSessionHasErrors(['error' => 'Polis allaqachon mavjud']);

        $this->assertSame(0, Order::count());
        $this->assertNotNull(session('osago.terms'));
    }

    public function test_steps_are_guarded_and_bad_input_is_refused(): void
    {
        $this->get('/uz/osago/owner')->assertRedirect(route('osago.index', ['locale' => 'uz']));
        $this->get('/uz/osago/terms')->assertRedirect(route('osago.index', ['locale' => 'uz']));
        $this->get('/uz/osago/confirm')->assertRedirect(route('osago.index', ['locale' => 'uz']));

        $this->post('/uz/osago/vehicle', ['gov_number' => 'ABC', 'tech_passport_seria' => 'A1', 'tech_passport_number' => '12'])
            ->assertSessionHasErrors(['gov_number', 'tech_passport_seria', 'tech_passport_number']);

        $this->vehicleStep();
        $this->post('/uz/osago/owner', ['owner_seria' => 'AB', 'owner_number' => '1234567', 'owner_pinfl' => '30000000000000', 'applicant_is_owner' => '1', 'phone' => '901234567'])
            ->assertSessionHasErrors('owner_pinfl');
    }

    public function test_a_new_vehicle_resets_the_owner_and_price(): void
    {
        $this->vehicleStep();
        $this->ownerStep();
        $this->post('/uz/osago/terms', ['start_date' => now()->format('Y-m-d'), 'driver_limit' => 'unlimited']);

        $this->vehicleStep('40B456CD');

        $this->assertNull(session('osago.owner'));
        $this->assertNull(session('osago.terms'));
        $this->get('/uz/osago/terms')->assertRedirect(route('osago.index', ['locale' => 'uz']));
    }

    public function test_old_links_and_public_lookups(): void
    {
        $this->get('/uz/osago/payment/15')->assertRedirect(route('payment.show', ['locale' => 'uz', 'orderId' => 15]));

        // The unauthenticated registry proxies used by the old page are gone
        $this->post('/get-person-info', ['pinfl' => self::OWNER])->assertNotFound();
        $this->post('/get-vehicle-info')->assertNotFound();
        $this->post('/get-driver-info')->assertNotFound();
        $this->get('/api/get-person-info')->assertNotFound();
    }
}
