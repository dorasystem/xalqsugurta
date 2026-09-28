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
        'route',
        'icon', 'icon_color', 'icon_bg',
        'offerta_uz', 'offerta_ru', 'offerta_en',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings'  => 'array',
    ];

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
