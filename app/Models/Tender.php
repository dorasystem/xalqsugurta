<?php

namespace App\Models;

use App\Support\SafeHtml;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Tender the company announces (/{locale}/tenders); proposals are sent by e-mail */
class Tender extends Model
{
    protected $fillable = [
        'slug', 'email', 'published_at', 'deadline_at', 'is_published',
        'title_uz', 'title_ru', 'title_en', 'body_uz', 'body_ru', 'body_en',
    ];

    protected $casts = [
        'published_at' => 'date',
        'deadline_at'  => 'date',
        'is_published' => 'boolean',
    ];

    /** Published and already announced (published_at may be set ahead) */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('published_at', '<=', today());
    }

    /** Proposals are accepted through the whole deadline day */
    public function isOpen(): bool
    {
        return $this->deadline_at === null || !$this->deadline_at->isBefore(today());
    }

    /** Title in the locale, falling back to Uzbek then Russian */
    public function title(string $locale): string
    {
        return (string) ($this->{'title_' . $locale} ?: ($this->title_uz ?: $this->title_ru));
    }

    public function body(string $locale): ?string
    {
        return SafeHtml::clean($this->{'body_' . $locale}) ?? SafeHtml::clean($this->body_uz) ?? SafeHtml::clean($this->body_ru);
    }
}
