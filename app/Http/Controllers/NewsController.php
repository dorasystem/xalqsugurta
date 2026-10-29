<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Contracts\View\View;

/** Press centre (/{locale}/news): newest first, 12 per page */
final class NewsController extends Controller
{
    public function index(string $locale): View
    {
        return view('pages.news.index', [
            'news'   => News::visible()->orderByDesc('published_at')->orderByDesc('id')->paginate(12),
            'locale' => $locale,
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $item = News::visible()->where('slug', $slug)->firstOrFail();

        return view('pages.news.show', [
            'item'   => $item,
            'body'   => $item->body($locale),
            'more'   => News::visible()->whereKeyNot($item->id)->orderByDesc('published_at')->limit(3)->get(),
            'locale' => $locale,
        ]);
    }
}
