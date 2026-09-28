<?php

namespace App\Models;

use App\Services\ProductSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;

class Product extends Model
{
    protected $fillable = [
        'name_uz', 'name_ru', 'name_en',
        'desc_uz', 'desc_ru', 'desc_en',
        'content',
        'route',
        'icon', 'icon_color', 'icon_bg',
        'offerta_uz', 'offerta_ru', 'offerta_en',
        'rules_uz', 'rules_ru', 'rules_en',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings'  => 'array',
        'content'   => 'array',
    ];

    /** Tags an admin may use in the info page texts; everything else is stripped on output */
    private const SAFE_TAGS = '<p><br><strong><b><em><i><u><s><ul><ol><li><h2><h3><h4><blockquote><a><table><thead><tbody><tr><th><td>';

    protected static function booted(): void
    {
        static::updated(fn (Product $product) => $product->recordSettingChanges());
    }

    public function settingChanges(): HasMany
    {
        return $this->hasMany(ProductSettingChange::class)->latest();
    }

    /**
     * Writes one history row per changed setting key and for the on-sale switch.
     * Runs in the "updated" event, where getOriginal() still holds the previous values.
     */
    public function recordSettingChanges(): void
    {
        $changes = [];

        if ($this->wasChanged('is_active')) {
            $changes['is_active'] = [$this->getOriginal('is_active'), $this->is_active];
        }

        if ($this->wasChanged('settings')) {
            $old = $this->getOriginal('settings') ?? [];
            $new = $this->settings ?? [];

            foreach (array_keys(ProductSettings::LABELS) as $key) {
                if (($old[$key] ?? null) != ($new[$key] ?? null)) {
                    $changes[$key] = [$old[$key] ?? null, $new[$key] ?? null];
                }
            }
        }

        foreach ($changes as $key => [$from, $to]) {
            $this->settingChanges()->create([
                'user_id'   => auth()->id(),
                'field'     => $key,
                'old_value' => ProductSettings::display($key, $from),
                'new_value' => ProductSettings::display($key, $to),
            ]);
        }
    }

    /** Product route => category key (messages.product_categories.*) */
    public const CATEGORIES = [
        'osago'    => 'transport',
        'kasko'    => 'transport',
        'accident' => 'personal',
        'tourist'  => 'personal',
        'property' => 'property',
        'gas'      => 'property',
        'osgor'    => 'business',
        'osgop'    => 'business',
    ];

    public function categoryKey(): ?string
    {
        return self::CATEGORIES[$this->route] ?? null;
    }

    /**
     * Link to the product's application page in the current locale.
     * Relative, so it works behind the host proxy whatever Host header it sends.
     * If `route` is not a known route name (e.g. edited in the admin panel),
     * fall back to "/{locale}/{route}" like the old hardcoded link did,
     * but only for path-safe values (letters, digits, "-", "_", "/").
     */
    // ─── Info page (/{locale}/products/{route}) ───────────────────────────────

    /** ['about' => html, 'claim' => html, 'faq' => [[q, a], …]] for the locale, cleaned; empty parts dropped */
    public function info(?string $locale = null): array
    {
        $raw = $this->content[$locale ?? getCurrentLocale()] ?? [];

        $faq = collect($raw['faq'] ?? [])
            ->filter(fn ($item): bool => is_array($item) && filled($item['q'] ?? null) && filled($item['a'] ?? null))
            ->map(fn (array $item): array => ['q' => trim($item['q']), 'a' => trim($item['a'])])
            ->values()
            ->all();

        return array_filter([
            'about' => self::safeHtml($raw['about'] ?? null),
            'claim' => self::safeHtml($raw['claim'] ?? null),
            'faq'   => $faq,
        ]);
    }

    /** The info page has something to show in this locale */
    public function hasInfo(?string $locale = null): bool
    {
        return $this->info($locale) !== [];
    }

    public function infoUrl(): string
    {
        return route('product.show', ['locale' => getCurrentLocale(), 'product' => trim((string) $this->route, '/')], false);
    }

    /** Where the home page card leads: the info page when it is filled in, else straight to the application */
    public function cardUrl(): string
    {
        return $this->hasInfo() && Route::has('product.show') ? $this->infoUrl() : $this->url();
    }

    /**
     * Admin HTML reduced to formatting tags, with every attribute removed except a safe href
     * (no scripts, event handlers, styles or javascript: links); null when nothing is left.
     */
    public static function safeHtml(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1\s*>#is', '', $html);
        $html = strip_tags($html, self::SAFE_TAGS);
        $html = preg_replace_callback('/<(\w+)\b[^>]*>/', function (array $m): string {
            $tag = strtolower($m[1]);
            if ($tag === 'a' && preg_match('/\bhref\s*=\s*["\']?(https?:\/\/[^"\'\s>]+|\/[^"\'\s>]*|mailto:[^"\'\s>]+|tel:[^"\'\s>]+)/i', $m[0], $href)) {
                return '<a href="' . e($href[1]) . '" rel="noopener" target="_blank">';
            }

            return '<' . $tag . '>';
        }, $html);

        return trim(strip_tags($html)) === '' ? null : trim($html);
    }

    public function url(): string
    {
        $locale = getCurrentLocale();
        $slug   = trim((string) $this->route, '/');
        $name   = $slug . '.index';

        if ($slug !== '' && Route::has($name)) {
            return route($name, ['locale' => $locale], false);
        }

        // Only path-safe characters: the value can never become an external or scheme URL
        return preg_match('#^[a-z0-9_/-]+$#i', $slug) ? '/' . $locale . '/' . $slug : '/' . $locale;
    }
}
