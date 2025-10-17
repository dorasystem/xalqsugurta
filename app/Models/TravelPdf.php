<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TravelPdf extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'pdf',
        'qr_code1',
        'qr_code2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
