<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
