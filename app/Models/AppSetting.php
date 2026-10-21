<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One admin-panel setting (App\Services\PaymentSettings); secret values are encrypted */
class AppSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];
}
