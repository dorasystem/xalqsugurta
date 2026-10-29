@extends('layouts.app')
@section('title', $tender->title($locale))

{{-- One tender, design "A": its text, the deadline card and the "how to send" panel. $body = clean HTML --}}
@section('content')
@php $state = $tender->isOpen() ? 'open' : 'closed'; @endphp
<div class="in">
    <x-inner.head :title="$tender->title($locale)" :crumb="[__t('messages.tenders.title'), route('tenders.index', ['locale' => $locale])]" />

    <div class="in-wrap in-cols">
        <article class="in-main in-panel">
            <span class="in-status in-status--{{ $state }}">{{ __t('messages.tenders.status_' . $state) }}</span>
            <div class="in-prose">{!! $body !!}</div>
        </article>

        <aside class="in-side">
            <div class="in-panel in-form">
                @if ($tender->deadline_at)
                    <span class="in-meta">{{ __t('messages.tenders.deadline_label') }}</span>
                    <b class="in-deadline">{{ __t('messages.tenders.deadline', ['date' => $tender->deadline_at->format('d.m.Y')]) }}</b>
                @endif
                <span class="in-meta">{{ __t('messages.tenders.published', ['date' => $tender->published_at->format('d.m.Y')]) }}</span>
            </div>
            @if ($state === 'open')
                @include('pages.tenders._how', ['email' => $tender->email ?: 'info@xalqsugurta.uz'])
            @endif
            @if ($others->isNotEmpty())
                <div class="in-latest">
                    <span class="in-latest__label">{{ __t('messages.tenders.others') }}</span>
                    @foreach ($others as $other)
                        <a class="in-latest__item" href="{{ route('tenders.show', ['locale' => $locale, 'slug' => $other->slug]) }}">
                            <span>
                                <span class="in-status in-status--{{ $other->isOpen() ? 'open' : 'closed' }}">{{ __t('messages.tenders.status_' . ($other->isOpen() ? 'open' : 'closed')) }}</span><br>
                                <b>{{ \Illuminate\Support\Str::limit($other->title($locale), 110) }}</b>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
            <a class="in-btn in-btn--line" href="{{ route('tenders.index', ['locale' => $locale]) }}">← {{ __t('messages.tenders.all') }}</a>
        </aside>
    </div>
</div>
@endsection
