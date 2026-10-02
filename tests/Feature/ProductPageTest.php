<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\ProductResource\Pages\EditProduct;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'name_uz' => 'Gaz ballon', 'name_ru' => 'Газ баллон', 'name_en' => 'Gas balloon',
            'desc_uz' => 'Gaz jihozlari uchun', 'route' => 'gas', 'is_active' => true, 'sort_order' => 1,
            'content' => ['uz' => [
                'about' => '<p onclick="steal()">Yong\'in va <strong>portlash</strong> qoplanadi<script>alert(1)</script></p><a href="javascript:x()">bad</a>',
                'claim' => '<ol><li>Qo\'ng\'iroq qiling</li></ol>',
                'faq'   => [['q' => 'Qancha turadi?', 'a' => "Summaning 0,5%\nOnlayn to'lanadi"], ['q' => '', 'a' => 'bo\'sh savol']],
            ]],
            'rules_uz' => 'rules/gas.pdf',
        ], $attrs));
    }

    public function test_info_page_shows_clean_content_faq_and_the_buy_button(): void
    {
        $this->product();

        $html = $this->get('/uz/products/gas')
            ->assertOk()
            ->assertSee('Gaz ballon')
            ->assertSee('<strong>portlash</strong>', false)
            ->assertSee('<li>Qo\'ng\'iroq qiling</li>', false)
            ->assertSee('Qancha turadi?')
            ->assertSee("Summaning 0,5%<br />\nOnlayn to&#039;lanadi", false)
            ->assertSee('/storage/rules/gas.pdf', false)
            ->assertSee('href="/uz/gas"', false)
            ->getContent();

        $this->assertStringNotContainsString('steal()', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringNotContainsString('javascript:x()', $html);
        $this->assertStringNotContainsString('bo\'sh savol', $html);
    }

    public function test_locale_without_texts_goes_to_the_application(): void
    {
        $this->product();

        $this->get('/ru/products/gas')->assertRedirect('/ru/gas');
    }

    public function test_unknown_or_inactive_products_are_404(): void
    {
        $this->product(['is_active' => false]);

        $this->get('/uz/products/gas')->assertNotFound();
        $this->get('/uz/products/nope')->assertNotFound();
    }

    public function test_home_card_leads_to_the_info_page_only_when_it_has_texts(): void
    {
        $this->product();
        Product::create(['name_uz' => 'KASKO', 'name_ru' => 'КАСКО', 'name_en' => 'KASKO', 'route' => 'kasko', 'is_active' => true, 'sort_order' => 2]);

        $this->get('/uz')
            ->assertOk()
            ->assertSee('href="/uz/products/gas"', false)
            ->assertSee('href="/uz/kasko"', false)
            ->assertSee(__t('messages.product_page.more'));

        $this->assertStringContainsString('/uz/products/gas', $this->get('/sitemap.xml')->getContent());
    }

    public function test_admin_saves_the_page_texts(): void
    {
        $product = $this->product(['content' => null, 'rules_uz' => null]);
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm([
                'content.uz.about' => '<p>Yangi matn</p>',
                'content.uz.faq'   => [['q' => 'Savol?', 'a' => 'Javob.']],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertStringContainsString('Yangi matn', $product->info('uz')['about']);
        $this->assertSame([['q' => 'Savol?', 'a' => 'Javob.']], $product->info('uz')['faq']);
        $this->assertTrue($product->hasInfo('uz'));
        $this->assertFalse($product->hasInfo('ru'));
    }
}
