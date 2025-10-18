@extends('layouts.app')

@section('content')
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
                                        OSAGO Sug'urta To'lovi
                                    </h3>
                                    <p class="text-muted mb-0">Sug'urta to'lovini amalga oshiring</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Order Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-info-circle"></i>Buyurtma ma'lumotlari</h5>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>Buyurtma raqami:</strong> #{{ $order->id }}</p>
                                                <p class="mb-2"><strong>Mahsulot:</strong>
                                                    {{ strtoupper($order->product_name) }}</p>
                                                <p class="mb-2"><strong>Telefon:</strong> {{ $order->phone }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>Sug'urta ID:</strong> {{ $order->insurance_id }}
                                                </p>
                                                <p class="mb-2"><strong>Holat:</strong>
                                                    <span class="badge bg-warning">{{ strtoupper($order->status) }}</span>
                                                </p>
                                                <p class="mb-2"><strong>Sana:</strong>
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
                                        To'lov usulini tanlang
                                    </h5>

                                    {{-- Click Payment Card --}}
                                    <div class="card mb-3 payment-card" onclick="selectPayment('click')">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <input type="radio" name="payment_method" id="payment_click"
                                                    value="click" class="me-3">
                                                <label for="payment_click">
                                                    <div class="flex-grow-1">
                                                        <h5 class="mb-1">
                                                            <img src="{{ asset('images/tolovTizimi/click.svg') }}"
                                                                alt="Click" style="height: 50px;" class="me-2">
                                                        </h5>
                                                        <p class="text-muted mb-0 small">Bank kartalari orqali to'lash
                                                            (Uzcard,
                                                            Humo)</p>
                                                    </div>
                                                </label>
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
                                                <label for="payment_payme">
                                                    <div class="flex-grow-1">
                                                        <h5 class="mb-1">
                                                            <img src="{{ asset('images/tolovTizimi/payme.svg') }}"
                                                                alt="Payme" style="height: 50px;" class="me-2">
                                                        </h5>
                                                        <p class="text-muted mb-0 small">Bank kartalari orqali to'lash
                                                            (Uzcard,
                                                            Humo)</p>
                                                    </div>
                                                </label>
                                                <form id="form-payme" method="GET" action="{{ route('payment.payme') }}"
                                                    class="d-none" target="_blank">
                                                    <input type="hidden" name="id" value="{{ $order->id }}">
                                                </form>
                                                <form id="form-click" method="GET" action="{{ route('payment.click') }}"
                                                    class="d-none" target="_blank">
                                                    <input type="hidden" name="id" value="{{ $order->id }}">
                                                </form>
                                                <i class="fas fa-chevron-right text-muted"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning mt-3" id="payment-warning" style="display: none;">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Iltimos, to'lov usulini tanlang
                                    </div>
                                </div>

                                {{-- Order Summary --}}
                                <div class="col-md-4">
                                    <div class="card bg-light border-0 shadow-sm">
                                        <div class="card-body">
                                            <h6 class="card-title text-muted mb-3">To'lov summasi</h6>
                                            <h2 class="text-primary mb-3">{{ number_format($order->amount, 0, '.', ' ') }}
                                                UZS</h2>
                                            <hr>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Sug'urta mukofoti:</span>
                                                <span class="small">{{ number_format($order->amount, 0, '.', ' ') }}
                                                    UZS</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Komissiya:</span>
                                                <span class="small text-success">0 UZS</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <strong>Jami:</strong>
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
                                            Xavfsiz to'lov
                                        </p>
                                        <p class="text-muted small mb-0">
                                            To'lov SSL shifrlash orqali himoyalangan
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('osago.application', ['locale' => app()->getLocale()]) }}"
                                    class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Orqaga
                                </a>
                                <button type="button" id="payBtn" class="btn btn-success btn-lg"
                                    onclick="processPayment()">
                                    <i class="fas fa-credit-card me-2"></i>To'lovni amalga oshirish
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            const orderId = {{ $order->id }};

            console.log(orderId);
            let selectedPayment = null;

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

                const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;

                if (!paymentMethod) {
                    alert('Iltimos, to‘lov usulini tanlang!');
                    return;
                }

                if (paymentMethod === 'payme') {
                    document.getElementById('form-payme').submit();
                } else if (paymentMethod === 'click') {
                    document.getElementById('click_form').submit();
                } else {
                    alert('Boshqa to‘lov usuli tanlandi');
                }
                // Update button state
                const payBtn = document.getElementById('payBtn');
                payBtn.disabled = true;
                payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>To\'lov amalga oshirilmoqda...';

                // Here you would typically make an API call to initiate payment
                // For now, we'll just show a message
                console.log('Processing payment with:', selectedPayment, 'for order:', orderId);

                // TODO: Implement actual payment gateway integration
                setTimeout(() => {
                    alert(
                        `${selectedPayment.toUpperCase()} to'lov tizimiga ulanilmoqda...\nBuyurtma raqami: #${orderId}`
                    );
                    payBtn.disabled = false;
                    payBtn.innerHTML = '<i class="fas fa-credit-card me-2"></i>To\'lovni amalga oshirish';
                }, 2000);
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
    @endpush

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
