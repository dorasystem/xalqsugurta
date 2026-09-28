<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One changed product setting (or the on-sale switch), written by Product::recordSettingChanges() */
class ProductSettingChange extends Model
{
    protected $fillable = ['product_id', 'user_id', 'field', 'old_value', 'new_value'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
