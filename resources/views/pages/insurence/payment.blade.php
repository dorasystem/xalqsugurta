@extends('layouts.app')

@section('content')
    @php
        // Determine product title based on insurance type
        $productTitles = [
            'osago' => __('messages.osago') . ' ' . __('messages.insurance') . ' ' . __('messages.payment'),
            'accident' => __('messages.accident_insurance_payment'),
            'property' => __('messages.property_insurance_payment'),
            'unknown' => __('messages.insurance') . ' ' . __('messages.payment'),
        ];

        $backRoutes = [
            'osago' => 'osago.application',
            'accident' => 'accident.application.view',
            'property' => 'property.application.view',
            'unknown' => 'home',
        ];

        $productTitle = $productTitles[$insuranceType] ?? $productTitles['unknown'];
        $backRoute = $backRoutes[$insuranceType] ?? $backRoutes['unknown'];
    @endphp

    <section class="container-fluid product-page py-4">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="card-title mb-0">
                                        <i class="fas fa-credit-card me-2"></i>
                                        {{ $productTitle }}
                                    </h3>
                                    <p class="text-muted mb-0">{{ __('messages.complete_insurance_payment') }}</p>
                                </div>
                                {{-- Language Switcher --}}
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                        id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-globe me-2"></i>
                                        {{ strtoupper(app()->getLocale()) }}
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('payment.show', ['locale' => 'uz', 'orderId' => $order->id]) }}">
                                                <img src="{{ asset('assets/images/flags/uz.png') }}" alt="UZ"
                                                    class="me-2" style="width: 20px;"> O'zbek
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('payment.show', ['locale' => 'ru', 'orderId' => $order->id]) }}">
                                                <img src="{{ asset('assets/images/flags/ru.png') }}" alt="RU"
                                                    class="me-2" style="width: 20px;"> Русский
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('payment.show', ['locale' => 'en', 'orderId' => $order->id]) }}">
                                                <img src="{{ asset('assets/images/flags/en.png') }}" alt="EN"
                                                    class="me-2" style="width: 20px;"> English
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Order Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-info-circle me-2"></i>{{ __('messages.order_information') }}
                                        </h5>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>{{ __('messages.order_number') }}:</strong>
                                                    #{{ $order->id }}</p>
                                                <p class="mb-2"><strong>{{ __('messages.product') }}:</strong>
                                                    {{ $order->product_name }}</p>
                                                <p class="mb-2"><strong>{{ __('messages.phone') }}:</strong>
                                                    {{ $order->phone }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>{{ __('messages.insurance_id') }}:</strong>
                                                    {{ $order->insurance_id }}
                                                </p>
                                                <p class="mb-2"><strong>{{ __('messages.status') }}:</strong>
                                                    <span class="badge bg-warning">{{ strtoupper($order->status) }}</span>
                                                </p>
                                                <p class="mb-2"><strong>{{ __('messages.date') }}:</strong>
                                                    {{ $order->created_at->format('d.m.Y H:i') }}</p>
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
                                        {{ __('messages.select_payment_method') }}
                                    </h5>

                                    {{-- Click Payment Card --}}
                                    <div class="card mb-3 payment-card" onclick="selectPayment('click')">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <input type="radio" name="payment_method" id="payment_click"
                                                    value="click" class="me-3">
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1">
                                                        <img src="https://click.uz/click/images/logo.svg" alt="Click"
                                                            style="height: 30px;" class="me-2">
                                                        Click
                                                    </h5>
                                                    <p class="text-muted mb-0 small">{{ __('messages.pay_with_cards') }}
                                                    </p>
                                                </div>
                                                <i class="fas fa-chevron-right text-muted"></i>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Payme Payment Card --}}
                                    <div class="card mb-3 payment-card" onclick="selectPayment('payme')">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <input type="radio" name="payment_method" id="payment_payme"
                                                    value="payme" class="me-3">
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1">
                                                        <img src="https://payme.uz/assets/img/logo.svg" alt="Payme"
                                                            style="height: 30px;" class="me-2">
                                                        Payme
                                                    </h5>
                                                    <p class="text-muted mb-0 small">{{ __('messages.pay_with_cards') }}
                                                    </p>
                                                </div>
                                                <i class="fas fa-chevron-right text-muted"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning mt-3" id="payment-warning" style="display: none;">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        {{ __('messages.please_select_payment_method') }}
                                    </div>
                                </div>

                                {{-- Order Summary --}}
                                <div class="col-md-4">
                                    <div class="card bg-light border-0 shadow-sm">
                                        <div class="card-body">
                                            <h6 class="card-title text-muted mb-3">{{ __('messages.payment_amount') }}</h6>
                                            <h2 class="text-primary mb-3">{{ number_format($order->amount, 0, '.', ' ') }}
                                                UZS</h2>
                                            <hr>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span
                                                    class="text-muted small">{{ __('messages.insurance_premium') }}:</span>
                                                <span class="small">{{ number_format($order->amount, 0, '.', ' ') }}
                                                    {{ __('messages.currency') }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">{{ __('messages.commission') }}:</span>
                                                <span class="small text-success">0 {{ __('messages.currency') }}</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ __('messages.total') }}:</strong>
                                                <strong
                                                    class="text-primary">{{ number_format($order->amount, 0, '.', ' ') }}
                                                    {{ __('messages.currency') }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Security Info --}}
                                    <div class="mt-3 text-center">
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-lock me-1"></i>
                                            {{ __('messages.secure_payment') }}
                                        </p>
                                        <p class="text-muted small mb-0">
                                            {{ __('messages.payment_ssl_protected') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route($backRoute, ['locale' => app()->getLocale()]) }}"
                                    class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>{{ __('messages.back') }}
                                </a>
                                <button type="button" id="payBtn" class="btn btn-success btn-lg"
                                    onclick="processPayment()">
                                    <i class="fas fa-credit-card me-2"></i>{{ __('messages.proceed_payment') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- JavaScript for Payment Selection --}}
    <script>
        const orderId = {{ $order->id }};
        const insuranceType = '{{ $insuranceType }}';
        let selectedPayment = null;

        // Translation strings
        const translations = {
            processing: '{{ __('messages.payment_processing') }}',
            proceedPayment: '{{ __('messages.proceed_payment') }}',
            connectingToGateway: '{{ __('messages.connecting_to_payment_gateway') }}',
            orderNumber: '{{ __('messages.order_number') }}',
            error: '{{ __('errors.general_error') }}',
            paymentError: '{{ __('errors.insurance.payment.processing_failed') }}'
        };

        function selectPayment(method) {
            selectedPayment = method;

            // Remove previous selections
            document.querySelectorAll('.payment-card').forEach(card => {
                card.classList.remove('border-primary', 'bg-light');
            });

            // Uncheck all radios
            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.checked = false;
            });

            // Select current payment
            const selectedCard = document.getElementById('payment_' + method).closest('.payment-card');
            selectedCard.classList.add('border-primary', 'bg-light');
            document.getElementById('payment_' + method).checked = true;

            // Hide warning
            document.getElementById('payment-warning').style.display = 'none';
        }

        function processPayment() {
            if (!selectedPayment) {
                document.getElementById('payment-warning').style.display = 'block';
                return;
            }

            // Update button state
            const payBtn = document.getElementById('payBtn');
            payBtn.disabled = true;
            payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + translations.processing;

            // Make API call to process payment
            const locale = '{{ app()->getLocale() }}';
            const processUrl = `/${locale}/payment/${orderId}/process`;

            fetch(processUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        payment_method: selectedPayment,
                        insurance_type: insuranceType
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // TODO: Redirect to payment gateway
                        alert(
                            `${selectedPayment.toUpperCase()} ${translations.connectingToGateway}\n${translations.orderNumber}: #${orderId}`
                        );
                    } else {
                        alert(translations.error + ': ' + (data.message || translations.paymentError));
                    }
                    payBtn.disabled = false;
                    payBtn.innerHTML = '<i class="fas fa-credit-card me-2"></i>' + translations.proceedPayment;
                })
                .catch(error => {
                    console.error('Payment error:', error);
                    alert(translations.paymentError);
                    payBtn.disabled = false;
                    payBtn.innerHTML = '<i class="fas fa-credit-card me-2"></i>' + translations.proceedPayment;
                });
        }

        // Add hover effect to payment cards
        document.querySelectorAll('.payment-card').forEach(card => {
            card.style.cursor = 'pointer';
            card.addEventListener('mouseenter', function() {
                if (!this.classList.contains('border-primary')) {
                    this.style.borderColor = '#0d6efd';
                }
            });
            card.addEventListener('mouseleave', function() {
                if (!this.classList.contains('border-primary')) {
                    this.style.borderColor = '';
                }
            });
        });
    </script>

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
