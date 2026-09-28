@extends('layouts.app')
@section('title', __t('messages.my_policies.title'))

{{-- "Mening polislarim", signed in: every order placed with the verified $phone --}}
@php $locale = getCurrentLocale(); @endphp

@section('content')
<x-insurence.flow icon="bi-person-lines-fill" :title="__t('messages.my_policies.title')" :subtitle="formatPhone($phone)">

    <div class="xf-policy-links">
        <span></span>
        <form action="{{ route('my-policies.logout', ['locale' => $locale]) }}" method="POST">
            @csrf
            <button type="submit" class="xf-link-btn"><i class="bi bi-box-arrow-right"></i> {{ __t('messages.my_policies.sign_out') }}</button>
        </form>
    </div>

    @forelse ($orders as $order)
        @php
            $response = $order->insurances_response_data ?? [];
            $polisNo  = trim(($response['polis_sery'] ?? '') . ' ' . ($response['polis_number'] ?? ''));
            $status   = in_array($order->status, ['paid', 'cancelled', 'failed'], true) ? $order->status : 'new';
        @endphp
        <article class="xf-panel xf-policy">
            <div class="xf-policy__head">
                <div>
                    <h2 class="xf-policy__title">{{ $order->insuranceProductName ?: $order->product_name }}</h2>
                    <p class="xf-policy__meta">
                        №{{ $order->id }}
                        @if ($polisNo) · {{ __t('messages.my_policies.policy') }} {{ $polisNo }} @endif
                        · {{ $order->created_at?->format('d.m.Y') }}
                    </p>
                </div>
                <span class="xf-policy__status is-{{ $status }}">{{ __t('messages.my_policies.status_' . $status) }}</span>
            </div>

            <dl class="xf-policy__facts">
                @if ($order->contractStartDate)
                    <div><dt>{{ __t('messages.flow.period') }}</dt>
                        <dd>{{ $order->contractStartDate->format('d.m.Y') }} – {{ $order->contractEndDate?->format('d.m.Y') ?? '…' }}</dd></div>
                @endif
                <div><dt>{{ __('messages.insurance_premium') }}</dt><dd>{{ formatMoney((int) $order->amount) }}</dd></div>
            </dl>

            <div class="xf-policy__actions">
                @if (!empty($response['download_url']))
                    <a class="xf-btn xf-btn--primary" href="{{ $response['download_url'] }}" target="_blank" rel="noopener">
                        <i class="bi bi-file-earmark-pdf"></i> {{ __('messages.download_policy') }}
                    </a>
                @endif
                @if (!empty($response['polis_check']))
                    <a class="xf-btn xf-btn--soft" href="{{ $response['polis_check'] }}" target="_blank" rel="noopener">
                        <i class="bi bi-patch-check"></i> {{ __('messages.verify_policy') }}
                    </a>
                @endif
                <a class="xf-btn {{ $status === 'new' ? 'xf-btn--primary' : 'xf-btn--ghost' }}"
                   href="{{ route('payment.show', ['locale' => $locale, 'orderId' => $order->id]) }}">
                    @if ($status === 'new')
                        <i class="bi bi-credit-card"></i> {{ __t('messages.my_policies.pay') }}
                    @else
                        {{ __t('messages.my_policies.details') }} <i class="bi bi-arrow-right"></i>
                    @endif
                </a>
            </div>
        </article>
    @empty
        <div class="xf-panel">
            <div class="xf-panel__body">
                <p class="xf-note">
                    <i class="bi bi-info-circle"></i>
                    <span>{{ __t('messages.my_policies.empty') }}</span>
                </p>
                <div>
                    <a class="xf-btn xf-btn--primary" href="{{ route('home', ['locale' => $locale]) }}">{{ __t('messages.flow.all_products') }}</a>
                </div>
            </div>
        </div>
    @endforelse

    <p class="xf-note">
        <i class="bi bi-shield-lock"></i>
        <span>{{ __t('messages.my_policies.only_site') }}</span>
    </p>

</x-insurence.flow>
@endsection
