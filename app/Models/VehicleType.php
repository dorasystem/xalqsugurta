<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    protected $fillable = [
        'provider_vehicle_type_id',
        'name_uz',
        'name_ru',
        'name_en',
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
}
