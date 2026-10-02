<?php

namespace App\Http\Controllers;

use App\Models\InfoPage;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Site search (/{locale}/search?q=): products on sale, published company pages and the service pages */
final class SearchController extends Controller
{
    private const MIN_LENGTH = 2;

    private const LOCALES = ['uz', 'ru', 'en'];

    public function index(Request $request, string $locale): View
    {
        $query   = trim(mb_substr((string) $request->query('q', ''), 0, 100));
        $results = mb_strlen($query) >= self::MIN_LENGTH ? $this->search($query, $locale) : [];

        return view('pages.search', [
            'query'   => $query,
            'results' => $results,
            'tooShort' => $query !== '' && mb_strlen($query) < self::MIN_LENGTH,
        ]);
    }

    /** @return list<array{title: string, text: ?string, url: string, icon: string}> */
    private function search(string $query, string $locale): array
    {
        $needle  = self::normalize($query);
        $matches = fn (?string ...$texts): bool => collect($texts)->contains(fn (?string $t): bool => $t !== null && str_contains(self::normalize($t), $needle));
        $results = [];

        foreach (Product::where('is_active', true)->orderBy('sort_order')->get() as $product) {
            // A product is found by its name in any language ("каско" on the Uzbek page too)
            $names = array_map(fn (string $l) => $product->{'name_' . $l}, self::LOCALES);
            if ($matches($product->{'desc_' . $locale}, ...$names)) {
                $results[] = [
                    'title' => $product->{'name_' . $locale} ?: $product->name_uz,
                    'text'  => $product->{'desc_' . $locale},
                    'url'   => $product->cardUrl(),
                    'icon'  => preg_replace('/^bi\s+/', '', (string) $product->icon) ?: 'bi-shield-check',
                ];
            }
        }

        foreach ($this->servicePages($locale) as $page) {
            if ($matches($page['title'], $page['text'])) {
                $results[] = $page;
            }
        }

        // publishedKeys() is cached and empty until info_pages exists, so search works before that migration
        $infoKeys = InfoPage::publishedKeys();
        $infoPages = $infoKeys === [] ? [] : InfoPage::whereIn('key', $infoKeys)->orderBy('section')->orderBy('sort_order')->get();

        foreach ($infoPages as $page) {
            $body = $page->body($locale);
            $text = $body !== null ? trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8'))) : null;

            if ($matches($page->title($locale), $text)) {
                $results[] = [
                    'title' => (string) $page->title($locale),
                    'text'  => $text !== null ? self::snippet($text, $needle) : null,
                    'url'   => route('info.show', ['locale' => $locale, 'key' => $page->key]),
                    'icon'  => 'bi-building',
                ];
            }
        }

        return $results;
    }

    private function servicePages(string $locale): array
    {
        return [
            ['title' => __t('messages.my_policies.title'), 'text' => __t('messages.my_policies.subtitle'), 'url' => route('my-policies', ['locale' => $locale]), 'icon' => 'bi-person-vcard'],
            ['title' => __t('messages.claims.title'), 'text' => __t('messages.claims.subtitle'), 'url' => route('claims.create', ['locale' => $locale]), 'icon' => 'bi-life-preserver'],
            ['title' => __t('messages.claims.check_status'), 'text' => null, 'url' => route('claims.status', ['locale' => $locale]), 'icon' => 'bi-search'],
            ['title' => __t('messages.callback.title'), 'text' => __t('messages.callback.subtitle'), 'url' => route('callback', ['locale' => $locale]), 'icon' => 'bi-telephone'],
        ];
    }

    /** About 160 characters of the text around the first match */
    private static function snippet(string $text, string $needle): string
    {
        $at = mb_strpos(self::normalize($text), $needle);
        if ($at === false || $at < 60) {
            return Str::limit($text, 160);
        }

        return '…' . Str::limit(mb_substr($text, $at - 50), 150);
    }

    /** Lower case, one kind of apostrophe (o‘, o', oʻ, o` are the same letter) */
    private static function normalize(string $text): string
    {
        return mb_strtolower(str_replace(['‘', '’', 'ʻ', 'ʼ', '`', '´'], "'", $text));
    }
}
