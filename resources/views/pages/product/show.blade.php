@extends('layouts.app')
@section('title', $product->{'name_' . $locale})

{{-- Product info page. $info = Product::info(): about / claim (clean HTML), faq ([q, a]) --}}
@php
    $storage = \Illuminate\Support\Facades\Storage::disk('public');
    $rules   = $product->{'rules_' . $locale};
    $offerta = $product->{'offerta_' . $locale};
@endphp

@section('content')
<x-insurence.flow :icon="$product->icon ?: 'bi-shield-check'" :title="$product->{'name_' . $locale}" :subtitle="$product->{'desc_' . $locale}">

    @isset($info['about'])
        <section class="xf-panel" aria-labelledby="about_title">
            <div class="xf-panel__head">
                <h2 class="xf-panel__title" id="about_title">{{ __t('messages.product_page.about') }}</h2>
            </div>
            <div class="xf-panel__body xf-prose">{!! $info['about'] !!}</div>
        </section>
    @endisset

    @isset($info['claim'])
        <section class="xf-panel" aria-labelledby="claim_title">
            <div class="xf-panel__head">
                <h2 class="xf-panel__title" id="claim_title"><i class="bi bi-life-preserver"></i> {{ __t('messages.product_page.claim') }}</h2>
            </div>
            <div class="xf-panel__body xf-prose">{!! $info['claim'] !!}</div>
        </section>
    @endisset

    @isset($info['faq'])
        <section class="xf-panel" aria-labelledby="faq_title">
            <div class="xf-panel__head">
                <h2 class="xf-panel__title" id="faq_title">{{ __t('messages.product_page.faq') }}</h2>
            </div>
            <div class="xf-panel__body xf-faq">
                @foreach ($info['faq'] as $item)
                    <details class="xf-faq__item">
                        <summary>{{ $item['q'] }}</summary>
                        <p>{!! nl2br(e($item['a'])) !!}</p>
                    </details>
                @endforeach
            </div>
        </section>
    @endisset

    <x-slot:summary>
        <aside class="xf-panel xf-summary xf-product-aside">
            <div class="xf-summary__top">
                <small>{{ __t('messages.product_page.online') }}</small>
                <a class="xf-btn xf-product-cta" href="{{ $product->url() }}">
                    {{ __t('messages.buy_policy') }} <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <ul class="xf-summary__list xf-product-docs">
                <li><a href="{{ route('claims.create', ['locale' => $locale, 'product' => $product->route]) }}"><i class="bi bi-life-preserver"></i> {{ __t('messages.claims.title') }}</a></li>
            </ul>

            @if ($rules || $offerta)
                <ul class="xf-summary__list xf-product-docs">
                    @if ($rules)
                        <li><a href="{{ $storage->url($rules) }}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf"></i> {{ __t('messages.product_page.rules') }}</a></li>
                    @endif
                    @if ($offerta)
                        <li><a href="{{ $storage->url($offerta) }}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-text"></i> {{ __t('messages.product_page.offerta') }}</a></li>
                    @endif
                </ul>
            @endif

            <div class="xf-summary__help">
                <i class="bi bi-telephone"></i>
                <span>{{ __t('messages.flow.help') }} <a href="tel:+998712021966">(+998 71) 202-19-66</a></span>
            </div>
        </aside>
    </x-slot:summary>

</x-insurence.flow>
@endsection
