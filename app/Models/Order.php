<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'amount',
        'state',
        'payment_type',
        'insurance_id',
        'phone',
        'insurances_data',
        'insurances_response_data',
        'payme_url',
        'click_url',
        'status',
        'contractStartDate',
        'contractEndDate',
        'insuranceProductName',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'state' => 'integer',
        'insurances_data' => 'array',
        'insurances_response_data' => 'array',
        'contractStartDate' => 'datetime',
        'contractEndDate' => 'datetime',
    ];

    /**
     * Order statuses
     */
    public const STATUS_NEW = 'new';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';

    /**
     * Payment types
     */
    public const PAYMENT_CLICK = 'click';
    public const PAYMENT_PAYME = 'payme';

    /** Status => Uzbek label (admin panel) */
    public const STATUS_LABELS = [
        self::STATUS_NEW       => 'Yangi',
        self::STATUS_PENDING   => 'Kutilmoqda',
        self::STATUS_PAID      => 'To\'langan',
        self::STATUS_CANCELLED => 'Bekor qilingan',
        self::STATUS_FAILED    => 'Xato',
    ];

    /** Status => Filament color */
    public const STATUS_COLORS = [
        self::STATUS_NEW       => 'info',
        self::STATUS_PENDING   => 'warning',
        self::STATUS_PAID      => 'success',
        self::STATUS_CANCELLED => 'danger',
        self::STATUS_FAILED    => 'danger',
    ];

    public static function statusLabel(?string $status): string
    {
        return self::STATUS_LABELS[$status] ?? (string) $status;
    }

    public static function statusColor(?string $status): string
    {
        return self::STATUS_COLORS[$status] ?? 'gray';
    }

    /** Applicant data, whatever shape the product flow stored it in */
    public function getApplicantAttribute(): array
    {
        $applicant = $this->insurances_data['applicant'] ?? [];

        if (!is_array($applicant)) {
            return [];
        }

        // OSGOP nests the applicant under 'person' or 'organization'
        return $applicant['person'] ?? $applicant['organization'] ?? $applicant;
    }

    /** Client name: person's full name or organization name */
    public function getClientNameAttribute(): ?string
    {
        $a = $this->applicant;

        if (!empty($a['lastname'])) {
            return trim($a['lastname'] . ' ' . ($a['firstname'] ?? '') . ' ' . ($a['middlename'] ?? ''));
        }

        return $a['name'] ?? null;
    }
}
