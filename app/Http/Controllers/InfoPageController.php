<?php

namespace App\Http\Controllers;

use App\Models\InfoPage;
use Illuminate\Contracts\View\View;

/** Company / disclosure page (/{locale}/info/{key}), published from the admin panel */
final class InfoPageController extends Controller
{
    public function show(string $locale, string $key): View
    {
        $page = InfoPage::where('key', $key)->where('is_published', true)->firstOrFail();

        $siblings = InfoPage::where('section', $page->section)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('pages.info.show', [
            'page'     => $page,
            'body'     => $page->body($locale),
            'siblings' => $siblings,
            'locale'   => $locale,
        ]);
    }
}
