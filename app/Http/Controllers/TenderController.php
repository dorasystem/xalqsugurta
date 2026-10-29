<?php

namespace App\Http\Controllers;

use App\Models\Tender;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** Tenders (/{locale}/tenders): open ones first, then the closed ones; ?state=open|closed filters */
final class TenderController extends Controller
{
    private const STATES = ['open', 'closed'];

    private const DEFAULT_EMAIL = 'info@xalqsugurta.uz';

    public function index(Request $request, string $locale): View
    {
        $tenders = Tender::visible()->orderByDesc('published_at')->orderByDesc('id')->get();
        $open    = $tenders->filter->isOpen()->values();
        $closed  = $tenders->reject->isOpen()->values();
        $state   = in_array($request->query('state'), self::STATES, true) ? $request->query('state') : null;

        return view('pages.tenders.index', [
            'open'   => $state === 'closed' ? collect() : $open,
            'closed' => $state === 'open' ? collect() : $closed,
            'counts' => ['open' => $open->count(), 'closed' => $closed->count()],
            'state'  => $state,
            'email'  => $open->first()?->email ?: self::DEFAULT_EMAIL,
            'locale' => $locale,
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $tender = Tender::visible()->where('slug', $slug)->firstOrFail();

        return view('pages.tenders.show', [
            'tender' => $tender,
            'body'   => $tender->body($locale),
            'others' => Tender::visible()->whereKeyNot($tender->id)->orderByDesc('published_at')->limit(3)->get(),
            'locale' => $locale,
        ]);
    }
}
