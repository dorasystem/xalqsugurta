@extends('layouts.app')
@section('title', __('insurance.osgop.page_title'))

@section('content')
<section class="container-fluid product-page py-4">
    <div class="container">

        <x-insurence.multi-step-stepper :totalSteps="6" :currentStep="2" :labels="[
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

                <form action="{{ route('osgop.create.step2.store', ['locale' => getCurrentLocale()]) }}" method="POST" id="step2-form">
                    @csrf

                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h4 class="card-title mb-0 fs-5">
                                <span class="badge bg-primary me-2">2</span>
                                {{ __('messages.step_insurant_type') }}
                            </h4>
                        </div>
                        <div class="card-body p-4">

                            @error('insurant_type')
                                <div class="alert alert-danger py-2 small mb-3">{{ $message }}</div>
                            @enderror

                            <div class="row g-3">

                                {{-- Organization --}}
                                <div class="col-sm-6">
                                    <input type="radio" class="btn-check" name="insurant_type" id="type_organization"
                                        value="organization"
                                        {{ old('insurant_type', $data['insurant_type'] ?? '') === 'organization' ? 'checked' : '' }}>
                                    <label for="type_organization" class="type-card card h-100 border-2 p-0 w-100" style="cursor:pointer;">
                                        <div class="card-body text-center py-4 px-3">
                                            <div class="mb-3">
                                                <span class="type-icon d-inline-flex align-items-center justify-content-center rounded-circle bg-light"
                                                    style="width:64px;height:64px;">
                                                    <i class="bi bi-building fs-2 text-primary"></i>
                                                </span>
                                            </div>
                                            <h6 class="fw-bold mb-1">{{ __('messages.organization') }}</h6>
                                            <p class="text-muted small mb-0">{{ __('messages.inn') }}</p>
                                        </div>
                                    </label>
                                </div>

                                {{-- Person --}}
                                <div class="col-sm-6">
                                    <input type="radio" class="btn-check" name="insurant_type" id="type_person"
                                        value="person"
                                        {{ old('insurant_type', $data['insurant_type'] ?? '') === 'person' ? 'checked' : '' }}>
                                    <label for="type_person" class="type-card card h-100 border-2 p-0 w-100" style="cursor:pointer;">
                                        <div class="card-body text-center py-4 px-3">
                                            <div class="mb-3">
                                                <span class="type-icon d-inline-flex align-items-center justify-content-center rounded-circle bg-light"
                                                    style="width:64px;height:64px;">
                                                    <i class="bi bi-person fs-2 text-primary"></i>
                                                </span>
                                            </div>
                                            <h6 class="fw-bold mb-1">{{ __('messages.person') }}</h6>
                                            <p class="text-muted small mb-0">PINFL / {{ __('insurance.passport.series') }}</p>
                                        </div>
                                    </label>
                                </div>

                            </div>
                        </div>

                        <div class="card-footer bg-transparent d-flex justify-content-between gap-2 py-3">
                            <a href="{{ route('osgop.create.step1', ['locale' => getCurrentLocale()]) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> {{ __('messages.prev_step') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4" id="step2-submit" disabled>
                                {{ __('messages.next_step') }} <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                @php $s1 = session('osgop.step1') @endphp
                @if($s1)
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold small">{{ __('messages.step_contract_info') }}</div>
                    <div class="card-body p-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ __('messages.start_date') }}</span>
                            <strong>{{ $s1['start_date'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ __('messages.end_date') }}</span>
                            <strong>{{ $s1['end_date'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('messages.insurance_period') }}</span>
                            <strong>{{ $s1['period_months'] }} {{ __('messages.step') }}</strong>
                        </div>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .btn-check:checked + .type-card {
        border-color: var(--bs-primary) !important;
        background-color: #f0f6ff;
    }
    .btn-check:checked + .type-card .type-icon {
        background-color: var(--bs-primary) !important;
    }
    .btn-check:checked + .type-card .type-icon i {
        color: #fff !important;
    }
    .type-card {
        transition: border-color .15s, background-color .15s;
    }
    .type-card:hover {
        border-color: #b0c8f5 !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var radios = document.querySelectorAll('input[name="insurant_type"]');
    var submitBtn = document.getElementById('step2-submit');

    function checkSelected() {
        var checked = document.querySelector('input[name="insurant_type"]:checked');
        submitBtn.disabled = !checked;
    }

    radios.forEach(function (r) { r.addEventListener('change', checkSelected); });
    checkSelected();
});
</script>
@endpush
