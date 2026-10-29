@extends('layouts.app')
@section('title', $item->title($locale))

{{-- One news item. $body = News::body() (clean HTML), $more = other latest news --}}
@section('content')
<x-insurence.flow icon="bi-newspaper" :title="$item->title($locale)" :subtitle="$item->published_at->format('d.m.Y')">

    <article class="xf-panel">
        @if ($item->imageUrl())
            <img class="xf-news__cover" src="{{ $item->imageUrl() }}" alt="">
        @endif
        @if ($body)
            <div class="xf-panel__body xf-prose">{!! $body !!}</div>
        @endif
    </article>

    @if ($more->isNotEmpty())
        <h2 class="xf-news__more">{{ __t('messages.news.more') }}</h2>
        <div class="xf-news">
            @foreach ($more as $other)
                @include('pages.news._card', ['item' => $other])
            @endforeach
        </div>
    @endif

    <p><a href="{{ route('news.index', ['locale' => $locale]) }}" class="xf-btn xf-btn--ghost"><i class="bi bi-arrow-left"></i> {{ __t('messages.news.all') }}</a></p>

</x-insurence.flow>
@endsection
