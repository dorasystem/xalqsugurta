<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClickUz extends Model
{
    use HasFactory;
    protected $fillable = ['click_trans_id', 'merchant_trans_id', 'amount', 'sign_time', 'situation', 'status'];
}
