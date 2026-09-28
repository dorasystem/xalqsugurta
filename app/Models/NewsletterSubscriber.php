<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Email left in the footer's "subscribe" form; listed and exported in the admin panel */
class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'locale'];
}
