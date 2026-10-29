@extends('layouts.app')
@section('title', $page->title($locale))

{{--
    Company / disclosure page, design "C": section tabs, the section's pages as tiles, the page itself below.
    $body = InfoPage::body() (clean HTML), $sections = section => published pages, $siblings = this section's pages
--}}
@section('content')
<div class="in">
    <x-inner.head :title="__t('messages.info_page.hub_title')" :lead="__t('messages.info_page.hub_lead')"
>
        @if ($sections->count() > 1)
            <nav class="in-chips" aria-label="{{ __t('messages.info_page.hub_title') }}">
                @foreach ($sections as $section => $pages)
                    <a href="{{ route('info.show', ['locale' => $locale, 'key' => $pages->first()->key]) }}" @class(['in-chip', 'is-active' => $section === $page->section])>
                        {{ __t('messages.info_page.sections.' . $section) }}
                    </a>
                @endforeach
            </nav>
        @endif
    </x-inner.head>

    <div class="in-wrap">
        @if ($siblings->count() > 1)
            @include('pages.info._tiles', ['pages' => $siblings, 'active' => $page])
        @endif

        <section class="in-doc" id="page">
            <div>
                <span class="in-doc__label">{{ __t('messages.info_page.sections.' . $page->section) }}</span>
                <h2 class="in-doc__title">{{ $page->title($locale) }}</h2>
                <div class="in-prose">{!! $body !!}</div>
            </div>
            <aside class="in-doc__side">
                <div class="in-dark">
                    <h3 class="in-dark__title">{{ __t('messages.info_page.question') }}</h3>
                    <a class="in-mail" href="tel:+998712021966"><i class="bi bi-telephone" aria-hidden="true"></i> (+998 71) 202-19-66</a>
                    <a class="in-btn in-btn--on-dark" href="mailto:info@xalqsugurta.uz">info@xalqsugurta.uz</a>
                </div>
                <a class="in-btn in-btn--line" href="{{ route('info.index', ['locale' => $locale]) }}">← {{ __t('messages.info_page.all') }}</a>
            </aside>
        </section>
    </div>
</div>
@endsection
