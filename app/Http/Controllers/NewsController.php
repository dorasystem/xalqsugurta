<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** Press centre (/{locale}/news): page 1 opens with the newest item and the three after it; ?year= filters */
final class NewsController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request, string $locale): View
    {
        $years = News::visible()->orderByDesc('published_at')->pluck('published_at')
            ->map(fn ($date) => (int) $date->format('Y'))->unique()->values();
        $year = in_array((int) $request->query('year'), $years->all(), true) ? (int) $request->query('year') : null;

        $query = News::visible()->orderByDesc('published_at')->orderByDesc('id');
        if ($year) {
            $query->where('published_at', '>=', $year . '-01-01')->where('published_at', '<', ($year + 1) . '-01-01');
        }
        $news = $query->paginate(self::PER_PAGE)->withQueryString();

        // Page 1: the newest is the big story, the next three are listed beside it
        $items = collect($news->items());
        $first = $news->onFirstPage() ? $items->shift() : null;
        $latest = $news->onFirstPage() ? $items->splice(0, 3) : collect();

        return view('pages.news.index', [
            'news'     => $news,
            'featured' => $first,
            'latest'   => $latest,
            'rest'     => $items->values(),
            'years'    => $years,
            'year'     => $year,
            'locale'   => $locale,
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $item = News::visible()->where('slug', $slug)->firstOrFail();

        return view('pages.news.show', [
            'item'   => $item,
            'body'   => $item->body($locale),
            'more'   => News::visible()->whereKeyNot($item->id)->orderByDesc('published_at')->limit(4)->get(),
            'locale' => $locale,
        ]);
    }
}
