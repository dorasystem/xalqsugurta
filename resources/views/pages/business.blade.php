@extends('layouts.app')
@section('title', __t('messages.business_page.title'))

@push('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Unbounded:wght@600;800&display=swap">
    <link rel="stylesheet" href="{{ assetVersioned('assets/css/home.css') }}">
    <link rel="stylesheet" href="{{ assetVersioned('assets/css/flow.css') }}">
@endpush

{{-- "For business" section. $online = Product rows sold online, $offers = individual-offer keys, $documents = key => url --}}
@section('content')
@php
    $name  = fn ($p) => $p->{'name_' . $locale} ?: $p->name_uz;
    $desc  = fn ($p) => $p->{'desc_' . $locale} ?: $p->desc_uz;
    $tints = ['#f3f1fb', '#eaf2fb', '#eef6f1'];
@endphp

<div class="hp">

    {{-- ─── Hero ─────────────────────────────────────────────────────────── --}}
    <section class="hp-hero">
        <span class="hp-ring hp-ring--1" aria-hidden="true"></span>
        <span class="hp-ring hp-ring--2" aria-hidden="true"></span>
        <span class="hp-blob" aria-hidden="true"></span>

        <div class="hp-wrap hp-hero__grid">
            <div class="hp-hero__copy">
                <span class="hp-pill"><span class="hp-pill__dot"></span>{{ __t('messages.business_page.badge') }}</span>
                <h1 class="hp-hero__title">
                    {{ __t('messages.business_page.title_1') }}<br>
                    <span>{{ __t('messages.business_page.title_2') }}</span>
                </h1>
                <p class="hp-hero__lead">{{ __t('messages.business_page.lead') }}</p>
                <div class="hp-hero__actions">
                    @if ($online->isNotEmpty())
                        <a href="#products" class="hp-btn hp-btn--accent hp-btn--lg">{{ __t('messages.business_page.cta_online') }}</a>
                    @endif
                    <a href="#offer" class="hp-btn hp-btn--ghost hp-btn--lg">{{ __t('messages.business_page.cta_offer') }}</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── Online products ──────────────────────────────────────────────── --}}
    @if ($online->isNotEmpty())
        <section class="hp-wrap hp-section" id="products">
            <h2 class="hp-h2">{{ __t('messages.business_page.online_title') }}</h2>
            <div class="hp-bento biz-bento">
                @foreach ($online as $p)
                    <a href="{{ $p->cardUrl() }}" class="hp-tile hp-tile--span-{{ $online->count() === 3 ? ($loop->first ? 2 : 1) : 2 }}" style="--hp-tint: {{ $tints[$loop->index % count($tints)] }}">
                        <span class="hp-tile__top">
                            <span class="hp-tile__icon"><i class="{{ $p->icon }}" aria-hidden="true"></i></span>
                            <span class="hp-tile__rate">{{ __t('messages.business_page.online_' . $p->route) }}</span>
                        </span>
                        <span class="hp-tile__body">
                            <span class="hp-tile__name">{{ $name($p) }}</span>
                            <span class="hp-tile__text">{{ $desc($p) }}</span>
                            <span class="hp-tile__go">{{ __t('messages.homepage.apply') }} →</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Individual offer ─────────────────────────────────────────────── --}}
    <section class="hp-wrap hp-section biz-offer" id="offer">
        <div class="biz-offer__copy">
            <h2 class="hp-h2">{{ __t('messages.business_page.offer_title') }}</h2>
            <p>{{ __t('messages.business_page.offer_text') }}</p>
            <ul class="biz-offer__list">
                @foreach ($offers as $key)
                    @continue($key === 'other')
                    <li>{{ __t('messages.sections.products.' . $key) }}</li>
                @endforeach
            </ul>
        </div>

        {{-- .xf gives the form the flow look (tokens and field styles) --}}
        <div class="xf biz-offer__form">
        <form action="{{ route('business.request', ['locale' => $locale]) }}" method="POST" class="xf-panel">
            @csrf
            <div class="visually-hidden" aria-hidden="true">
                <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>
            <div class="xf-panel__head">
                <h3 class="xf-panel__title">{{ __t('messages.business_page.form.title') }}</h3>
            </div>
            <div class="xf-panel__body">
                @if (session('status'))
                    <x-insurence.found :title="session('status')" :text="__t('messages.business_page.form.sent_text')" />
                @endif
                <div class="xf-row">
                    <x-insurence.field name="company" :label="__t('messages.business_page.form.company')" maxlength="150" autocomplete="organization" />
                    <x-insurence.field name="inn" :label="__t('messages.business_page.form.inn')" inputmode="numeric" maxlength="9" :help="__t('messages.business_page.form.inn_help')" />
                </div>
                <div class="xf-field">
                    <label for="f_product" class="xf-field__label">{{ __t('messages.business_page.form.product') }}</label>
                    <select name="product" id="f_product" class="xf-input">
                        @foreach ($offers as $key)
                            <option value="{{ $key }}" @selected(old('product') === $key)>{{ __t('messages.sections.products.' . $key) }}</option>
                        @endforeach
                    </select>
                    @error('product')<span class="xf-field__error">{{ $message }}</span>@enderror
                </div>
                <div class="xf-row">
                    <x-insurence.field name="name" :label="__t('messages.business_page.form.name')" maxlength="100" autocomplete="name" />
                    <x-insurence.field name="phone" type="tel" :label="__('messages.phone_number')" inputmode="tel" placeholder="+998 90 123 45 67" autocomplete="tel" />
                </div>
                <div class="xf-field">
                    <label for="f_message" class="xf-field__label">{{ __t('messages.callback.message') }}</label>
                    <textarea name="message" id="f_message" class="xf-input" rows="3" maxlength="1000">{{ old('message') }}</textarea>
                </div>
            </div>
            <x-insurence.actions :payment="false" :submit="__t('messages.business_page.form.send')" />
        </form>
        </div>
    </section>

    {{-- ─── Partners and investors ───────────────────────────────────────── --}}
    <section class="hp-wrap hp-faq">
        <div class="hp-faq__side">
            <h2 class="hp-h2">{{ __t('messages.business_page.partners_title') }}</h2>
            <p>{{ __t('messages.business_page.partners_text') }}</p>
        </div>
        <div class="hp-docs biz-docs">
            @foreach ($documents as $key => $url)
                <a href="{{ $url }}">{{ __t('messages.business_page.docs.' . $key) }} <span aria-hidden="true">→</span></a>
            @endforeach
        </div>
    </section>
</div>
@endsection
