@extends('layouts.app')
@section('title', __t('messages.claims.check_status'))

{{-- Claim status by number + phone. $claim null when not searched or not found --}}
@php $locale = getCurrentLocale(); @endphp

@section('content')
<x-insurence.flow icon="bi-search" :title="__t('messages.claims.check_status')" :subtitle="__t('messages.claims.status_subtitle')">

    <form action="{{ route('claims.status', ['locale' => $locale]) }}" method="GET" class="xf-panel">
        <div class="xf-panel__body">
            <div class="xf-row">
                <x-insurence.field name="number" :label="__t('messages.claims.number')" :value="request('number')"
                    placeholder="ZH-260101-1234" maxlength="20" autocomplete="off" style="text-transform: uppercase" />
                <x-insurence.field name="phone" type="tel" :label="__('messages.phone_number')" :value="request('phone') ? formatPhone(request('phone')) : null"
                    inputmode="tel" placeholder="+998 90 123 45 67" autocomplete="tel" />
            </div>
        </div>
        <x-insurence.actions :payment="false" :submit="__t('messages.claims.find')" />
    </form>

    @if ($claim)
        @php $status = $claim->status; @endphp
        <article class="xf-panel xf-policy">
            <div class="xf-policy__head">
                <div>
                    <h2 class="xf-policy__title">{{ $claim->number }}</h2>
                    <p class="xf-policy__meta">
                        {{ $products[$claim->product] ?? __t('messages.claims.product_other') }} · {{ $claim->policy_number }}
                        · {{ $claim->created_at->format('d.m.Y') }}
                    </p>
                </div>
                <span class="xf-policy__status is-{{ in_array($status, ['approved', 'paid'], true) ? 'paid' : ($status === 'rejected' ? 'cancelled' : 'new') }}">
                    {{ __t('messages.claims.status_' . $status) }}
                </span>
            </div>
            @if ($claim->public_note)
                <p class="xf-note"><i class="bi bi-chat-left-text"></i><span>{!! nl2br(e($claim->public_note)) !!}</span></p>
            @endif
            <p class="xf-policy__meta">{{ __t('messages.claims.updated', ['date' => $claim->updated_at->format('d.m.Y H:i')]) }}</p>
        </article>
    @elseif ($searched && !$errors->any())
        <div class="xf-alert" role="alert">
            <i class="bi bi-exclamation-circle"></i>
            <span>{{ __t('messages.claims.not_found') }}</span>
        </div>
    @endif

</x-insurence.flow>
@endsection
