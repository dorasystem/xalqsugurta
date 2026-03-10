@extends('layouts.app')
@section('title', __('insurance.osgop.page_title'))

@section('content')
<section class="container-fluid product-page py-4">
    <div class="container">

        <x-insurence.multi-step-stepper :totalSteps="6" :currentStep="1" :labels="[
            __('messages.step_contract_info'),
            __('messages.step_insurant_type'),
            __('messages.step_organization'),
            __('messages.step_vehicle_info'),
            __('messages.step_policy_info'),
            __('messages.step_review'),
        ]" />

        <div class="row g-4 mt-1">

            <div class="col-lg-8">
                <x-insurence.error-block />

                <form action="{{ route('osgop.create.step1.store', ['locale' => getCurrentLocale()]) }}" method="POST" novalidate>
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
                                    <label for="start_date" class="form-label fw-semibold">
                                        {{ __('messages.start_date') }} <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        id="start_date"
                                        name="start_date"
                                        class="form-control @error('start_date') is-invalid @enderror"
                                        value="{{ old('start_date', $data['start_date'] ?? \Carbon\Carbon::today()->format('Y-m-d')) }}"
                                        min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                        required
                                    >
                                    @error('start_date')
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
                                    >
                                    <small class="text-muted" id="period_hint"></small>
                                </div>

                                {{-- Period selection --}}
                                <div class="col-12 mt-2">
                                    <label class="form-label fw-semibold d-block mb-2">
                                        {{ __('messages.insurance_period') }} <span class="text-danger">*</span>
                                    </label>
                                    @error('period_months')
                                        <div class="text-danger small mb-2">{{ $message }}</div>
                                    @enderror
                                    <div class="row g-2">
                                        @foreach ([3 => __('messages.period_3_months'), 6 => __('messages.period_6_months'), 12 => __('messages.period_12_months')] as $months => $label)
                                        <div class="col-4">
                                            <input type="radio" class="btn-check period-radio" name="period_months" id="period_{{ $months }}"
                                                value="{{ $months }}"
                                                {{ old('period_months', $data['period_months'] ?? '12') == $months ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary w-100 py-3 fw-semibold" for="period_{{ $months }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
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
                        <h5 class="fw-bold mb-3">{{ __('insurance.osgop.title') }}</h5>
                        <p class="mb-0 opacity-75 small">{{ __('insurance.osgop.description') }}</p>
                    </div>
                </div>
                @if(!empty($data))
                <div class="card shadow-sm mt-3">
                    <div class="card-header fw-semibold small">{{ __('messages.step_contract_info') }}</div>
                    <div class="card-body p-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ __('messages.start_date') }}</span>
                            <strong>{{ $data['start_date'] ?? '—' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ __('messages.end_date') }}</span>
                            <strong>{{ $data['end_date'] ?? '—' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('messages.period') }}</span>
                            <strong>{{ $data['period_months'] ?? '—' }} {{ __('messages.step') }}</strong>
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
    var startEl   = document.getElementById('start_date');
    var endEl     = document.getElementById('end_date_display');
    var hintEl    = document.getElementById('period_hint');
    var radios    = document.querySelectorAll('.period-radio');

    var periodLabels = { 3: '3 oy', 6: '6 oy', 12: '12 oy' };

    function getSelectedMonths() {
        var checked = document.querySelector('.period-radio:checked');
        return checked ? parseInt(checked.value) : 12;
    }

    function calcEnd() {
        if (!startEl.value) return;
        var months = getSelectedMonths();
        var d = new Date(startEl.value);
        d.setMonth(d.getMonth() + months);
        d.setDate(d.getDate() - 1);
        endEl.value = d.toISOString().split('T')[0];
        if (hintEl) hintEl.textContent = periodLabels[months] || '';
    }

    startEl.addEventListener('change', calcEnd);
    radios.forEach(function (r) { r.addEventListener('change', calcEnd); });
    calcEnd();
});
</script>
@endpush
