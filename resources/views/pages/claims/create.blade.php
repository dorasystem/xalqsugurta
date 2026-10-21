@extends('layouts.app')
@section('title', __t('messages.claims.title'))

{{-- Insured-event report. $products: route => name; $prefill from an order of "Mening polislarim" --}}
@php
    $locale  = getCurrentLocale();
    $maxMb   = (int) (\App\Http\Controllers\ClaimController::MAX_FILE_KB / 1024);
@endphp

@section('content')
<x-insurence.flow icon="bi-life-preserver" :title="__t('messages.claims.title')" :subtitle="__t('messages.claims.subtitle')">

    <form action="{{ route('claims.store', ['locale' => $locale]) }}" method="POST" enctype="multipart/form-data" class="xf-panel">
        @csrf
        <input type="hidden" name="order_id" value="{{ old('order_id', $prefill['order_id']) }}">
        {{-- Honeypot: hidden from people, bots fill it --}}
        <div class="visually-hidden" aria-hidden="true">
            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </div>

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.claims.policy_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.claims.policy_subtitle') }}</p>
        </div>
        <div class="xf-panel__body">
            <div class="xf-row">
                <div class="xf-field">
                    <label for="f_product" class="xf-field__label">{{ __t('messages.claims.product') }}</label>
                    <select name="product" id="f_product" class="xf-input">
                        <option value="">{{ __t('messages.claims.product_other') }}</option>
                        @foreach ($products as $route => $name)
                            <option value="{{ $route }}" @selected(old('product', $prefill['product']) === $route)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-insurence.field name="policy_number" :label="__t('messages.claims.policy_number')" :value="$prefill['policy_number']"
                    :help="__t('messages.claims.policy_number_help')" maxlength="40" autocomplete="off" style="text-transform: uppercase" />
            </div>
            <div class="xf-row">
                <x-insurence.field name="full_name" :label="__('messages.full_name')" :value="$prefill['full_name']" maxlength="150" autocomplete="name" />
                <x-insurence.field name="phone" type="tel" :label="__('messages.phone_number')" :value="$prefill['phone'] ? formatPhone($prefill['phone']) : null"
                    :help="__t('messages.flow.phone_help')" inputmode="tel" placeholder="+998 90 123 45 67" autocomplete="tel" />
            </div>
        </div>

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.claims.event_title') }}</h2>
        </div>
        <div class="xf-panel__body">
            <div class="xf-row">
                <x-insurence.field name="event_date" type="date" :label="__t('messages.claims.event_date')" :max="now()->format('Y-m-d')" />
            </div>
            <div class="xf-field">
                <label for="f_description" class="xf-field__label">{{ __t('messages.claims.description') }}</label>
                <textarea name="description" id="f_description" class="xf-input @error('description') is-invalid @enderror" rows="6"
                    maxlength="3000" placeholder="{{ __t('messages.claims.description_help') }}">{{ old('description') }}</textarea>
                @error('description')
                    <p class="xf-field__error">{{ $message }}</p>
                @enderror
            </div>
            <div class="xf-field">
                <label for="f_files" class="xf-field__label">{{ __t('messages.claims.files') }}</label>
                <input type="file" name="files[]" id="f_files" class="xf-file" multiple accept=".jpg,.jpeg,.png,.pdf">
                <p class="xf-field__help">{{ __t('messages.claims.files_help', ['max' => \App\Http\Controllers\ClaimController::MAX_FILES, 'mb' => $maxMb]) }}</p>
                @foreach (['files', 'files.*'] as $key)
                    @error($key)
                        <p class="xf-field__error">{{ $message }}</p>
                    @enderror
                @endforeach
            </div>
        </div>

        <x-insurence.actions :payment="false" :submit="__t('messages.claims.send')" />
    </form>

    <x-slot:summary>
        <aside class="xf-panel xf-summary">
            <div class="xf-summary__top">
                <small>{{ __t('messages.claims.urgent') }}</small>
                <a class="xf-btn xf-product-cta" href="tel:+998712021966"><i class="bi bi-telephone"></i> (+998 71) 202-19-66</a>
            </div>
            <ul class="xf-summary__list xf-product-docs">
                <li><a href="{{ route('claims.status', ['locale' => $locale]) }}"><i class="bi bi-search"></i> {{ __t('messages.claims.check_status') }}</a></li>
                <li><a href="{{ route('callback', ['locale' => $locale]) }}"><i class="bi bi-telephone-inbound"></i> {{ __t('messages.callback.title') }}</a></li>
            </ul>
        </aside>
    </x-slot:summary>

</x-insurence.flow>
@endsection
