@extends('layouts.app')
@section('title', __('insurance.osgor.page_title'))

@section('content')
<section class="container-fluid product-page py-4">
    <div class="container">

        <x-insurence.multi-step-stepper :totalSteps="4" :currentStep="1" :labels="[
            __('messages.step_contract_info'),
            __('messages.step_organization'),
            __('messages.step_policy_info'),
            __('messages.step_review'),
        ]" />

        <div class="row g-4 mt-1">

            <div class="col-lg-8">
                <x-insurence.error-block />

                <form action="{{ route('osgor.step1.store', ['locale' => getCurrentLocale()]) }}" method="POST" novalidate>
                    @csrf
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h4 class="card-title mb-0 fs-5">
                                <span class="badge bg-primary me-2">1</span>
                                {{ __('messages.step_contract_info') }}
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">

                                {{-- Start Date --}}
                                <div class="col-md-6">
                                    <label for="payment_start_date" class="form-label fw-semibold">
                                        {{ __('messages.start_date') }} <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        id="payment_start_date"
                                        name="payment_start_date"
                                        class="form-control @error('payment_start_date') is-invalid @enderror"
                                        value="{{ old('payment_start_date', $data['payment_start_date'] ?? \Carbon\Carbon::today()->format('Y-m-d')) }}"
                                        min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                        required
                                    >
                                    @error('payment_start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- End Date (auto-calculated, readonly) --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.end_date') }}</label>
                                    <input
                                        type="date"
                                        id="end_date_display"
                                        class="form-control bg-light"
                                        readonly
                                        value="{{ $data['payment_end_date'] ?? \Carbon\Carbon::today()->addYear()->subDay()->format('Y-m-d') }}"
                                    >
                                    <small class="text-muted">{{ __('messages.period_12_months') }}</small>
                                </div>

                            </div>
                        </div>
                        <div class="card-footer bg-transparent d-flex justify-content-end gap-2 py-3">
                            <button type="submit" class="btn btn-primary px-4">
                                {{ __('messages.next_step') }} <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 bg-primary text-white">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">{{ __('insurance.osgor.title') }}</h5>
                        <p class="mb-0 opacity-75 small">{{ __('insurance.osgor.description') }}</p>
                    </div>
                </div>
                @if(!empty($data))
                <div class="card shadow-sm mt-3">
                    <div class="card-header fw-semibold small">{{ __('messages.step_contract_info') }}</div>
                    <div class="card-body p-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ __('messages.start_date') }}</span>
                            <strong>{{ $data['payment_start_date'] ?? '—' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('messages.end_date') }}</span>
                            <strong>{{ $data['payment_end_date'] ?? '—' }}</strong>
                        </div>
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
    var startEl = document.getElementById('payment_start_date');
    var endEl   = document.getElementById('end_date_display');

    function calcEnd() {
        if (!startEl.value) return;
        var d = new Date(startEl.value);
        d.setFullYear(d.getFullYear() + 1);
        d.setDate(d.getDate() - 1);
        endEl.value = d.toISOString().split('T')[0];
    }

    startEl.addEventListener('change', calcEnd);
    calcEnd();
});
</script>
@endpush
