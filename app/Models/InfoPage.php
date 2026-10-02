<?php

namespace App\Models;

use App\Support\SafeHtml;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

/**
 * Company / disclosure page (/{locale}/info/{key}). The old site's pages are imported by
 * `php artisan site:import-old-pages`; staff edit and publish them in the admin panel.
 */
class InfoPage extends Model
{
    public const SECTIONS = [
        'about'        => 'Kompaniya haqida',
        'shareholders' => 'Aksiyadorlar va investorlar',
        'useful'       => 'Foydali ma\'lumotlar',
    ];

    /**
     * key => [section, old-site path per locale]. null path: the page has no version in that
     * locale on the old site. Most paths come from resources/lang/{l}/routes.php.
     */
    public const SOURCES = [
        'about'              => ['about', 'routes'],
        'management'         => ['about', 'routes'],
        'licenses'           => ['about', 'routes'],
        'financial-statements' => ['about', 'routes'],
        'audit'              => ['about', 'routes'],
        'business-plan'      => ['about', 'routes'],
        'vacancies'          => ['about', 'routes'],
        'collegial-bodies'   => ['about', 'routes'],
        'subject-objectives' => ['about', 'routes'],
        'company-structure'  => ['about', 'routes'],
        'regulation'         => ['about', 'routes'],
        'branches'           => ['about', 'routes'],
        'affiliates'         => ['shareholders', 'routes'],
        'stocks'             => ['shareholders', 'routes'],
        'dividend'           => ['shareholders', 'routes'],
        'essential-facts'    => ['shareholders', 'routes'],
        'useful-information' => ['useful', 'routes'],
        'legislation'        => ['useful', ['ru' => 'zakonodatelstvo-v-sfere-strahovaniya']],
        'tax-benefits'       => ['useful', ['ru' => 'osnovanie-predostavleniya-nalogovyh-lgot']],
    ];

    private const CACHE_KEY = 'info_pages.published';

    /** Container key holding publishedKeys() for this request: the header asks on every menu link */
    private const MEMO = 'info_pages.published.memo';

    protected $fillable = [
        'key', 'section', 'sort_order', 'is_published', 'imported_at',
        'title_uz', 'title_ru', 'title_en', 'body_uz', 'body_ru', 'body_en',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'imported_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        $forget = function (): void {
            Cache::forget(self::CACHE_KEY);
            app()->forgetInstance(self::MEMO);
        };

        static::saved($forget);
        static::deleted($forget);
    }

    /** Old-site path of a page in a locale, or null when it has none */
    public static function oldPath(string $key, string $locale): ?string
    {
        $source = self::SOURCES[$key][1] ?? null;

        if ($source === 'routes') {
            $slug = __('routes.' . $key, [], $locale);

            return str_starts_with($slug, 'routes.') ? null : $slug;
        }

        return is_array($source) ? ($source[$locale] ?? null) : null;
    }

    /** Title in the locale, falling back to Russian then Uzbek (some pages exist only in Russian) */
    public function title(string $locale): ?string
    {
        return $this->{'title_' . $locale} ?: ($this->title_ru ?: $this->title_uz);
    }

    public function body(string $locale): ?string
    {
        return SafeHtml::clean($this->{'body_' . $locale}) ?? SafeHtml::clean($this->body_ru) ?? SafeHtml::clean($this->body_uz);
    }

    /** Keys of published pages (cached; cleared on save) */
    public static function publishedKeys(): array
    {
        try {
            if (!app()->bound(self::MEMO)) {
                app()->instance(self::MEMO, Cache::rememberForever(self::CACHE_KEY, fn () => self::where('is_published', true)->pluck('key')->all()));
            }

            return app(self::MEMO);
        } catch (QueryException) {
            return [];   // not migrated yet
        }
    }

    /**
     * Link for the header / footer: our page once it is published, else the old site
     * (so nothing breaks while pages are being checked).
     */
    public static function link(string $key, ?string $locale = null): string
    {
        $locale ??= getCurrentLocale();

        if (in_array($key, self::publishedKeys(), true)) {
            return route('info.show', ['locale' => $locale, 'key' => $key], false);
        }

        $path = self::oldPath($key, $locale);

        return $path !== null
            ? 'https://xalqsugurta.uz/' . $locale . '/' . $path
            : 'https://xalqsugurta.uz/ru/' . self::oldPath($key, 'ru');
    }
}
