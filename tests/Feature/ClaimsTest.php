<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\CallbackRequestResource\Pages\ListCallbackRequests;
use App\Filament\Admin\Resources\ClaimResource;
use App\Filament\Admin\Resources\ClaimResource\Pages\EditClaim;
use App\Models\CallbackRequest;
use App\Models\Claim;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class ClaimsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Product::create(['name_uz' => 'Gaz ballon', 'name_ru' => 'Газ', 'name_en' => 'Gas', 'route' => 'gas', 'is_active' => true]);
    }

    private function valid(array $extra = []): array
    {
        return array_merge([
            'product' => 'gas', 'policy_number' => 'xs 004512', 'full_name' => 'Aliyev Vali',
            'phone' => '+998 90 123 45 67', 'event_date' => now()->subDays(2)->format('Y-m-d'),
            'description' => 'Oshxonadagi gaz ballon quvuri yorilib, devor va jihozlar zarar ko\'rdi.',
        ], $extra);
    }

    public function test_customer_files_a_claim_with_files_and_checks_its_status(): void
    {
        $this->get('/uz/claims')->assertOk()->assertSee(__t('messages.claims.send'));

        $response = $this->post('/uz/claims', $this->valid([
            'files' => [UploadedFile::fake()->image('photo.jpg'), UploadedFile::fake()->create('akt.pdf', 200, 'application/pdf')],
        ]));

        $claim = Claim::sole();
        $response->assertRedirect(route('claims.sent', ['locale' => 'uz', 'number' => $claim->number]));
        $this->assertMatchesRegularExpression('/^ZH-\d{6}-\d{4}$/', $claim->number);
        $this->assertSame('XS 004512', $claim->policy_number);
        $this->assertSame('998901234567', $claim->phone);
        $this->assertSame('gas', $claim->product);
        $this->assertCount(2, $claim->files);
        Storage::disk('local')->assertExists($claim->files[0]['path']);

        $this->get('/uz/claims/sent/' . $claim->number)->assertOk()->assertSee($claim->number);

        // Status needs number and phone together
        $this->get('/uz/claims/status?number=' . $claim->number . '&phone=998901234567')
            ->assertOk()->assertSee(__t('messages.claims.status_new'));
        $this->get('/uz/claims/status?number=' . $claim->number . '&phone=998977777777')
            ->assertOk()->assertSee(__t('messages.claims.not_found'))->assertDontSee('XS 004512');

        $claim->update(['status' => Claim::STATUS_DOCUMENTS, 'public_note' => 'Yong\'in xizmati dalolatnomasini yuboring']);
        $this->get('/uz/claims/status?number=' . strtolower($claim->number) . '&phone=901234567')
            ->assertSee(__t('messages.claims.status_documents'))
            ->assertSee('dalolatnomasini');
    }

    public function test_invalid_input_and_bots_are_refused(): void
    {
        $this->post('/uz/claims', $this->valid([
            'description' => 'qisqa', 'event_date' => now()->addDay()->format('Y-m-d'), 'phone' => '123',
            'files' => [UploadedFile::fake()->create('virus.exe', 10)],
        ]))->assertSessionHasErrors(['description', 'event_date', 'phone', 'files.0']);

        $this->post('/uz/claims', $this->valid(['website' => 'http://spam']))->assertRedirect();
        $this->assertSame(0, Claim::count());

        // Someone else's sent page is not shown
        $this->get('/uz/claims/sent/ZH-000000-0000')->assertRedirect(route('claims.status', ['locale' => 'uz']));
    }

    public function test_a_verified_customer_can_link_their_order(): void
    {
        $order = Order::create(['product_name' => 'Gaz', 'amount' => 1, 'insurance_id' => 'G-1', 'phone' => '998901234567',
            'status' => 'paid', 'insurances_data' => ['_product_key' => 'gas'], 'insurances_response_data' => ['polis_sery' => 'XS', 'polis_number' => '777']]);
        $other = Order::create(['product_name' => 'Gaz', 'amount' => 1, 'insurance_id' => 'G-2', 'phone' => '998977777777', 'status' => 'paid']);

        $this->withSession([OrderService::SESSION_PHONE => ['phone' => '998901234567', 'until' => now()->addHour()->timestamp]]);

        $this->get('/uz/claims?order=' . $order->id)->assertSee('value="XS 777"', false);
        $this->get('/uz/claims?order=' . $other->id)->assertDontSee('G-2');

        $this->post('/uz/claims', $this->valid(['order_id' => $other->id]));
        $this->assertNull(Claim::sole()->order_id);
    }

    public function test_callback_request_is_stored_once_per_phone(): void
    {
        $this->get('/uz/callback')->assertOk();

        $this->post('/uz/callback', ['name' => 'Vali', 'phone' => '901234567', 'topic' => 'buy'])
            ->assertRedirect('/uz/callback')->assertSessionHas('status');
        $this->post('/uz/callback', ['name' => 'Vali', 'phone' => '+998 90 123 45 67', 'topic' => 'claim', 'message' => 'Tezroq']);

        $this->assertSame(1, CallbackRequest::count());
        $this->assertSame('claim', CallbackRequest::sole()->topic);

        $this->post('/uz/callback', ['name' => 'X', 'phone' => '1', 'topic' => 'hack'])->assertSessionHasErrors(['phone', 'topic']);
    }

    public function test_admin_handles_claims_and_callbacks(): void
    {
        $this->post('/uz/claims', $this->valid(['files' => [UploadedFile::fake()->create('akt.pdf', 50, 'application/pdf')]]));
        $this->post('/uz/callback', ['name' => 'Vali', 'phone' => '901234567']);
        $claim = Claim::sole();

        $admin = User::factory()->create();
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->get(ClaimResource::getUrl('index'))->assertOk()->assertSee($claim->number);
        $this->get(ClaimResource::getUrl('edit', ['record' => $claim]))->assertOk()->assertSee('akt.pdf');

        Livewire::test(EditClaim::class, ['record' => $claim->getRouteKey()])
            ->fillForm(['status' => Claim::STATUS_REVIEW, 'public_note' => 'Mutaxassis qo\'ng\'iroq qiladi'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame(Claim::STATUS_REVIEW, $claim->fresh()->status);
        $this->assertSame($admin->id, $claim->fresh()->handled_by);

        // Files: signed link + admin session
        $url = URL::temporarySignedRoute('claims.file', now()->addMinutes(30), ['claim' => $claim->id, 'index' => 0]);
        $this->get($url)->assertOk()->assertDownload('akt.pdf');
        $this->get(route('claims.file', ['claim' => $claim->id, 'index' => 0]))->assertForbidden();

        Livewire::test(ListCallbackRequests::class)
            ->callTableAction('done', CallbackRequest::sole())
            ->assertHasNoTableActionErrors();
        $this->assertSame(CallbackRequest::STATUS_DONE, CallbackRequest::sole()->status);
    }

    public function test_files_are_not_given_to_guests_even_with_a_signed_link(): void
    {
        $this->post('/uz/claims', $this->valid(['files' => [UploadedFile::fake()->create('akt.pdf', 50, 'application/pdf')]]));

        $url = URL::temporarySignedRoute('claims.file', now()->addMinutes(30), ['claim' => Claim::sole()->id, 'index' => 0]);
        $this->get($url)->assertForbidden();
    }
}
