<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One request to the insurer's API, written by App\Services\ApiLogger */
class ApiLog extends Model
{
    use Prunable;

    /** Days a log row is kept (it holds passport data) */
    public const KEEP_DAYS = 30;

    protected $fillable = [
        'order_id', 'product', 'method', 'endpoint', 'url',
        'status', 'result', 'success', 'duration_ms',
        'request', 'response', 'error',
    ];

    protected $casts = [
        'request'     => 'array',
        'success'     => 'boolean',
        'status'      => 'integer',
        'duration_ms' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('success', false);
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(self::KEEP_DAYS));
    }

    /** "HTTP 404 · HTML", "result 0", "Ulanib bo'lmadi" */
    public function outcome(): string
    {
        if ($this->status === null) {
            return 'Ulanib bo\'lmadi';
        }

        $html = $this->response !== null && str_starts_with(ltrim($this->response), '<');

        return match (true) {
            $this->status < 200 || $this->status >= 300 => 'HTTP ' . $this->status . ($html ? ' · HTML' : ''),
            $this->result !== null                      => 'result ' . $this->result,
            default                                     => 'HTTP ' . $this->status,
        };
    }

    /** Response body pretty-printed when it is JSON */
    public function prettyRequest(): ?string
    {
        return $this->request === null || $this->request === []
            ? null
            : json_encode($this->request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function prettyResponse(): ?string
    {
        $json = json_decode((string) $this->response, true);

        return is_array($json)
            ? json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : $this->response;
    }
}
