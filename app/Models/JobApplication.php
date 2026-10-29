<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** CV sent from the career page; the file is on the private "local" disk under resumes/{id} */
class JobApplication extends Model
{
    public const STATUS_NEW      = 'new';
    public const STATUS_REVIEWED = 'reviewed';

    protected $fillable = [
        'vacancy_id', 'name', 'phone', 'email', 'message', 'file_path', 'file_name', 'file_size',
        'status', 'handled_by', 'handled_at', 'locale',
    ];

    protected $casts = ['handled_at' => 'datetime'];

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
