<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Resources\ProductResource;
use App\Filament\Admin\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Admin\Resources\ProductResource\RelationManagers\SettingChangesRelationManager;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductPricingFormTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->product = Product::create([
            'name_uz' => 'Gaz ballon', 'name_ru' => 'Газ баллон', 'name_en' => 'Gas balloon',
            'route' => 'gas', 'is_active' => true,
        ]);
    }

    public function test_edit_page_shows_the_pricing_section(): void
    {
        $this->get(ProductResource::getUrl('edit', ['record' => $this->product]))
            ->assertOk()
            ->assertSee('Narx va chegaralar')
            ->assertSee('Standart: 5 000 000');
    }

    public function test_saving_stores_normalized_settings(): void
    {
        Livewire::test(EditProduct::class, ['record' => $this->product->getRouteKey()])
            ->fillForm([
                'settings.rate'        => '0.6',
                'settings.max'         => '300000000',
                'settings.presets'     => ['100000000', '50000000'],
                'settings.term_months' => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(
            ['rate' => 0.6, 'max' => 300_000_000, 'presets' => [50_000_000, 100_000_000]],
            $this->product->fresh()->settings,
        );
        $this->assertSame(3, $this->product->settingChanges()->count());
    }

    public function test_saving_rejects_a_step_the_slider_cannot_land_on(): void
    {
        Livewire::test(EditProduct::class, ['record' => $this->product->getRouteKey()])
            ->fillForm(['settings.step' => '7000000'])
            ->call('save')
            ->assertHasFormErrors(['settings.step']);

        $this->assertNull($this->product->fresh()->settings);
    }

    public function test_history_is_listed_on_the_edit_page(): void
    {
        $this->product->update(['settings' => ['rate' => 0.7]]);

        Livewire::test(SettingChangesRelationManager::class, [
            'ownerRecord' => $this->product,
            'pageClass'   => EditProduct::class,
        ])
            ->assertOk()
            ->assertSee('Stavka')
            ->assertSee('0,7%');
    }
}
