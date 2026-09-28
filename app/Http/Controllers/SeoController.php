<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

/** sitemap.xml and robots.txt, built from the products on sale */
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
