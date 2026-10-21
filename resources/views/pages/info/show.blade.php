@extends('layouts.app')
@section('title', $page->title($locale))

{{-- Company / disclosure page. $body = InfoPage::body() (clean HTML), $siblings = published pages of the section --}}
@section('content')
<x-insurence.flow icon="bi-building" :title="$page->title($locale)" :subtitle="__t('messages.info_page.sections.' . $page->section)">

    <section class="xf-panel">
        <div class="xf-panel__body xf-prose">{!! $body !!}</div>
    </section>

    <x-slot:summary>
        <aside class="xf-panel xf-summary">
            @if ($siblings->count() > 1)
                <div class="xf-summary__top">
                    <small>{{ __t('messages.info_page.in_section') }}</small>
                </div>
                <ul class="xf-summary__list xf-product-docs">
                    @foreach ($siblings as $sibling)
                        <li>
                            @if ($sibling->is($page))
                                <strong aria-current="page">{{ $sibling->title($locale) }}</strong>
                            @else
                                <a href="{{ route('info.show', ['locale' => $locale, 'key' => $sibling->key]) }}">{{ $sibling->title($locale) }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="xf-summary__help">
                <i class="bi bi-telephone"></i>
                <span>{{ __t('messages.info_page.question') }} <a href="tel:+998712021966">(+998 71) 202-19-66</a></span>
            </div>
        </aside>
    </x-slot:summary>

</x-insurence.flow>
@endsection
