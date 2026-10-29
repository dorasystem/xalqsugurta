@extends('layouts.app')
@section('title', __t('messages.news.title'))

{{-- Press centre. $news = paginated News --}}
@section('content')
<x-insurence.flow icon="bi-newspaper" :title="__t('messages.news.title')" :subtitle="__t('messages.news.subtitle')">

    @if ($news->isEmpty())
        <section class="xf-panel">
            <div class="xf-panel__body">
                <p class="xf-note">
                    <i class="bi bi-info-circle"></i>
                    <span>{{ __t('messages.news.none') }}</span>
                </p>
            </div>
        </section>
    @else
        <div class="xf-news">
            @foreach ($news as $item)
                @include('pages.news._card', ['item' => $item])
            @endforeach
        </div>

        @if ($news->hasPages())
            <nav class="xf-pager" aria-label="{{ __t('messages.news.pages') }}">
                @if ($news->onFirstPage())
                    <span class="xf-btn xf-btn--ghost is-disabled"><i class="bi bi-arrow-left"></i> {{ __t('messages.news.newer') }}</span>
                @else
                    <a class="xf-btn xf-btn--ghost" href="{{ $news->previousPageUrl() }}" rel="prev"><i class="bi bi-arrow-left"></i> {{ __t('messages.news.newer') }}</a>
                @endif
                <span class="xf-pager__info">{{ $news->currentPage() }} / {{ $news->lastPage() }}</span>
                @if ($news->hasMorePages())
                    <a class="xf-btn xf-btn--ghost" href="{{ $news->nextPageUrl() }}" rel="next">{{ __t('messages.news.older') }} <i class="bi bi-arrow-right"></i></a>
                @else
                    <span class="xf-btn xf-btn--ghost is-disabled">{{ __t('messages.news.older') }} <i class="bi bi-arrow-right"></i></span>
                @endif
            </nav>
        @endif
    @endif

</x-insurence.flow>
@endsection
