<?php

namespace App\Models;

use App\Support\SafeHtml;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Vacancy on the career page (/{locale}/career), edited in the admin panel */
class Vacancy extends Model
{
    /** Employment types offered in the admin form (messages.career.employment_*) */
    public const EMPLOYMENT = ['full', 'part', 'intern'];

    protected $fillable = [
        'slug', 'location', 'employment', 'published_at', 'deadline_at', 'is_published', 'sort_order',
        'title_uz', 'title_ru', 'title_en', 'body_uz', 'body_ru', 'body_en',
    ];

    protected $casts = [
        'published_at' => 'date',
        'deadline_at'  => 'date',
        'is_published' => 'boolean',
    ];

    /** Published, announced and not past the deadline (the deadline day itself counts) */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', today())
            ->where(fn (Builder $q) => $q->whereNull('deadline_at')->orWhere('deadline_at', '>=', today()));
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
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
