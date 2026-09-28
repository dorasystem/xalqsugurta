@extends('layouts.app')
@section('title', __t('insurance.payment.page_title'))

{{--
    Last step of every product: pay, then get the policy.
    $state: pay | policy_pending | paid | cancelled (PaymentController::show)
    $showDetails: phone, period and policy links only for the browser that made the order (or an admin)
--}}
@php
    $locale      = getCurrentLocale();
    $productName = $order->insuranceProductName ?: $order->product_name;
    $downloadUrl = $response['download_url'] ?? null;
    $polisCheck  = $response['polis_check'] ?? null;
    $polisNo     = trim(($response['polis_sery'] ?? '') . ' ' . ($response['polis_number'] ?? ''));
    $period      = $order->contractStartDate
        ? $order->contractStartDate->format('d.m.Y') . ' – ' . ($order->contractEndDate?->format('d.m.Y') ?? '…')
        : null;

    $summaryItems = array_filter([
        'product' => [__t('insurance.payment.product'), $productName],
        'order'   => [__t('insurance.payment.order_number'), '№' . $order->id],
        'phone'   => $showDetails ? [__t('messages.phone_number'), $order->phone ? formatPhone($order->phone) : null] : null,
        'period'  => $showDetails ? [__t('messages.flow.period'), $period] : null,
    ]);
@endphp

@section('content')
<x-insurence.flow
    icon="bi-credit-card-2-front"
    :title="__t('insurance.payment.page_title')"
    :subtitle="$productName"
    :steps="[__t('messages.flow.application'), __t('messages.confirm_details'), __t('messages.flow.payment')]"
    :current="3"
>
    @if (session('error'))
        <div class="xf-alert" role="alert">
            <i class="bi bi-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @switch ($state)

        {{-- ── Waiting for payment ── --}}
        @case('pay')
            <div class="xf-panel">
                <div class="xf-panel__head">
                    <h2 class="xf-panel__title">{{ __t('insurance.payment.select_payment_method') }}</h2>
                    <p class="xf-panel__subtitle">{{ __t('messages.flow.pay_subtitle') }}</p>
                </div>

                <div class="xf-panel__body">
                    <div class="xf-pay">
                        <a class="xf-pay__method" rel="noopener"
                           href="{{ $order->payme_url ?: route('payment.payme', ['id' => $order->id]) }}">
                            <img src="{{ asset('images/tolovTizimi/payme.svg') }}" alt="" width="84" height="28">
                            <span class="xf-pay__name">Payme <small>{{ __t('messages.flow.pay_cards') }}</small></span>
                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </a>

                        @if ($order->click_url)
                            <a class="xf-pay__method" rel="noopener" href="{{ $order->click_url }}">
                                <img src="{{ asset('images/tolovTizimi/click.svg') }}" alt="" width="84" height="28">
                                <span class="xf-pay__name">Click <small>{{ __t('messages.flow.pay_cards') }}</small></span>
                                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>

                    <p class="xf-note">
                        <i class="bi bi-info-circle"></i>
                        <span>
                            {{ __t('messages.flow.pay_after') }}
                            <a href="{{ request()->url() }}">{{ __t('messages.flow.refresh') }}</a>
                        </span>
                    </p>

                    <p class="xf-pay__secure">
                        <i class="bi bi-lock-fill"></i> {{ __t('insurance.payment.ssl_encrypted') }}
                    </p>
                </div>
            </div>
            @break

        {{-- ── Paid, policy is on its way ── --}}
        @case('policy_pending')
            <div class="xf-panel">
                <div class="xf-panel__body">
                    <x-insurence.found :title="__t('messages.flow.paid_title')" :text="__t('messages.flow.policy_pending')" />
                    <p class="xf-note">
                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <span>{{ __t('messages.flow.policy_pending_text') }}</span>
                    </p>
                </div>
            </div>
            @push('scripts')
                <script>setTimeout(function () { location.reload(); }, 15000);</script>
            @endpush
            @break

        {{-- ── Paid ── --}}
        @case('paid')
            <div class="xf-panel">
                <div class="xf-panel__body">
                    <x-insurence.found
                        :title="__t('messages.flow.paid_title')"
                        :text="$polisNo ? __t('messages.flow.policy_number', ['number' => $polisNo]) : __t('messages.flow.paid_text')"
                    />

                    @if ($showDetails && ($downloadUrl || $polisCheck))
                        <div class="xf-pay">
                            @if ($downloadUrl)
                                <a class="xf-pay__method is-primary" href="{{ $downloadUrl }}" target="_blank" rel="noopener">
                                    <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>
                                    <span class="xf-pay__name">{{ __t('messages.download_policy') }}</span>
                                    <i class="bi bi-download" aria-hidden="true"></i>
                                </a>
                            @endif
                            @if ($polisCheck)
                                <a class="xf-pay__method" href="{{ $polisCheck }}" target="_blank" rel="noopener">
                                    <i class="bi bi-patch-check" aria-hidden="true"></i>
                                    <span class="xf-pay__name">{{ __t('messages.verify_policy') }}</span>
                                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                </a>
                            @endif
                        </div>
                    @elseif (!$showDetails)
                        <p class="xf-note">
                            <i class="bi bi-shield-lock"></i>
                            <span>{{ __t('messages.flow.details_private') }}</span>
                        </p>
                    @endif
                </div>
            </div>
            @break

        {{-- ── Cancelled / failed ── --}}
        @case('cancelled')
            <div class="xf-panel">
                <div class="xf-panel__body">
                    <div class="xf-alert" role="alert">
                        <i class="bi bi-x-circle"></i>
                        <span>{{ __t('messages.flow.cancelled_text') }}</span>
                    </div>
                    <div>
                        <a class="xf-btn xf-btn--primary" href="{{ route('home', ['locale' => $locale]) }}">
                            <i class="bi bi-arrow-left"></i> {{ __t('messages.flow.all_products') }}
                        </a>
                    </div>
                </div>
            </div>
            @break

    @endswitch

    <x-slot:summary>
        <x-insurence.summary :premium="(int) $order->amount" :items="$summaryItems" />
    </x-slot:summary>
</x-insurence.flow>
@endsection
