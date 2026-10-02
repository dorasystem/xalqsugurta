<?php

namespace Tests\Feature;

use App\Models\InfoPage;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Product::create(['name_uz' => 'KASKO', 'name_ru' => 'КАСКО', 'name_en' => 'KASKO', 'desc_uz' => 'Transport vositasini sug‘urta qilish', 'route' => 'kasko', 'is_active' => true, 'sort_order' => 1]);
        Product::create(['name_uz' => 'OSGOR', 'name_ru' => 'ОСГОР', 'name_en' => 'OSGOR', 'desc_uz' => 'Xodimlarni sug‘urta qilish', 'route' => 'osgor', 'is_active' => false, 'sort_order' => 2]);
        InfoPage::create(['key' => 'licenses', 'section' => 'about', 'is_published' => true, 'title_uz' => 'Litsenziyalar', 'body_ru' => '<p>Лицензия на страхование</p>']);
        InfoPage::create(['key' => 'audit', 'section' => 'about', 'is_published' => false, 'title_uz' => 'Audit litsenziya']);
    }

    public function test_finds_products_pages_and_services(): void
    {
        // Russian product name on the Uzbek page; the apostrophe kind does not matter
        $this->get('/uz/search?q=каско')->assertOk()->assertSee('KASKO')->assertSee('Topildi: 1');
        $this->get("/uz/search?q=sug'urta+qilish")->assertOk()->assertSee('KASKO')->assertDontSee('OSGOR');

        $this->get('/uz/search?q=litsenz')->assertOk()
            ->assertSee('Litsenziyalar')
            ->assertSee(route('info.show', ['locale' => 'uz', 'key' => 'licenses']), false)
            ->assertDontSee('Audit litsenziya');

        $this->get('/uz/search?q=' . urlencode('qo\'ng\'iroq'))->assertOk()->assertSee(route('callback', ['locale' => 'uz']), false);
    }

    public function test_short_empty_and_missing_queries(): void
    {
        $this->get('/uz/search')->assertOk()->assertDontSee('Topildi');
        $this->get('/uz/search?q=a')->assertOk()->assertSee('Kamida 2 ta harf')->assertDontSee('Topildi');
        $this->get('/ru/search?q=zzzz')->assertOk()->assertSee('Найдено: 0')->assertSee('ничего не найдено');
    }

    public function test_works_before_the_info_pages_migration(): void
    {
        // Production ran the branch before migrating: a query must not 500 on the missing table
        Schema::drop('info_pages');
        Cache::flush();

        $this->get('/uz/search?q=kasko')->assertOk()->assertSee('KASKO');
        $this->get('/uz/search?q=zzzz')->assertOk();
        $this->get('/uz/info/licenses')->assertNotFound();
    }

    public function test_header_has_the_search_form(): void
    {
        $this->get('/uz')->assertOk()
            ->assertSee('action="' . route('search', ['locale' => 'uz']) . '"', false)
            ->assertSee('aria-controls="site_search"', false);
    }
}
