<?php

namespace Modules\ApiPolic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model',
        'year',
        'vin',
        'license_plate',
        'color',
        'engine_type',
        'fuel_type',
        'transmission',
        'mileage',
        'status',
        'owner_id',
        'insurance_expires_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'mileage' => 'integer',
        'insurance_expires_at' => 'datetime',
    ];

    /**
     * Get the owner of the vehicle.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }

    /**
     * Scope a query to only include active vehicles.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter by brand.
     */
    public function scopeByBrand($query, string $brand)
    {
        return $query->where('brand', 'like', "%{$brand}%");
    }

    /**
     * Scope a query to filter by year range.
     */
    public function scopeByYearRange($query, int $from, int $to)
    {
        return $query->whereBetween('year', [$from, $to]);
    }
}
