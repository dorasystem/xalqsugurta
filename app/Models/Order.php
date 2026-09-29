<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'product_key',
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

    /** Products whose policy is issued by PerformTransactionRequest after payment */
    public const POLICY_AFTER_PAYMENT = ['gas', 'property', 'kasko'];

    /** eshop contracts: a payment through the site's own Payme / Click is confirmed with eshop/payment */
    public const ESHOP_PAYMENT_CONFIRM = ['osgop', 'osgor', 'accident', 'tourist'];

    public function apiLogs(): HasMany
    {
        return $this->hasMany(ApiLog::class)->latest('id');
    }

    /** product_key (indexed) follows insurances_data._product_key, which every flow writes */
    protected static function booted(): void
    {
        static::saving(function (Order $order): void {
            $key = $order->insurances_data['_product_key'] ?? null;

            if (is_string($key) && $key !== '') {
                $order->attributes['product_key'] = substr($key, 0, 30);
            }
        });
    }

    /** Product key saved by createOrderAndRedirect() ("gas", "kasko", …) */
    public function getProductKeyAttribute(?string $value): ?string
    {
        return $value ?? $this->insurances_data['_product_key'] ?? null;
    }

    /** True when the policy is issued after payment and has not arrived yet */
    public function awaitsPolicy(): bool
    {
        return $this->status === self::STATUS_PAID
            && in_array($this->product_key, self::POLICY_AFTER_PAYMENT, true)
            && empty($this->insurances_response_data['download_url']);
    }

    /** Paid gas / property / KASKO orders still without a policy download link */
    public function scopeAwaitingPolicy(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PAID)
            ->whereIn('product_key', self::POLICY_AFTER_PAYMENT)
            ->whereNull('insurances_response_data->download_url');
    }

    /** True when a paid eshop contract has not been confirmed to the insurer yet */
    public function awaitsPaymentConfirmation(): bool
    {
        return $this->status === self::STATUS_PAID
            && in_array($this->product_key, self::ESHOP_PAYMENT_CONFIRM, true)
            && empty($this->insurances_response_data['payment_confirmed_at']);
    }

    /** Paid, but the insurer still owes the policy or has not been told about the payment */
    public function awaitsInsurer(): bool
    {
        return $this->awaitsPolicy() || $this->awaitsPaymentConfirmation();
    }

    /** Paid eshop contracts whose payment the insurer has not confirmed */
    public function scopeAwaitingPaymentConfirmation(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PAID)
            ->whereIn('product_key', self::ESHOP_PAYMENT_CONFIRM)
            ->whereNull('insurances_response_data->payment_confirmed_at');
    }

    /** awaitingPolicy() or awaitingPaymentConfirmation() */
    public function scopeAwaitingInsurer(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where(fn (Builder $q) => $q->awaitingPolicy())
            ->orWhere(fn (Builder $q) => $q->awaitingPaymentConfirmation()));
    }

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
