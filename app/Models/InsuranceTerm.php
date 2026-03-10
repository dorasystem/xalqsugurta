<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InsuranceTerm extends Model
{
    protected $fillable = [
        'product_code',
        'provider_term_id',
        'name_uz',
        'name_ru',
        'name_en',
        'months',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Accessor: localized name ─────────────────────────────────────────────

    public function getNameAttribute(): string
    {
        $col = 'name_' . app()->getLocale();
        return $this->attributes[$col] ?? $this->attributes['name_uz'] ?? '';
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForProduct(Builder $query, string $productCode): Builder
    {
        return $query->where('product_code', $productCode);
    }
}
