<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * One Click SHOP API transaction. The row id is the merchant_prepare_id returned in Prepare.
 * status: prepared → paid | cancelled (App\Services\Payments\ClickShopApi).
 */
class ClickUz extends Model
{
    use HasFactory;

    public const STATUS_PREPARED  = 'prepared';
    public const STATUS_PAID      = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'click_trans_id', 'click_paydoc_id', 'merchant_trans_id', 'amount',
        'sign_time', 'situation', 'status', 'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];
}
