<?php

namespace App\Support;

use App\Models\InfoPage;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;

/**
 * The site is split into three sections — individuals, business, career — each with its own
 * header menu. The menus hold the old xalqsugurta.uz menu, every item placed in the section(s)
 * it belongs to; the "Insurance" group lists the old site's products of that section.
 */
final class SiteMenu
{
    /** section => [route name, label key] */
    public const SECTIONS = [
        'private'  => ['home', 'messages.sections.private'],
        'business' => ['business', 'messages.sections.business'],
        'career'   => ['career.index', 'messages.sections.career'],
    ];

    /** Route name prefixes that belong to a section other than "private" */
    private const SECTION_ROUTES = [
        'business' => ['business', 'osgor.', 'osgop.', 'tenders.'],
        'career'   => ['career.'],
    ];

    /**
     * Products of the old site's catalogue (/sugurta#private, #corporate): label key => [our product
     * route, old-site path]. Our page is used while the product is on sale, else the old one.
     */
    private const PRODUCTS = [
        'private' => [
            'accident' => ['accident', 'strahovaniens'],
            'family'   => [null, 'strahovanie-moyasemya'],
            'home'     => ['property', 'imuschestvo-moydom'],
            'kasko'    => ['kasko', 'kasko'],
            'osago'    => ['osago', 'osgo'],
        ],
        'business' => [
            'all_risks'  => [null, 'strahovanie-imushestva-ot-vseh-riskov'],
            'collective' => [null, 'accident-insurance'],
            'osgop'      => ['osgop', 'osgop'],
            'osgor'      => ['osgor', 'osgor'],
            'osago_corp' => ['osago', 'osgo-corp'],
        ],
    ];

    /** Section of the current page (by route name); everything else belongs to individuals */
    public static function current(): string
    {
        $name = (string) Route::currentRouteName();

        foreach (self::SECTION_ROUTES as $section => $prefixes) {
            foreach ($prefixes as $prefix) {
                if ($name === rtrim($prefix, '.') || str_starts_with($name, $prefix)) {
                    return $section;
                }
            }
        }

        return 'private';
    }

    public static function sectionUrl(string $section, ?string $locale = null): string
    {
        return route(self::SECTIONS[$section][0], ['locale' => $locale ?? getCurrentLocale()]);
    }

    /**
     * Header groups of a section: [['label', 'url', 'items' => [['label', 'url'], …]], …].
     * A group without items is a plain link.
     */
    public static function groups(string $section, ?string $locale = null): array
    {
        $locale ??= getCurrentLocale();
        $info   = fn (string $key): array => [__t('messages.header_' . self::INFO_LABELS[$key]), InfoPage::link($key, $locale)];
        $route  = fn (string $name, array $params = []): string => route($name, ['locale' => $locale] + $params);
        $press  = [
            [__t('messages.header_press_center'), $route('news.index')],
            [__t('messages.sections.appeals'), $route('callback')],
        ];

        $groups = match ($section) {
            'business' => [
                self::group(__t('messages.header_insurance'), $route('business') . '#products', self::products('business', $locale)),
                self::group(__t('messages.header_shareholders'), InfoPage::link('affiliates', $locale), [
                    $info('affiliates'), $info('stocks'), $info('dividend'), $info('essential-facts'),
                ]),
                self::group(__t('messages.header_about_company'), InfoPage::link('about', $locale), [
                    $info('about'), $info('financial-statements'), $info('audit'), $info('business-plan'),
                    $info('collegial-bodies'), $info('subject-objectives'), $info('company-structure'),
                    $info('licenses'), $info('regulation'),
                    [__t('messages.header_tenders'), $route('tenders.index')],
                ]),
                self::group(__t('messages.header_useful_info'), InfoPage::link('legislation', $locale), [
                    $info('legislation'), $info('tax-benefits'),
                ]),
                self::group(__t('messages.header_press_center'), $press[0][1], $press),
            ],
            'career' => [
                self::group(__t('messages.header_vacancies'), $route('career.index'), []),
                self::group(__t('messages.header_about_company'), InfoPage::link('about', $locale), [
                    $info('about'), $info('management'), $info('company-structure'),
                ]),
                self::group(__t('messages.header_press_center'), $press[0][1], $press),
            ],
            default => [
                self::group(__t('messages.header_insurance'), $route('home') . '#products', self::products('private', $locale)),
                self::group(__t('messages.header_useful_info'), InfoPage::link('legislation', $locale), [
                    $info('legislation'), $info('useful-information'),
                ]),
                self::group(__t('messages.header_press_center'), $press[0][1], $press),
                self::group(__t('messages.header_about_company'), InfoPage::link('about', $locale), [
                    $info('about'), $info('management'), $info('licenses'), $info('regulation'),
                ]),
            ],
        };

        return $groups;
    }

    /** InfoPage key => messages.header_* label */
    private const INFO_LABELS = [
        'about'                => 'about_company',
        'management'           => 'management',
        'licenses'             => 'licenses',
        'financial-statements' => 'financial_reports',
        'audit'                => 'audit',
        'business-plan'        => 'business_plan',
        'collegial-bodies'     => 'collegial_bodies',
        'subject-objectives'   => 'objectives',
        'company-structure'    => 'company_structure',
        'regulation'           => 'regulations',
        'affiliates'           => 'affiliated_persons',
        'stocks'               => 'shares',
        'dividend'             => 'dividends',
        'essential-facts'      => 'important_facts',
        'legislation'          => 'insurance_legislation',
        'tax-benefits'         => 'tax_benefits_basis',
        'useful-information'   => 'insurance_terms',
    ];

    private static function group(string $label, string $url, array $items): array
    {
        return [
            'label' => $label,
            'url'   => $url,
            'items' => array_map(fn (array $i): array => ['label' => $i[0], 'url' => $i[1]], $items),
        ];
    }

    /** The section's products: our application / info page while on sale, else the old site */
    private static function products(string $section, string $locale): array
    {
        $onSale = self::productsOnSale();

        return collect(self::PRODUCTS[$section])->map(function (array $p, string $key) use ($onSale, $locale): array {
            [$route, $oldPath] = $p;
            $url = $route !== null && isset($onSale[$route]) ? $onSale[$route]->cardUrl() : 'https://xalqsugurta.uz/' . $oldPath;

            return [__t('messages.sections.products.' . $key), $url];
        })->values()->all();
    }

    /** route => Product on sale (once per request; empty before the products table exists) */
    private static function productsOnSale(): array
    {
        if (!app()->bound('site_menu.products')) {
            try {
                $products = Product::where('is_active', true)->get()->keyBy('route')->all();
            } catch (QueryException) {
                $products = [];
            }
            app()->instance('site_menu.products', $products);
        }

        return app('site_menu.products');
    }

    /** Old-site page by its routes.php key (news, questionnaire …) */
    public static function oldSite(string $locale, string $routeKey): string
    {
        $slug = __('routes.' . $routeKey, [], $locale);

        return 'https://xalqsugurta.uz/' . $locale . '/' . (str_starts_with($slug, 'routes.') ? '' : $slug);
    }
}
