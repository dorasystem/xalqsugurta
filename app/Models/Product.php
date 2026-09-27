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

    public function url(): string
    {
        $name = $this->route === 'osago' ? 'osago.main' : $this->route . '.index';

        return Route::has($name)
            ? route($name, ['locale' => getCurrentLocale()])
            : route('home', ['locale' => getCurrentLocale()]);
    }
}
