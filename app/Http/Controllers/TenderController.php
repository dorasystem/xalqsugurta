<?php

namespace App\Http\Controllers;

use App\Models\Tender;
use Illuminate\Contracts\View\View;

/** Tenders (/{locale}/tenders): open ones first, then the closed ones */
final class TenderController extends Controller
{
    public function index(string $locale): View
    {
        $tenders = Tender::visible()->orderByDesc('published_at')->orderByDesc('id')->get();

        return view('pages.tenders.index', [
            'open'   => $tenders->filter->isOpen()->values(),
            'closed' => $tenders->reject->isOpen()->values(),
            'locale' => $locale,
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $tender = Tender::visible()->where('slug', $slug)->firstOrFail();

        return view('pages.tenders.show', [
            'tender' => $tender,
            'body'   => $tender->body($locale),
            'locale' => $locale,
        ]);
    }
}
