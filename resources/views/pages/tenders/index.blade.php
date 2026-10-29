@extends('layouts.app')
@section('title', __t('messages.tenders.title'))

{{-- Tenders, design "A": rows with status and deadline, "how to send" panel beside. $open / $closed = Tender collections --}}
@section('content')
@php
    $chips = [null => __t('messages.tenders.filter_all'), 'open' => __t('messages.tenders.status_open'), 'closed' => __t('messages.tenders.status_closed')];
    $excerpt = fn ($t) => \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $t->body($locale)), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), 230);
@endphp
<div class="in">
    <x-inner.head :title="__t('messages.tenders.title')" :lead="__t('messages.tenders.subtitle')">
        <nav class="in-chips" aria-label="{{ __t('messages.tenders.status') }}">
            @foreach ($chips as $key => $label)
                <a href="{{ route('tenders.index', array_filter(['locale' => $locale, 'state' => $key])) }}" @class(['in-chip', 'is-active' => $state === ($key ?: null)])>
                    {{ $label }}@if ($key) · {{ $counts[$key] }}@endif
                </a>
            @endforeach
        </nav>
    </x-inner.head>

    <div class="in-wrap in-cols">
        <div class="in-main">
            @if ($open->isEmpty() && $closed->isEmpty())
                <p class="in-empty">{{ __t('messages.tenders.none') }}</p>
            @endif

            @foreach (['open' => $open, 'closed' => $closed] as $key => $list)
                @foreach ($list as $tender)
                    <a @class(['in-trow', 'is-open' => $key === 'open']) href="{{ route('tenders.show', ['locale' => $locale, 'slug' => $tender->slug]) }}">
                        <span class="in-trow__top">
                            <span class="in-status in-status--{{ $key }}">{{ __t('messages.tenders.status_' . $key) }}</span>
                            <span class="in-meta">{{ __t('messages.tenders.published', ['date' => $tender->published_at->format('d.m.Y')]) }}</span>
                        </span>
                        <b class="in-trow__title">{{ $tender->title($locale) }}</b>
                        <span class="in-trow__text">{{ $excerpt($tender) }}</span>
                        @if ($tender->deadline_at)
                            <span class="in-trow__deadline"><i class="bi bi-clock" aria-hidden="true"></i> {{ __t('messages.tenders.deadline', ['date' => $tender->deadline_at->format('d.m.Y')]) }}</span>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </div>

        <aside class="in-side">
            @include('pages.tenders._how', ['email' => $email])
        </aside>
    </div>
</div>
@endsection
