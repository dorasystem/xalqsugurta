@extends('layouts.app')
@section('title', __('insurance.payment.page_title'))
@section('content')
    <x-insurence.steps :activeStep="3" />
    <section class="container-fluid product-page py-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Buyurtma ma'lumotlari</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Buyurtma raqami:</strong> #{{ $order->id }}</p>
                                    <p class="mb-2"><strong>Mahsulot:</strong> {{ strtoupper($order->product_name) }}</p>
                                    <p class="mb-0"><strong>Telefon:</strong> {{ $order->phone }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Sug'urta ID:</strong> {{ $order->insurance_id }}</p>
                                    <p class="mb-2"><strong>Holat:</strong> {{ strtoupper($order->status) }}</p>
                                    <p class="mb-0"><strong>Sana:</strong>
                                        {{ optional($order->created_at)->format('d.m.Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="mb-3">To'lov usulini tanlang</h5>

                            <form method="GET" action="{{ route('payment.click') }}" class="mb-2" target="_blank">
                                <input type="hidden" name="id" value="{{ $order->id }}">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <img src="{{ asset('images/tolovTizimi/click.svg') }}" alt="Click"
                                        style="height: 26px;" class="me-2">
                                    Click orqali to'lash
                                </button>
                            </form>

                            <form method="GET" action="{{ route('payment.payme') }}" target="_blank">
                                <input type="hidden" name="id" value="{{ $order->id }}">
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <img src="{{ asset('images/tolovTizimi/payme.svg') }}" alt="Payme"
                                        style="height: 26px;" class="me-2">
                                    Payme orqali to'lash
                                </button>
                            </form>

                            <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Orqaga</a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">To'lov summasi</h6>
                            <h3 class="mb-0">{{ number_format($order->amount, 0, '.', ' ') }} UZS</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
