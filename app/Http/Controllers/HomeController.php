<?php

namespace App\Http\Controllers;

use App\Models\InfoPage;
use App\Models\Product;
use App\Services\OsagoPriceCalculator;
use App\Services\ProductSettings;
use App\Services\SiteSettings;
use Illuminate\Contracts\View\View;

/**
 * Home page: hero, quick quote, figures, products, how it works, claims, self-service, FAQ.
 * Products, rates and the OSAGO price come from the same code the application pages use.
 */
final class HomeController extends Controller
{
    /** Products sold to organizations: shown together in the wide "for business" tile */
    private const BUSINESS = ['osgor', 'osgop'];

    /** Order of the quick-quote tabs */
    private const QUICK = ['osago', 'kasko', 'property', 'gas', 'tourist', 'accident'];

    public function __construct(private readonly OsagoPriceCalculator $osagoPrice) {}

    public function index(): View
    {
        $products = Product::where('is_active', true)->orderBy('sort_order')->get();
        $byRoute  = $products->keyBy('route');
        $osago    = $byRoute->get('osago');

        return view('welcome', [
            'products'  => $products,
            'osago'     => $osago,
            'osagoFrom' => $osago ? $this->osagoFrom() : null,
            'tiles'     => $products->reject(fn (Product $p) => $p->route === 'osago' || in_array($p->route, self::BUSINESS, true))->values(),
            'business'  => $products->filter(fn (Product $p) => in_array($p->route, self::BUSINESS, true))->values(),
            'quick'     => collect(self::QUICK)->map(fn (string $r) => $byRoute->get($r))->filter()->values(),
            'rates'     => $products->mapWithKeys(fn (Product $p) => [$p->route => $this->rate($p)])->filter()->all(),
            'stats'     => SiteSettings::stats(),
            'mock'      => $this->osagoPrice->calculate('01A123BC', 2, '1', 'limited')['amount'],
            'documents' => [
                'licenses'             => InfoPage::link('licenses'),
                'financial-statements' => InfoPage::link('financial-statements'),
                'about'                => InfoPage::link('about'),
            ],
        ]);
    }

    /** Cheapest 12-month OSAGO for a passenger car outside Tashkent, limited drivers */
    private function osagoFrom(): int
    {
        return (int) $this->osagoPrice->calculate('40A123BC', 2, '1', 'limited')['amount'];
    }

    /** "0,5" for products priced as sum × rate (admin settings applied), else null */
    private function rate(Product $product): ?string
    {
        if (!ProductSettings::hasRate($product->route)) {
            return null;
        }

        $rate = ProductSettings::effective($product->route, $product->settings)['rate'] ?? null;

        return $rate === null ? null : str_replace('.', ',', rtrim(rtrim(number_format((float) $rate, 2, '.', ''), '0'), '.'));
    }
}
