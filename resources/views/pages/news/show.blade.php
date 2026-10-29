@extends('layouts.app')
@section('title', $item->title($locale))

{{-- One news item, design "A": the article with its picture, the latest news beside it. $body = clean HTML --}}
@section('content')
<div class="in">
    <x-inner.head :title="$item->title($locale)" :lead="$item->published_at->format('d.m.Y')"
        :crumb="[__t('messages.news.title'), route('news.index', ['locale' => $locale])]" />

    <div class="in-wrap in-cols">
        <article class="in-main in-panel">
            @if ($item->imageUrl())
                <img class="in-cover" src="{{ $item->imageUrl() }}" alt="">
            @endif
            @if ($body)
                <div class="in-prose">{!! $body !!}</div>
            @endif
        </article>

        @if ($more->isNotEmpty())
            <aside class="in-side">
                <div class="in-latest">
                    <span class="in-latest__label">{{ __t('messages.news.more') }}</span>
                    @foreach ($more as $other)
                        <a class="in-latest__item" href="{{ route('news.show', ['locale' => $locale, 'slug' => $other->slug]) }}">
                            @include('pages.news._thumb', ['item' => $other])
                            <span>
                                <span class="in-meta">{{ $other->published_at->format('d.m.Y') }}</span><br>
                                <b>{{ \Illuminate\Support\Str::limit($other->title($locale), 110) }}</b>
                            </span>
                        </a>
                    @endforeach
                </div>
                <a class="in-btn in-btn--line" href="{{ route('news.index', ['locale' => $locale]) }}">← {{ __t('messages.news.all') }}</a>
            </aside>
        @endif
    </div>
</div>
@endsection
