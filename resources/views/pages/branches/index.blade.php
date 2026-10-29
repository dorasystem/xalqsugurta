@extends('layouts.app')
@section('title', __t('messages.branches.title'))

@push('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Unbounded:wght@600;800&display=swap">
    <link rel="stylesheet" href="{{ assetVersioned('assets/css/inner.css') }}">
@endpush

{{--
    Branches: list + schematic map + details of the chosen branch (design "A", inner.css).
    Pins sit on an Uzbekistan box (lng 55.9–73.2, lat 37.1–45.6). Without JS the list with phones still works.
    $branches = active Branch rows
--}}
@section('content')
@php
    $pin = fn ($b) => $b->lat && $b->lng
        ? 'left: ' . round(($b->lng - 55.9) / 17.3 * 100, 1) . '%; top: ' . round((45.6 - $b->lat) / 8.5 * 100, 1) . '%'
        : null;
@endphp
<div class="in" data-branches>
    <section class="in-wrap in-head">
        <div>
            <nav class="in-crumbs" aria-label="breadcrumb">
                <a href="{{ route('home', ['locale' => $locale]) }}">{{ __t('messages.error_page.home') }}</a>
                <span aria-hidden="true">/</span>
                <span>{{ __t('messages.branches.title') }}</span>
            </nav>
            <h1 class="in-title">{{ __t('messages.branches.title') }}</h1>
            <p class="in-lead">{{ __t('messages.branches.subtitle', ['count' => $branches->count()]) }}</p>
        </div>
        @if ($branches->isNotEmpty())
            <label class="in-search">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
                <input type="search" data-branch-search placeholder="{{ __t('messages.branches.search') }}" aria-label="{{ __t('messages.branches.search') }}" autocomplete="off">
            </label>
        @endif
    </section>

    @if ($branches->isEmpty())
        <section class="in-wrap"><p class="in-empty">{{ __t('messages.branches.none') }}</p></section>
    @else
        <section class="in-wrap in-split">
            <div class="in-list" role="list">
                @foreach ($branches as $branch)
                    <button type="button" role="listitem" @class(['in-row', 'is-active' => $loop->first]) data-branch="{{ $branch->id }}"
                        data-search="{{ mb_strtolower($branch->name('uz') . ' ' . $branch->name('ru') . ' ' . $branch->name('en') . ' ' . $branch->address($locale)) }}"
                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}" aria-controls="branch-{{ $branch->id }}">
                        <span class="in-row__text">
                            <b>{{ $branch->name($locale) }}</b>
                            <span>{{ $branch->address($locale) }}</span>
                        </span>
                        @if ($branch->phones)
                            <span class="in-row__phone">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg>
                                {{ $branch->phones[0] }}
                            </span>
                        @endif
                    </button>
                @endforeach
                <p class="in-empty" data-branch-nothing hidden>{{ __t('messages.branches.nothing') }}</p>
            </div>

            <aside class="in-aside">
                <div class="in-map" aria-hidden="true">
                    <div class="in-map__area">
                        @foreach ($branches as $branch)
                            @if ($style = $pin($branch))
                                <button type="button" tabindex="-1" @class(['in-pin', 'is-active' => $loop->first]) style="{{ $style }}" data-branch="{{ $branch->id }}" title="{{ $branch->name($locale) }}"></button>
                            @endif
                        @endforeach
                    </div>
                    <span class="in-map__hint">{{ __t('messages.branches.hint') }}</span>
                </div>

                @foreach ($branches as $branch)
                    <article class="in-card" id="branch-{{ $branch->id }}" data-branch-card="{{ $branch->id }}" @unless ($loop->first) hidden @endunless>
                        <h2 class="in-card__title">{{ $branch->name($locale) }}</h2>
                        @if ($address = $branch->address($locale))
                            <p class="in-card__row">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6-7-12a7 7 0 0 1 14 0c0 6-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
                                <span>{{ $address }}</span>
                            </p>
                        @endif
                        @if ($branch->manager)
                            <p class="in-card__row">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c1-4 4-6 8-6s7 2 8 6"/></svg>
                                <span>{{ $branch->manager }}</span>
                            </p>
                        @endif
                        @if ($branch->phones)
                            <div class="in-card__phones">
                                @foreach ($branch->phones as $phone)
                                    <a href="tel:{{ \App\Models\Branch::tel($phone) }}">{{ $phone }}</a>
                                @endforeach
                            </div>
                        @endif
                        <div class="in-card__actions">
                            @if ($map = $branch->mapUrl())
                                <a class="in-btn in-btn--brand" href="{{ $map }}" target="_blank" rel="noopener">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2zM9 4v14M15 6v14"/></svg>
                                    {{ __t('messages.branches.map') }}
                                </a>
                            @endif
                            <a class="in-btn in-btn--line" href="{{ route('callback', ['locale' => $locale]) }}">{{ __t('messages.callback.title') }}</a>
                        </div>
                    </article>
                @endforeach
            </aside>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Branches: a list row or a map pin selects the branch; the search box filters the list
    (function () {
        var root = document.querySelector('[data-branches]');
        if (!root) return;
        var rows = root.querySelectorAll('.in-row');
        var pins = root.querySelectorAll('.in-pin');
        var cards = root.querySelectorAll('[data-branch-card]');
        var aside = root.querySelector('.in-aside');

        function select(id, scroll) {
            rows.forEach(function (r) { var on = r.dataset.branch === id; r.classList.toggle('is-active', on); r.setAttribute('aria-pressed', on ? 'true' : 'false'); });
            pins.forEach(function (p) { p.classList.toggle('is-active', p.dataset.branch === id); });
            cards.forEach(function (c) { c.hidden = c.dataset.branchCard !== id; });
            // Phones show the card under the list: bring it into view
            if (scroll && window.matchMedia('(max-width: 900px)').matches) aside.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        rows.forEach(function (r) { r.addEventListener('click', function () { select(r.dataset.branch, true); }); });
        pins.forEach(function (p) { p.addEventListener('click', function () { select(p.dataset.branch, false); }); });

        var search = root.querySelector('[data-branch-search]');
        var nothing = root.querySelector('[data-branch-nothing]');
        if (search) search.addEventListener('input', function () {
            var q = search.value.trim().toLowerCase().replace(/[‘’ʻʼ`]/g, "'");
            var shown = 0;
            rows.forEach(function (r) {
                var hit = !q || r.dataset.search.replace(/[‘’ʻʼ`]/g, "'").indexOf(q) !== -1;
                r.hidden = !hit;
                if (hit) shown++;
            });
            nothing.hidden = shown > 0;
        });
    })();
</script>
@endpush
