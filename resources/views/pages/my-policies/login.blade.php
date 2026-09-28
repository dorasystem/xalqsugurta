@extends('layouts.app')
@section('title', __t('messages.my_policies.title'))

{{-- "Mening polislarim", signed out: phone → SMS code ($pending = phone waiting for its code) --}}
@php $locale = getCurrentLocale(); @endphp

@section('content')
<x-insurence.flow icon="bi-person-lines-fill" :title="__t('messages.my_policies.title')" :subtitle="__t('messages.my_policies.subtitle')">

    @if (session('status'))
        <p class="xf-note" role="status">
            <i class="bi bi-chat-dots"></i>
            <span>{{ session('status') }}</span>
        </p>
    @endif

    @if (!$available)
        <div class="xf-alert" role="alert">
            <i class="bi bi-exclamation-circle"></i>
            <span>{{ __t('messages.my_policies.error_unavailable') }}</span>
        </div>
    @endif

    @if ($pending)
        {{-- ── Step 2: code ── --}}
        <form action="{{ route('my-policies.verify', ['locale' => $locale]) }}" method="POST" class="xf-panel">
            @csrf
            <div class="xf-panel__head">
                <h2 class="xf-panel__title">{{ __t('messages.my_policies.code_title') }}</h2>
                <p class="xf-panel__subtitle">{{ __t('messages.my_policies.code_subtitle', ['phone' => formatPhone($pending)]) }}</p>
            </div>
            <div class="xf-panel__body">
                <x-insurence.field name="code" :label="__t('messages.my_policies.code_label')"
                    inputmode="numeric" maxlength="6" autocomplete="one-time-code" placeholder="••••••" autofocus />
            </div>
            <x-insurence.actions :submit="__t('messages.my_policies.sign_in')" />
        </form>

        <div class="xf-policy-links">
            <form action="{{ route('my-policies.send', ['locale' => $locale]) }}" method="POST">
                @csrf
                <input type="hidden" name="phone" value="{{ $pending }}">
                <button type="submit" class="xf-link-btn"><i class="bi bi-arrow-repeat"></i> {{ __t('messages.my_policies.resend') }}</button>
            </form>
            <form action="{{ route('my-policies.logout', ['locale' => $locale]) }}" method="POST">
                @csrf
                <button type="submit" class="xf-link-btn"><i class="bi bi-pencil"></i> {{ __t('messages.my_policies.other_phone') }}</button>
            </form>
        </div>
        @error('phone')
            <p class="xf-field__error" role="alert">{{ $message }}</p>
        @enderror
    @else
        {{-- ── Step 1: phone ── --}}
        <form action="{{ route('my-policies.send', ['locale' => $locale]) }}" method="POST" class="xf-panel">
            @csrf
            <div class="xf-panel__head">
                <h2 class="xf-panel__title">{{ __t('messages.my_policies.phone_title') }}</h2>
                <p class="xf-panel__subtitle">{{ __t('messages.my_policies.phone_subtitle') }}</p>
            </div>
            <div class="xf-panel__body">
                <x-insurence.field name="phone" type="tel" :label="__('messages.phone_number')"
                    :help="__t('messages.flow.phone_help')" inputmode="tel" placeholder="+998 90 123 45 67" autocomplete="tel" />
            </div>
            <x-insurence.actions :submit="__t('messages.my_policies.send_code')" />
        </form>
    @endif

</x-insurence.flow>
@endsection
