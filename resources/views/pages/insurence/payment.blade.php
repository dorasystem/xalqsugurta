@extends('layouts.app')
@section('title', __('insurance.payment.page_title'))

@section('content')
<section class="py-8 bg-gray-50 min-h-screen">
<div class="container">

    <x-insurence.page-header icon="bi-shield-check" :title="__('insurance.payment.page_title')" :subtitle="__('insurance.payment.subtitle')" />

    <div class="row g-4 justify-content-center">
        <div class="col-lg-6">

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 mb-4 d-flex align-items-center gap-3">
                    <i class="bi bi-exclamation-circle-fill text-red-500" style="font-size:18px;"></i>
                    <span class="text-sm text-red-700">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Order summary --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">{{ $order->insuranceProductName ?? $order->product_name }}</p>
                        <p class="text-2xl font-bold text-gray-800 mb-0">{{ number_format($order->amount, 0, '.', ' ') }} UZS</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold" style="background:#eff6ff;color:#2563eb;">
                        #{{ $order->id }}
                    </span>
                </div>
                <div class="row g-2 text-sm">
                    <div class="col-6">
                        <p class="text-xs text-gray-400 mb-0">{{ __('messages.phone_number') }}</p>
                        <p class="font-semibold text-gray-800 mb-0">+{{ $order->phone }}</p>
                    </div>
                    @if($order->contractStartDate && $order->contractEndDate)
                    <div class="col-6">
                        <p class="text-xs text-gray-400 mb-0">{{ __('messages.insurance_period') ?? __('insurance.payment.insurance_period') ?? 'Davr' }}</p>
                        <p class="font-semibold text-gray-800 mb-0">
                            {{ \Carbon\Carbon::parse($order->contractStartDate)->format('d.m.Y') }}
                            — {{ \Carbon\Carbon::parse($order->contractEndDate)->format('d.m.Y') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Polis (after payment) --}}
            @php
                $downloadUrl  = $order->insurances_response_data['download_url'] ?? null;
                $polisCheck   = $order->insurances_response_data['polis_check']  ?? null;
                $polisSery    = $order->insurances_response_data['polis_sery']   ?? null;
                $polisNumber  = $order->insurances_response_data['polis_number'] ?? null;
            @endphp
            @if($downloadUrl || $polisCheck)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#f0fdf4;">
                        <i class="bi bi-shield-check" style="color:#16a34a;font-size:16px;"></i>
                    </div>
                    <span class="font-semibold text-gray-800 text-sm">
                        {{ __('messages.your_policy') ?? 'Sizning polisingiz' }}
                        @if($polisSery && $polisNumber)
                            <span class="text-gray-400 font-normal">&mdash; {{ $polisSery }}-{{ $polisNumber }}</span>
                        @endif
                    </span>
                </div>
                <div class="p-5 d-flex flex-column gap-3">
                    @if($downloadUrl)
                    <a href="{{ $downloadUrl }}" target="_blank"
                        class="d-flex align-items-center gap-3 p-4 rounded-xl text-decoration-none"
                        style="border:1.5px solid #bbf7d0;background:#f0fdf4;">
                        <i class="bi bi-file-earmark-pdf-fill" style="color:#16a34a;font-size:20px;"></i>
                        <span class="font-semibold text-sm flex-1" style="color:#15803d;">{{ __('messages.download_policy') ?? 'Polisni yuklab olish (PDF)' }}</span>
                        <i class="bi bi-download" style="color:#16a34a;"></i>
                    </a>
                    @endif
                    @if($polisCheck)
                    <a href="{{ $polisCheck }}" target="_blank"
                        class="d-flex align-items-center gap-3 p-4 rounded-xl text-decoration-none"
                        style="border:1.5px solid #e5e7eb;background:#fff;">
                        <i class="bi bi-patch-check-fill" style="color:#2563eb;font-size:20px;"></i>
                        <span class="font-semibold text-sm flex-1 text-gray-800">{{ __('messages.verify_policy') ?? 'Polisni tekshirish' }}</span>
                        <i class="bi bi-arrow-right text-gray-400"></i>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Payment methods --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                <div class="px-5 py-4 border-bottom">
                    <span class="font-semibold text-gray-800 text-sm">{{ __('insurance.payment.select_payment_method') }}</span>
                </div>
                <div class="p-5 d-flex flex-column gap-3">

                    @if($order->payme_url)
                    <a href="{{ $order->payme_url }}" target="_blank"
                        class="d-flex align-items-center gap-3 p-4 rounded-xl text-decoration-none"
                        style="border:1.5px solid #e5e7eb;background:#fff;">
                        <img src="{{ asset('images/tolovTizimi/payme.svg') }}" alt="Payme" style="height:28px;width:auto;">
                        <span class="font-semibold text-gray-800 text-sm flex-1">Payme</span>
                        <i class="bi bi-arrow-right text-gray-400"></i>
                    </a>
                    @else
                    <a href="{{ route('payment.payme', ['id' => $order->id]) }}" target="_blank"
                        class="d-flex align-items-center gap-3 p-4 rounded-xl text-decoration-none"
                        style="border:1.5px solid #e5e7eb;background:#fff;">
                        <img src="{{ asset('images/tolovTizimi/payme.svg') }}" alt="Payme" style="height:28px;width:auto;">
                        <span class="font-semibold text-gray-800 text-sm flex-1">Payme</span>
                        <i class="bi bi-arrow-right text-gray-400"></i>
                    </a>
                    @endif

                    @if($order->click_url)
                    <a href="{{ $order->click_url }}" target="_blank"
                        class="d-flex align-items-center gap-3 p-4 rounded-xl text-decoration-none"
                        style="border:1.5px solid #e5e7eb;background:#fff;">
                        <img src="{{ asset('images/tolovTizimi/click.svg') }}" alt="Click" style="height:28px;width:auto;">
                        <span class="font-semibold text-gray-800 text-sm flex-1">Click</span>
                        <i class="bi bi-arrow-right text-gray-400"></i>
                    </a>
                    @endif

                </div>
                <div class="px-5 py-3 border-top d-flex align-items-center gap-2 text-xs text-gray-400" style="background:#f9fafb;">
                    <i class="bi bi-lock-fill"></i>
                    <span>{{ __('insurance.payment.ssl_encrypted') }}</span>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('home', ['locale' => getCurrentLocale()]) }}"
                    class="text-sm text-gray-400 text-decoration-none">
                    <i class="bi bi-house me-1"></i>{{ __('messages.home') ?? 'Bosh sahifa' }}
                </a>
            </div>

        </div>
    </div>

</div>
</section>
@endsection
