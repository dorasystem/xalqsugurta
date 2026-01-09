@extends('layouts.app')
@section('title', __('insurance.payment.page_title'))
@section('content')
    <x-insurence.steps :activeStep="3" />
    <section class="container-fluid product-page py-4">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="card-title mb-0">
                                        <i class="fas fa-credit-card"></i>
                                        {{ __('insurance.payment.title') }}
                                    </h3>
                                    <p class="text-muted mb-0">{{ __('insurance.payment.subtitle') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Order Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-info-circle"></i>{{ __('insurance.payment.order_info') }}</h5>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>{{ __('insurance.payment.order_number') }}:</strong> #{{ $order->id }}</p>
                                                <p class="mb-2"><strong>{{ __('insurance.payment.product') }}:</strong>
                                                    {{ strtoupper($order->product_name) }}</p>
                                                <p class="mb-2"><strong>{{ __('insurance.payment.phone') }}:</strong> {{ $order->phone }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>{{ __('insurance.payment.insurance_id') }}:</strong> {{ $order->insurance_id }}
                                                </p>
                                                <p class="mb-2"><strong>{{ __('insurance.payment.status') }}:</strong>
                                                    <span class="badge bg-warning">{{ strtoupper($order->status) }}</span>
                                                </p>
                                                <p class="mb-2"><strong>{{ __('insurance.payment.date') }}:</strong>
                                                    {{ $order->created_at->format('d.m.Y H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Payment Methods --}}
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="mb-3">
                                        <i class="fas fa-credit-card me-2"></i>
                                        {{ __('insurance.payment.select_payment_method') }}
                                    </h5>

                                    {{-- Click Payment Card --}}
                                    @if($clickUrl)
                                    <a href="{{ $clickUrl }}" target="_blank" class="text-decoration-none">
                                        <div class="card mb-3 payment-card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h5 class="mb-1">
                                                            <img src="{{ asset('images/tolovTizimi/click.svg') }}"
                                                                alt="Click" style="height: 50px;" class="me-2">
                                                        </h5>
                                                        <p class="text-muted mb-0 small">{{ __('insurance.payment.bank_cards_payment') }}</p>
                                                    </div>
                                                    <i class="fas fa-chevron-right text-muted"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    @endif

                                    {{-- Payme Payment Card --}}
                                    @if($paymeUrl)
                                    <a href="{{ $paymeUrl }}" target="_blank" class="text-decoration-none">
                                        <div class="card mb-3 payment-card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h5 class="mb-1">
                                                            <img src="{{ asset('images/tolovTizimi/payme.svg') }}"
                                                                alt="Payme" style="height: 50px;" class="me-2">
                                                        </h5>
                                                        <p class="text-muted mb-0 small">{{ __('insurance.payment.bank_cards_payment') }}</p>
                                                    </div>
                                                    <i class="fas fa-chevron-right text-muted"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    @endif
                                </div>

                                {{-- Order Summary --}}
                                <div class="col-md-4">
                                    <div class="card bg-light border-0 shadow-sm">
                                        <div class="card-body">
                                            <h6 class="card-title text-muted mb-3">{{ __('insurance.payment.payment_amount') }}</h6>
                                            <h2 class="text-primary mb-3">{{ number_format($order->amount, 0, '.', ' ') }}
                                                UZS</h2>
                                            <hr>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">{{ __('insurance.payment.insurance_premium') }}:</span>
                                                <span class="small">{{ number_format($order->amount, 0, '.', ' ') }}
                                                    UZS</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">{{ __('insurance.payment.commission') }}:</span>
                                                <span class="small text-success">0 UZS</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ __('insurance.payment.total') }}:</strong>
                                                <strong
                                                    class="text-primary">{{ number_format($order->amount, 0, '.', ' ') }}
                                                    UZS</strong>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Security Info --}}
                                    <div class="mt-3 text-center">
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-lock me-1"></i>
                                            {{ __('insurance.payment.secure_payment') }}
                                        </p>
                                        <p class="text-muted small mb-0">
                                            {{ __('insurance.payment.ssl_encrypted') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-start">
                                <a href="{{ route('osago.application.view', ['locale' => app()->getLocale()]) }}"
                                    class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>{{ __('insurance.payment.back') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <style>
        .payment-card {
            transition: all 0.3s ease;
            border: 2px solid #dee2e6;
        }

        .payment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .payment-card.border-primary {
            border-color: #0d6efd !important;
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
        }
    </style>
@endsection
