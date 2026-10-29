<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One filled questionnaire (the old site's "Anketa"). Keys of the options are stored; their texts
 * are messages.survey.{reasons|products|criteria}.*. `ranking` lists criteria keys, most important first.
 */
class SurveyResponse extends Model
{
    public const REASONS = ['trust', 'service', 'pays', 'never', 'other'];

    public const PRODUCTS = ['accident', 'travel', 'property', 'health', 'kasko', 'osago', 'financial', 'osgor', 'osgop', 'opo', 'smr', 'other'];

    public const CRITERIA = ['business_trust', 'rating', 'capital', 'foreign', 'age', 'tariffs', 'range', 'recommendations', 'service'];

    protected $fillable = ['reason', 'reason_other', 'products', 'product_other', 'ranking', 'locale'];

    protected $casts = [
        'products' => 'array',
        'ranking'  => 'array',
    ];
}
