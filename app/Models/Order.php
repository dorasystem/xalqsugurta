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
}
