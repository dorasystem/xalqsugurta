<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Insured-event report sent from the site (/{locale}/claims), handled in the admin panel */
class Claim extends Model
{
    public const STATUS_NEW       = 'new';
    public const STATUS_REVIEW    = 'in_review';
    public const STATUS_DOCUMENTS = 'documents';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_PAID      = 'paid';

    /** Admin labels (Uzbek); the site uses messages.claims.status_* */
    public const STATUS_LABELS = [
        self::STATUS_NEW       => 'Yangi',
        self::STATUS_REVIEW    => 'Ko\'rib chiqilmoqda',
        self::STATUS_DOCUMENTS => 'Hujjat kerak',
        self::STATUS_APPROVED  => 'Tasdiqlandi',
        self::STATUS_REJECTED  => 'Rad etildi',
        self::STATUS_PAID      => 'To\'landi',
    ];

    public const STATUS_COLORS = [
        self::STATUS_NEW       => 'warning',
        self::STATUS_REVIEW    => 'info',
        self::STATUS_DOCUMENTS => 'danger',
        self::STATUS_APPROVED  => 'success',
        self::STATUS_REJECTED  => 'gray',
        self::STATUS_PAID      => 'success',
    ];

    protected $fillable = [
        'number', 'order_id', 'product', 'policy_number', 'full_name', 'phone', 'event_date',
        'description', 'files', 'status', 'public_note', 'internal_note', 'handled_by', 'locale',
    ];

    protected $casts = [
        'event_date' => 'date',
        'files'      => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /** "ZH-260928-4821": date + random digits, so numbers can't be guessed in sequence */
    public static function newNumber(): string
    {
        do {
            $number = 'ZH-' . now()->format('ymd') . '-' . random_int(1000, 9999);
        } while (self::where('number', $number)->exists());

        return $number;
    }
}
