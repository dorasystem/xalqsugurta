<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** "Call me back" request from the site, handled in the admin panel */
class CallbackRequest extends Model
{
    public const STATUS_NEW  = 'new';
    public const STATUS_DONE = 'done';

    /** Topics offered on the form (messages.callback.topic_*) */
    public const TOPICS = ['buy', 'claim', 'policy', 'other'];

    protected $fillable = ['name', 'phone', 'topic', 'message', 'status', 'handled_by', 'handled_at', 'locale'];

    protected $casts = ['handled_at' => 'datetime'];

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
