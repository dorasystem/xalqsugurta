<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction',
        'code',
        'state',
        'order_id',
        'amount',
        'reason',
        'payme_time',
        'cancel_time',
        'create_time',
        'perform_time',
        'paycom_transaction_id',
        'paycom_time',
        'paycom_time_datetime',
        'perform_time_unix',
    ];
}
