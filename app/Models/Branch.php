<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Branch / office shown on /{locale}/branches */
class Branch extends Model
{
    protected $fillable = [
        'name_uz', 'name_ru', 'name_en', 'address_uz', 'address_ru', 'address_en',
        'manager', 'phones', 'lat', 'lng', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'phones'    => 'array',
        'lat'       => 'float',
        'lng'       => 'float',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /** Name in the locale, falling back to Russian then Uzbek (the old site had Russian only) */
    public function name(string $locale): string
    {
        return (string) ($this->{'name_' . $locale} ?: ($this->name_ru ?: $this->name_uz));
    }

    public function address(string $locale): ?string
    {
        return $this->{'address_' . $locale} ?: ($this->address_ru ?: $this->address_uz);
    }

    /** "tel:" value of a phone as written on the site: digits only, with the country code */
    public static function tel(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        return '+' . (strlen($digits) === 9 ? '998' . $digits : $digits);
    }

    /** Yandex Maps link to the branch's point (no API key needed), or null without coordinates */
    public function mapUrl(): ?string
    {
        return $this->lat && $this->lng
            ? 'https://yandex.uz/maps/?pt=' . $this->lng . ',' . $this->lat . '&z=16&l=map'
            : null;
    }
}
