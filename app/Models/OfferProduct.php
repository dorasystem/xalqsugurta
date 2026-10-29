<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A product sold on application (the old site's catalogue items we don't sell online): the page
 * /{locale}/insurance/{slug} shows its texts and a request form; requests become CallbackRequests.
 * Text fields hold one item per line; `terms` lines are "badge | text".
 */
class OfferProduct extends Model
{
    /** Form fields per product (OfferController); unknown slugs get the private set */
    public const FORMS = [
        'family'     => ['name', 'phone', 'members'],
        'all_risks'  => ['company', 'name', 'phone'],
        'collective' => ['company', 'phone', 'employees'],
    ];

    public const AUDIENCES = ['private', 'business'];

    protected $fillable = [
        'slug', 'audience', 'sort_order', 'is_active',
        'title_uz', 'title_ru', 'title_en', 'kicker_uz', 'kicker_ru', 'kicker_en', 'lead_uz', 'lead_ru', 'lead_en',
        'highlights_uz', 'highlights_ru', 'highlights_en', 'covers_uz', 'covers_ru', 'covers_en',
        'terms_uz', 'terms_ru', 'terms_en', 'note_uz', 'note_ru', 'note_en',
    ];

    protected $casts = ['is_active' => 'boolean'];

    /** A text field in the locale, falling back to Uzbek then Russian */
    public function text(string $field, string $locale): ?string
    {
        return $this->{$field . '_' . $locale} ?: ($this->{$field . '_uz'} ?: $this->{$field . '_ru'});
    }

    /** @return list<string> non-empty lines of a text field */
    public function lines(string $field, string $locale): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->text($field, $locale)))));
    }

    /** @return list<array{0: ?string, 1: string}> terms as [badge, text] */
    public function terms(string $locale): array
    {
        return array_map(function (string $line): array {
            [$badge, $text] = str_contains($line, '|') ? array_map('trim', explode('|', $line, 2)) : [null, $line];

            return [$badge ?: null, $text];
        }, $this->lines('terms', $locale));
    }

    /** @return list<string> */
    public function formFields(): array
    {
        return self::FORMS[$this->slug] ?? self::FORMS['family'];
    }
}
