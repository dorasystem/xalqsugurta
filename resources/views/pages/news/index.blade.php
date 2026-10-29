@extends('layouts.app')
@section('title', __t('messages.news.title'))

{{--
    Press centre, design "A": the newest story large with the next three beside it, then the rest as cards.
    $featured / $latest / $rest come from NewsController::index(); $years = years that have news
--}}
@section('content')
<div class="in">
    <x-inner.head :title="__t('messages.news.title')" :lead="__t('messages.news.subtitle')">
        @if ($years->count() > 1)
            <nav class="in-chips" aria-label="{{ __t('messages.news.years') }}">
                <a href="{{ route('news.index', ['locale' => $locale]) }}" @class(['in-chip', 'is-active' => !$year])>{{ __t('messages.news.all_years') }}</a>
                @foreach ($years as $y)
                    <a href="{{ route('news.index', ['locale' => $locale, 'year' => $y]) }}" @class(['in-chip', 'is-active' => $year === $y])>{{ $y }}</a>
                @endforeach
            </nav>
        @endif
    </x-inner.head>

    <div class="in-wrap">
        @if ($news->isEmpty())
            <p class="in-empty">{{ __t('messages.news.none') }}</p>
        @else
            @if ($featured)
                <section class="in-news-top">
                    <a class="in-feature" href="{{ route('news.show', ['locale' => $locale, 'slug' => $featured->slug]) }}">
                        @if ($featured->imageUrl())
                            <img src="{{ $featured->imageUrl() }}" alt="">
                        @endif
                        <span class="in-feature__body">
                            <span class="in-feature__tag">{{ __t('messages.news.main') }} · {{ $featured->published_at->format('d.m.Y') }}</span>
                            <span class="in-feature__title">{{ $featured->title($locale) }}</span>
                            @if ($excerpt = $featured->excerpt($locale))
                                <span class="in-feature__text">{{ $excerpt }}</span>
                            @endif
                        </span>
                    </a>

                    @if ($latest->isNotEmpty())
                        <div class="in-latest">
                            <span class="in-latest__label">{{ __t('messages.news.latest') }}</span>
                            @foreach ($latest as $item)
                                <a class="in-latest__item" href="{{ route('news.show', ['locale' => $locale, 'slug' => $item->slug]) }}">
                                    @include('pages.news._thumb', ['item' => $item])
                                    <span>
                                        <span class="in-meta">{{ $item->published_at->format('d.m.Y') }}</span><br>
                                        <b>{{ \Illuminate\Support\Str::limit($item->title($locale), 120) }}</b>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            @if ($rest->isNotEmpty())
                @if ($featured)
                    <h2 class="in-section-title">{{ __t('messages.news.all') }}</h2>
                @endif
                <div class="in-cards">
                    @foreach ($rest as $item)
                        @include('pages.news._card', ['item' => $item])
                    @endforeach
                </div>
            @endif

            @if ($news->hasPages())
                <nav class="in-pager" aria-label="{{ __t('messages.news.pages') }}">
                    <a @class(['in-btn', 'in-btn--line', 'is-disabled' => $news->onFirstPage()]) href="{{ $news->previousPageUrl() ?? '#' }}" rel="prev">← {{ __t('messages.news.newer') }}</a>
                    <span class="in-meta">{{ $news->currentPage() }} / {{ $news->lastPage() }}</span>
                    <a @class(['in-btn', 'in-btn--line', 'is-disabled' => !$news->hasMorePages()]) href="{{ $news->nextPageUrl() ?? '#' }}" rel="next">{{ __t('messages.news.older') }} →</a>
                </nav>
            @endif
        @endif
    </div>
</div>
@endsection
