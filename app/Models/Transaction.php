<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
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

    /**
     * Get transactions within a time range (Paycom GetStatement).
     * from/to are Unix timestamps in milliseconds.
     *
     * @return Collection<int, Transaction>
     */
    public static function getTransactionsByTimeRange(int $from, int $to): Collection
    {
        return self::query()
            ->whereNotNull('paycom_time')
            ->whereRaw('CAST(paycom_time AS UNSIGNED) >= ?', [$from])
            ->whereRaw('CAST(paycom_time AS UNSIGNED) <= ?', [$to])
            ->orderBy('paycom_time')
            ->get();
    }
}
