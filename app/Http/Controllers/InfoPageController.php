<?php

namespace App\Http\Controllers;

use App\Models\InfoPage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

/**
 * Company / disclosure pages, design "C" (information centre): section tabs, the section's pages as tiles,
 * the chosen page below. /{locale}/info lists every section; /{locale}/info/{key} opens one page.
 */
final class InfoPageController extends Controller
{
    public function index(string $locale): View
    {
        return view('pages.info.index', ['sections' => $this->sections(), 'locale' => $locale]);
    }

    public function show(string $locale, string $key): View
    {
        abort_unless(in_array($key, InfoPage::publishedKeys(), true), 404);

        $page = InfoPage::where('key', $key)->where('is_published', true)->firstOrFail();
        $sections = $this->sections();

        return view('pages.info.show', [
            'page'     => $page,
            'body'     => $page->body($locale),
            'sections' => $sections,
            'siblings' => $sections->get($page->section, collect()),
            'locale'   => $locale,
        ]);
    }

    /** section => published pages, in InfoPage::SECTIONS order; empty sections left out */
    private function sections(): Collection
    {
        $pages = InfoPage::where('is_published', true)->orderBy('sort_order')->orderBy('id')->get()->groupBy('section');

        return collect(InfoPage::SECTIONS)->keys()
            ->mapWithKeys(fn (string $section) => [$section => $pages->get($section, collect())])
            ->filter(fn (Collection $list) => $list->isNotEmpty());
    }
}
