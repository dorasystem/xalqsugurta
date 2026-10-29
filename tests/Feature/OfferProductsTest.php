<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\OfferProductResource;
use App\Models\CallbackRequest;
use App\Models\OfferProduct;
use App\Models\User;
use Database\Seeders\OfferProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferProductsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OfferProductSeeder::class);
    }

    public function test_seeder_adds_three_products_once_and_keeps_edits(): void
    {
        OfferProduct::where('slug', 'family')->update(['title_uz' => 'Tahrirlangan']);
        $this->seed(OfferProductSeeder::class);

        $this->assertSame(3, OfferProduct::count());
        $this->assertSame('Tahrirlangan', OfferProduct::where('slug', 'family')->value('title_uz'));
    }

    public function test_page_shows_texts_terms_and_the_form_for_the_product(): void
    {
        $this->get('/uz/insurance/family')->assertOk()
            ->assertSee('«Mening oilam»')
            ->assertSee('24/7 himoya')
            ->assertSee('Sug‘urtalangan shaxsning vafoti')
            ->assertSee('name="members"', false)
            ->assertDontSee('name="company"', false);

        $this->get('/ru/insurance/collective')->assertOk()
            ->assertSee('Коллективное страхование от несчастных случаев')
            ->assertSee('При инвалидности 2 группы.')
            ->assertSee('name="employees"', false)
            ->assertSee('name="company"', false);

        // The section strip follows the product's audience
        $this->get('/uz/insurance/all_risks')->assertOk()->assertSee('Dividendlar');
        $this->get('/uz/insurance/family')->assertOk()->assertDontSee('Dividendlar');

        $this->get('/uz/insurance/nope')->assertNotFound();

        OfferProduct::where('slug', 'all_risks')->update(['is_active' => false]);
        $this->get('/uz/insurance/all_risks')->assertNotFound();
    }

    public function test_menu_links_to_our_pages_while_they_are_active(): void
    {
        $this->get('/uz')->assertOk()
            ->assertSee(route('offers.show', ['locale' => 'uz', 'slug' => 'family']), false)
            ->assertDontSee('https://xalqsugurta.uz/strahovanie-moyasemya', false);

        OfferProduct::where('slug', 'family')->update(['is_active' => false]);
        $this->app->forgetInstance('site_menu.offers');
        $this->get('/uz')->assertSee('https://xalqsugurta.uz/strahovanie-moyasemya', false);

        $this->get('/uz/business')->assertOk()
            ->assertSee(route('offers.show', ['locale' => 'uz', 'slug' => 'collective']), false);
    }

    public function test_request_is_stored_with_the_product_fields(): void
    {
        $this->post('/uz/insurance/family', ['name' => 'Ali', 'phone' => '90 123 45 67', 'members' => 5])
            ->assertRedirect(route('offers.show', ['locale' => 'uz', 'slug' => 'family']) . '#apply');

        $request = CallbackRequest::sole();
        $this->assertSame(CallbackRequest::TOPIC_PRODUCT, $request->topic);
        $this->assertSame('998901234567', $request->phone);
        $this->assertStringContainsString('«Mening oilam»', $request->message);
        $this->assertStringContainsString('Oila a’zolari soni: 5+', $request->message);

        $this->post('/uz/insurance/collective', ['company' => 'Test MChJ', 'phone' => '998901234567', 'employees' => 40]);
        $this->assertStringContainsString('Xodimlar soni: 40', CallbackRequest::latest('id')->first()->message);
        $this->assertSame('Test MChJ', CallbackRequest::latest('id')->first()->name);
    }

    public function test_validation_and_honeypot(): void
    {
        $this->post('/uz/insurance/collective', ['phone' => '1'])->assertSessionHasErrors(['company', 'phone', 'employees']);
        $this->post('/uz/insurance/family', ['website' => 'x', 'name' => 'Bot', 'phone' => '998901234567', 'members' => 1]);

        $this->assertSame(0, CallbackRequest::count());
    }

    public function test_admin_pages(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(OfferProductResource::getUrl('index'))->assertOk()->assertSee('/insurance/family');
        $this->get(OfferProductResource::getUrl('edit', ['record' => OfferProduct::first()]))->assertOk();
    }
}
