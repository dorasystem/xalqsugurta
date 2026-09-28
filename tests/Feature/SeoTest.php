<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_products_on_sale_in_every_locale(): void
    {
        Product::create(['name_uz' => 'Gaz', 'name_ru' => 'Газ', 'name_en' => 'Gas', 'route' => 'gas', 'is_active' => true, 'sort_order' => 1]);
        Product::create(['name_uz' => 'Kasko', 'name_ru' => 'Каско', 'name_en' => 'Kasko', 'route' => 'kasko', 'is_active' => false, 'sort_order' => 2]);

        $xml = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->getContent();

        foreach (['uz', 'ru', 'en'] as $l) {
            $this->assertStringContainsString('<loc>' . url("/$l/gas") . '</loc>', $xml);
            $this->assertStringContainsString('<loc>' . url("/$l") . '</loc>', $xml);
        }
        $this->assertStringNotContainsString('/kasko', $xml);
        $this->assertStringContainsString('hreflang="ru"', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_robots_points_to_the_sitemap_and_hides_private_pages(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap: ' . url('/sitemap.xml'))
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /*/payment/');
    }
}
