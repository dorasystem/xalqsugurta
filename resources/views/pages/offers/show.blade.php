@extends('layouts.app')
@section('title', $product->text('title', $locale))

{{--
    Product sold on application, design "A": dark hero with the request card, what it covers, main terms,
    ways to apply. $product = OfferProduct (its form fields: OfferProduct::FORMS)
--}}
@push('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Unbounded:wght@600;800&display=swap">
    <link rel="stylesheet" href="{{ assetVersioned('assets/css/inner.css') }}">
@endpush

@section('content')
@php
    $fields = $product->formFields();
    $highlightIcons = ['bi-clock', 'bi-people', 'bi-shield-check'];
    $section = $product->audience === 'business' ? 'business' : 'home';
@endphp
<div class="in">
    <section class="in-hero">
        <div class="in-wrap in-hero__grid">
            <div class="in-hero__copy">
                <nav class="in-crumbs in-crumbs--dark" aria-label="breadcrumb">
                    <a href="{{ route('home', ['locale' => $locale]) }}">{{ __t('messages.error_page.home') }}</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ route($section, ['locale' => $locale]) }}">{{ __t('messages.sections.' . ($product->audience === 'business' ? 'business' : 'private')) }}</a>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page">{{ $product->text('title', $locale) }}</span>
                </nav>
                @if ($kicker = $product->text('kicker', $locale))
                    <span class="in-hero__kicker">{{ $kicker }}</span>
                @endif
                <h1 class="in-hero__title">{{ $product->text('title', $locale) }}</h1>
                @if ($lead = $product->text('lead', $locale))
                    <p class="in-hero__lead">{{ $lead }}</p>
                @endif
                @if ($highlights = $product->lines('highlights', $locale))
                    <ul class="in-hero__chips">
                        @foreach ($highlights as $i => $line)
                            <li><i class="bi {{ $highlightIcons[$i % count($highlightIcons)] }}" aria-hidden="true"></i>{{ $line }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <form action="{{ route('offers.request', ['locale' => $locale, 'slug' => $product->slug]) }}" method="POST" class="in-apply in-form" id="apply">
                @csrf
                <div class="in-sr" aria-hidden="true">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <div>
                    <h2 class="in-panel__title">{{ __t('messages.offer.title') }}</h2>
                    <p class="in-meta">{{ __t('messages.offer.subtitle') }}</p>
                </div>

                @if (session('status'))
                    <div class="in-ok" role="status">
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        <div><b>{{ session('status') }}</b><span>{{ __t('messages.offer.sent_text') }}</span></div>
                    </div>
                @endif

                @foreach ($fields as $field)
                    @if ($field === 'members')
                        <fieldset class="in-field in-members">
                            <legend>{{ __t('messages.offer.members') }}</legend>
                            <div class="in-members__row">
                                @foreach ([1, 2, 3, 4, 5] as $n)
                                    <label>
                                        <input type="radio" name="members" value="{{ $n }}" @checked((int) old('members', 3) === $n)>
                                        <span>{{ $n === 5 ? '5+' : $n }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('members')<p class="in-error">{{ $message }}</p>@enderror
                        </fieldset>
                    @else
                        @php
                            [$type, $attrs] = match ($field) {
                                'phone'     => ['tel', ['inputmode' => 'tel', 'placeholder' => '+998 90 123 45 67', 'autocomplete' => 'tel']],
                                'employees' => ['number', ['min' => 1, 'inputmode' => 'numeric']],
                                'company'   => ['text', ['maxlength' => 150, 'autocomplete' => 'organization']],
                                default     => ['text', ['maxlength' => 100, 'autocomplete' => 'name']],
                            };
                        @endphp
                        <label class="in-field" for="f_{{ $field }}">
                            {{ $field === 'phone' ? __('messages.phone_number') : __t('messages.offer.' . $field) }}
                            <input type="{{ $type }}" name="{{ $field }}" id="f_{{ $field }}" value="{{ old($field) }}" @class(['in-input', 'is-invalid' => $errors->has($field)])
                                @foreach ($attrs as $k => $v) {{ $k }}="{{ $v }}" @endforeach>
                            @error($field)<p class="in-error">{{ $message }}</p>@enderror
                        </label>
                    @endif
                @endforeach

                <button type="submit" class="in-submit in-submit--gold">{{ __t('messages.offer.send') }}</button>
            </form>
        </div>
    </section>

    <div class="in-wrap">
        <div class="in-offer-cards">
            @if ($covers = $product->lines('covers', $locale))
                <section class="in-panel">
                    <h2 class="in-panel__title">{{ __t('messages.offer.covers') }}</h2>
                    <ul class="in-covers">
                        @foreach ($covers as $line)
                            <li><span><i class="bi bi-check2-circle" aria-hidden="true"></i></span>{{ $line }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($terms = $product->terms($locale))
                <section class="in-panel">
                    <h2 class="in-panel__title">{{ __t('messages.offer.terms') }}</h2>
                    <ul class="in-terms">
                        @foreach ($terms as $i => [$badge, $text])
                            <li>
                                @if ($badge)
                                    <b @class(['in-terms__badge', 'is-gold' => $badge === '100%'])>{{ $badge }}</b>
                                @endif
                                <span>{{ $text }}</span>
                            </li>
                        @endforeach
                    </ul>
                    @if ($note = $product->text('note', $locale))
                        <p class="in-meta">{{ $note }}</p>
                    @endif
                </section>
            @endif
        </div>

        <ul class="in-ways">
            <li><span><i class="bi bi-ui-checks" aria-hidden="true"></i></span><a href="#apply"><b>{{ __t('messages.offer.way_online') }}</b>{{ __t('messages.offer.way_online_text') }}</a></li>
            <li><span><i class="bi bi-telephone" aria-hidden="true"></i></span><a href="tel:+998712021966"><b>{{ __t('messages.offer.way_phone') }}</b>(+998 71) 202-19-66</a></li>
            <li><span><i class="bi bi-geo-alt" aria-hidden="true"></i></span><a href="{{ route('branches', ['locale' => $locale]) }}"><b>{{ __t('messages.offer.way_office') }}</b>{{ __t('messages.offer.way_office_text') }}</a></li>
        </ul>
    </div>
</div>
@endsection
