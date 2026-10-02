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
        'request', 'request_raw', 'response', 'error',
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

    /** The body for reading: from the raw text when there is one, so the key order is the one sent */
    public function prettyRequest(): ?string
    {
        $data = is_string($this->request_raw) ? json_decode($this->request_raw, true) : null;
        $data = is_array($data) ? $data : $this->request;

        return $data === null || $data === []
            ? $this->request_raw
            : json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** What the insurer received, byte for byte (older rows only have the decoded JSON) */
    public function exactRequest(): ?string
    {
        return $this->request_raw ?? ($this->request ? json_encode($this->request, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null);
    }

    /** The body was sent with \uXXXX escapes (non-ASCII text) — some insurer endpoints cannot parse them */
    public function requestHasUnicodeEscapes(): bool
    {
        return is_string($this->request_raw) && preg_match('/\\\\u[0-9a-fA-F]{4}/', $this->request_raw) === 1;
    }

    /** Response body pretty-printed when it is JSON */
    public function prettyResponse(): ?string
    {
        $json = json_decode((string) $this->response, true);

        return is_array($json)
            ? json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : $this->response;
    }
}
