@extends('layouts.app')
@section('title', __('insurance.osgor.page_title'))

@section('content')
<section class="container-fluid product-page py-4">
    <div class="container">

        <x-insurence.multi-step-stepper :totalSteps="4" :currentStep="3" :labels="[
            __('messages.step_contract_info'),
            __('messages.step_organization'),
            __('messages.step_policy_info'),
            __('messages.step_review'),
        ]" />

        <div class="row g-4 mt-1">
            <div class="col-lg-8">
                <x-insurence.error-block />

                <form action="{{ route('osgor.step3.store', ['locale' => getCurrentLocale()]) }}" method="POST" novalidate>
                    @csrf

                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h4 class="card-title mb-0 fs-5">
                                <span class="badge bg-primary me-2">3</span>
                                {{ __('messages.step_policy_info') }}
                            </h4>
                        </div>
                        <div class="card-body p-4">

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    {{ __('messages.insurance_amount') }} <span class="text-danger">*</span>
                                    <span class="text-muted fw-normal small">(FOT — {{ __('messages.fond_oplaty_truda') }})</span>
                                </label>

                                {{-- Display value --}}
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span id="amount_display" class="fs-4 fw-bold text-primary">
                                        UZS {{ number_format($insuranceAmount['min'], 0, '.', ' ') }}
                                    </span>
                                    <span class="text-muted small">
                                        max: UZS {{ number_format($insuranceAmount['max'], 0, '.', ' ') }}
                                    </span>
                                </div>

                                {{-- Range slider --}}
                                <input
                                    type="range"
                                    id="insurance_range"
                                    class="form-range w-100"
                                    min="{{ $insuranceAmount['min'] }}"
                                    max="{{ $insuranceAmount['max'] }}"
                                    step="10000000"
                                    value="{{ old('insurance_amount', $data['insurance_amount'] ?? $insuranceAmount['min']) }}"
                                >

                                {{-- Hidden field submitted --}}
                                <input
                                    type="hidden"
                                    id="insurance_amount"
                                    name="insurance_amount"
                                    value="{{ old('insurance_amount', $data['insurance_amount'] ?? $insuranceAmount['min']) }}"
                                >

                                @error('insurance_amount')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Calculated premium preview --}}
                            <div class="alert alert-info py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>{{ __('messages.policy_price') }} <small class="text-muted">({{ __('messages.insurance_rate') }}: 5.71%)</small></span>
                                    <strong class="fs-5" id="premium_preview">—</strong>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer bg-transparent d-flex justify-content-between gap-2 py-3">
                            <a href="{{ route('osgor.step2', ['locale' => getCurrentLocale()]) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> {{ __('messages.prev_step') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                {{ __('messages.next_step') }} <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                @php $s1 = session('osgor.step1'); $s2 = session('osgor.step2'); @endphp
                @if($s1)
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-semibold small">{{ __('messages.step_contract_info') }}</div>
                    <div class="card-body p-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ __('messages.start_date') }}</span>
                            <strong>{{ $s1['payment_start_date'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('messages.end_date') }}</span>
                            <strong>{{ $s1['payment_end_date'] }}</strong>
                        </div>
                    </div>
                </div>
                @endif
                @if($s2)
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold small">{{ __('messages.step_organization') }}</div>
                    <div class="card-body p-3 small">
                        <div class="text-muted">{{ __('messages.inn') }}: {{ $s2['organization']['inn'] ?? '—' }}</div>
                        <strong>{{ $s2['organization']['name'] ?? '—' }}</strong>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var rangeEl   = document.getElementById('insurance_range');
    var hiddenEl  = document.getElementById('insurance_amount');
    var dispEl    = document.getElementById('amount_display');
    var premEl    = document.getElementById('premium_preview');
    var RATE      = 0.05710;

    function fmt(n) { return 'UZS ' + Number(n).toLocaleString('uz-UZ').replace(/,/g, ' '); }

    function update() {
        var val  = parseFloat(rangeEl.value);
        hiddenEl.value   = val;
        dispEl.textContent = fmt(val);
        premEl.textContent = fmt((val * RATE).toFixed(2));
    }

    rangeEl.addEventListener('input', update);
    update();
});
</script>
@endpush
