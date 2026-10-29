<?php

namespace App\Http\Controllers;

use App\Models\InfoPage;
use App\Models\News;
use App\Models\Product;
use App\Models\Tender;
use App\Models\Vacancy;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

/** sitemap.xml and robots.txt, built from the products on sale and the published company pages */
final class SeoController extends Controller
{
    private const LOCALES = ['uz', 'ru', 'en'];

    public function sitemap(): Response
    {
        // [route name, parameters, changefreq, priority]
        $pages = [['home', [], 'daily', '1.0'], ['my-policies', [], 'monthly', '0.3']];

        foreach (Product::where('is_active', true)->orderBy('sort_order')->get() as $product) {
            $slug = trim((string) $product->route, '/');

            if (collect(self::LOCALES)->contains(fn (string $l): bool => $product->hasInfo($l))) {
                $pages[] = ['product.show', ['product' => $slug], 'weekly', '0.9'];
            }
            if (Route::has($slug . '.index')) {
                $pages[] = [$slug . '.index', [], 'weekly', '0.8'];
            }
        }

        $pages[] = ['business', [], 'weekly', '0.8'];
        $pages[] = ['career.index', [], 'weekly', '0.6'];
        try {
            foreach (Vacancy::open()->pluck('slug') as $slug) {
                $pages[] = ['career.show', ['slug' => $slug], 'weekly', '0.5'];
            }
        } catch (QueryException) {
            // vacancies not migrated yet
        }

        $pages[] = ['branches', [], 'monthly', '0.6'];
        $pages[] = ['news.index', [], 'daily', '0.6'];
        try {
            foreach (News::visible()->pluck('slug') as $slug) {
                $pages[] = ['news.show', ['slug' => $slug], 'monthly', '0.5'];
            }
        } catch (QueryException) {
            // news not migrated yet
        }

        $pages[] = ['tenders.index', [], 'weekly', '0.6'];
        try {
            foreach (Tender::visible()->pluck('slug') as $slug) {
                $pages[] = ['tenders.show', ['slug' => $slug], 'monthly', '0.5'];
            }
        } catch (QueryException) {
            // tenders not migrated yet: the sitemap must still work
        }

        foreach (InfoPage::publishedKeys() as $key) {
            $pages[] = ['info.show', ['key' => $key], 'monthly', '0.5'];
        }

        $xml = view('seo.sitemap', ['pages' => $pages, 'locales' => self::LOCALES])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /api/',
            'Disallow: /*/payment/',
            'Disallow: /*/my-policies',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
