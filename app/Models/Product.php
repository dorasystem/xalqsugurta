<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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
     * fall back to "/{locale}/{route}" like the old hardcoded link did.
     */
    public function url(): string
    {
        $locale = getCurrentLocale();
        $slug   = trim((string) $this->route, '/');
        $name   = $slug === 'osago' ? 'osago.main' : $slug . '.index';

        if ($slug !== '' && Route::has($name)) {
            return route($name, ['locale' => $locale], false);
        }

        if (str_starts_with($slug, 'http')) {
            return $slug;
        }

        return $slug !== '' ? '/' . $locale . '/' . $slug : '/' . $locale;
    }
}
