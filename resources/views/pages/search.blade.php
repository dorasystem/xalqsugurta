@extends('layouts.app')
@section('title', __t('messages.site_search.title'))

{{-- Site search. $results = [title, text, url, icon] from SearchController --}}
@section('content')
<x-insurence.flow icon="bi-search" :title="__t('messages.site_search.title')" :subtitle="__t('messages.site_search.subtitle')">

    <form class="xf-panel" action="{{ route('search', ['locale' => getCurrentLocale()]) }}" method="GET" role="search">
        <div class="xf-panel__body">
            <x-insurence.field name="q" type="search" :label="__t('messages.site_search.title')" :value="$query"
                :help="$tooShort ? __t('messages.site_search.too_short') : __t('messages.site_search.hint')"
                :placeholder="__t('messages.site_search.placeholder')" maxlength="100" autofocus autocomplete="off">
                <x-slot:append>
                    <button class="xf-btn xf-btn--primary" type="submit">{{ __t('messages.site_search.button') }}</button>
                </x-slot:append>
            </x-insurence.field>
        </div>
    </form>

    @if ($query !== '' && !$tooShort)
        <section class="xf-panel" aria-live="polite">
            <div class="xf-panel__head">
                <h2 class="xf-panel__title">{{ __t('messages.site_search.found', ['count' => count($results)]) }}</h2>
            </div>
            <div class="xf-panel__body">
                @forelse ($results as $item)
                    <a class="xf-search-hit" href="{{ $item['url'] }}">
                        <span class="xf-search-hit__icon"><i class="bi {{ $item['icon'] }}"></i></span>
                        <span>
                            <strong>{{ $item['title'] }}</strong>
                            @if ($item['text'])
                                <small>{{ $item['text'] }}</small>
                            @endif
                        </span>
                        <i class="bi bi-arrow-right xf-search-hit__go" aria-hidden="true"></i>
                    </a>
                @empty
                    <p class="xf-note">
                        <i class="bi bi-info-circle"></i>
                        <span>{{ __t('messages.site_search.empty', ['query' => $query]) }}</span>
                    </p>
                @endforelse
            </div>
        </section>
    @endif

</x-insurence.flow>
@endsection
