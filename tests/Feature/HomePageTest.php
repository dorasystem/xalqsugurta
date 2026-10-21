<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\SiteSettingsPage;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $route, int $sort, array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name_uz' => strtoupper($route), 'name_ru' => strtoupper($route), 'name_en' => strtoupper($route),
            'desc_uz' => 'Tavsif ' . $route, 'route' => $route, 'is_active' => true, 'sort_order' => $sort,
        ], $attributes));
    }

    public function test_home_page_shows_products_prices_and_sections(): void
    {
        $this->product('osago', 1);
        $this->product('gas', 2, ['settings' => ['rate' => 0.7]]);
        $this->product('osgor', 3);
        $this->product('kasko', 4, ['is_active' => false]);

        $this->get('/uz')
            ->assertOk()
            ->assertSee('160 000 so‘mdan')                       // OsagoPriceCalculator: car outside Tashkent, 12 months
            ->assertSee('192 000 so\'m')                         // the sample e-policy (Tashkent)
            ->assertSee('summaning 0,7%')                        // admin rate for gas
            ->assertSee('action="/uz/osago"', false)             // quick OSAGO form
            ->assertSee('BIZNES UCHUN')
            ->assertSee('href="/uz/osgor"', false)
            ->assertDontSee('href="/uz/kasko"', false)
            ->assertSee('50+')
            ->assertSee(route('claims.create', ['locale' => 'uz']), false)
            ->assertSee(route('my-policies', ['locale' => 'uz']), false);

        $this->get('/ru')->assertOk()->assertSee('от 160 000 сум')->assertSee('Три шага. Без офиса.');
    }

    public function test_quick_form_prefills_the_osago_vehicle_step(): void
    {
        $this->product('osago', 1);

        $this->get('/uz/osago?gov_number=01 a 123 bc&tech_passport_seria=aaf&tech_passport_number=12-34567"')
            ->assertOk()
            ->assertSee('value="01A123BC"', false)
            ->assertSee('value="AAF"', false)
            ->assertSee('value="1234567"', false);
    }

    public function test_admin_edits_and_hides_the_figures(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(SiteSettingsPage::class)
            ->assertFormSet(['site.stats.1.value' => '50+', 'site.stats.2.label_ru' => 'лет на страховом рынке'])
            ->fillForm([
                'site.stats.1.value'    => '60+',
                'site.stats.1.label_uz' => 'filial',
                'site.stats.4.value'    => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/uz')->assertOk()->assertSee('60+')->assertSee('filial')->assertDontSee('1 mln+');
        $this->get('/ru')->assertOk()->assertSee('60+')->assertSee('филиалов и центров обслуживания');
    }
}
