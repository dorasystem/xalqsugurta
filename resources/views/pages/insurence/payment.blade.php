@extends('layouts.app')
@section('title', __('insurance.payment.page_title'))
@section('content')
    <x-insurence.steps :activeStep="3" />
    <section class="container-fluid product-page payment-page py-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="mb-3">@lang('payment.policy_details')</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2">@lang('payment.policy_number'): <strong>#{{ $order->id ?? 'N/A' }}</strong></p>
                                    <p class="mb-0">@lang('payment.phone'): <strong>+{{ $order->phone ?? 'N/A' }}</strong></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2">@lang('payment.contact_id'): <strong>

                                            {{ $order->polic_id_number }}</strong></p>
                                    @php
                                        // Sanalarni olish (Order model maydonlaridan foydalanish)
                                        $rawStartDate =
                                            $order->contractStartDate ??
                                            data_get($order, 'insurances_data.details.startDate');
                                        $rawEndDate =
                                            $order->contractEndDate ??
                                            data_get($order, 'insurances_data.details.endDate');

                                        // dd.MM.yyyy formatga o'zgartirish
$startDate = 'N/A';
$endDate = 'N/A';

if ($rawStartDate) {
    try {
        $startDate = \Carbon\Carbon::parse($rawStartDate)->format('d.m.Y');
    } catch (\Exception $e) {
        $startDate = $rawStartDate;
    }
}

if ($rawEndDate) {
    try {
        $endDate = \Carbon\Carbon::parse($rawEndDate)->format('d.m.Y');
                                            } catch (\Exception $e) {
                                                $endDate = $rawEndDate;
                                            }
                                        }
                                    @endphp
                                    <p class="mb-2">@lang('payment.insurance_period'): <strong>{{ $startDate }} -
                                            {{ $endDate }}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card mt-3">
                        <div class="card-body">
                            <h3 class="mb-4">@lang('payment.select_payment_method')</h3>

                            <div class="row g-3">
                                <!-- Plastik karta - Coming Soon -->
                                <div class="col-12 col-md-4">
                                    <div class="payment-card disabled position-relative">
                                        <span
                                            class="badge bg-success position-absolute top-0 end-0 m-2">@lang('payment.coming_soon')</span>
                                        <div class="payment-icon">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <rect x="2" y="5" width="20" height="14" rx="2"
                                                    stroke="#5B4CCC" stroke-width="2" />
                                                <path d="M2 10H22" stroke="#5B4CCC" stroke-width="2" />
                                                <rect x="5" y="14" width="6" height="2" fill="#5B4CCC" />
                                            </svg>
                                        </div>
                                        <h5 class="payment-title">@lang('payment.plastic_card')</h5>
                                        <p class="payment-subtitle text-muted">@lang('payment.cards_supported')</p>
                                    </div>
                                </div>

                                <!-- Click Payment -->
                                <div class="col-12 col-md-4">
                                    <form method="GET" action="{{ route('payment.click') }}" target="_blank"
                                        class="h-100">
                                        <input type="hidden" name="id" value="{{ $order->id ?? '' }}">
                                        <button type="submit" class="payment-card clickable w-100 h-100">
                                            <img src="{{ asset('images/tolovTizimi/click.svg') }}" alt="Click">
                                        </button>
                                    </form>
                                </div>

                                <!-- Payme Payment -->
                                <div class="col-12 col-md-4">
                                    <form method="GET" action="{{ route('payment.payme') }}" target="_blank"
                                        class="h-100">
                                        <input type="hidden" name="id" value="{{ $order->id ?? '' }}">
                                        <button type="submit" class="payment-card clickable w-100 h-100">
                                            <img src="{{ asset('images/tolovTizimi/payme.svg') }}" alt="Payme">
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <a href="{{ url()->previous() }}" class="btn btn-secondary mt-4">@lang('payment.back')</a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">@lang('payment.payment_amount')</h6>
                            <h3 class="mb-0">{{ number_format($order->amount ?? 0, 0, '.', ' ') }} UZS</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
