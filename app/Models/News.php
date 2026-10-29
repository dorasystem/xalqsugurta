<?php

namespace App\Models;

use App\Support\SafeHtml;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** News item of the press centre (/{locale}/news); the image is on the public disk */
class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'slug', 'image', 'published_at', 'is_published', 'source_url',
        'title_uz', 'title_ru', 'title_en', 'body_uz', 'body_ru', 'body_en',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    /** Published and already due (published_at may be set ahead) */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('published_at', '<=', now());
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

    /** About 160 characters of plain text for the list */
    public function excerpt(string $locale): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $this->body($locale)), ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        return $text === '' ? null : Str::limit($text, 160);
    }

    /** Relative, like the other public-disk links, so it works behind the host proxy */
    public function imageUrl(): ?string
    {
        return $this->image ? '/storage/' . ltrim($this->image, '/') : null;
    }
}
