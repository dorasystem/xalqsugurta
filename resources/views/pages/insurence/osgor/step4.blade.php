@extends('layouts.app')
@section('title', __('insurance.osgor.page_title'))

@section('content')
<section class="container-fluid product-page py-4">
    <div class="container">

        <x-insurence.multi-step-stepper :totalSteps="4" :currentStep="4" :labels="[
            __('messages.step_contract_info'),
            __('messages.step_organization'),
            __('messages.step_policy_info'),
            __('messages.step_review'),
        ]" />

        <div class="row g-4 mt-1">
            <div class="col-lg-8">
                <x-insurence.error-block />

                {{-- Review Cards --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fs-6"><i class="bi bi-calendar-check me-2 text-primary"></i>{{ __('messages.step_contract_info') }}</h5>
                        <a href="{{ route('osgor.step1', ['locale' => getCurrentLocale()]) }}" class="btn btn-sm btn-outline-secondary">{{ __('messages.edit') }}</a>
                    </div>
                    <div class="card-body p-3 small">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <span class="text-muted d-block">{{ __('messages.start_date') }}</span>
                                <strong>{{ $step1['payment_start_date'] ?? '—' }}</strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block">{{ __('messages.end_date') }}</span>
                                <strong>{{ $step1['payment_end_date'] ?? '—' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fs-6"><i class="bi bi-building me-2 text-primary"></i>{{ __('messages.step_organization') }}</h5>
                        <a href="{{ route('osgor.step2', ['locale' => getCurrentLocale()]) }}" class="btn btn-sm btn-outline-secondary">{{ __('messages.edit') }}</a>
                    </div>
                    @php $org = $step2['organization'] ?? [] @endphp
                    <div class="card-body p-3 small">
                        <div class="row g-2">
                            <div class="col-sm-6"><span class="text-muted d-block">{{ __('messages.inn') }}</span><strong>{{ $org['inn'] ?? '—' }}</strong></div>
                            <div class="col-sm-6"><span class="text-muted d-block">{{ __('messages.phone_number') }}</span><strong>{{ $org['phone'] ?? '—' }}</strong></div>
                            <div class="col-12"><span class="text-muted d-block">{{ __('messages.organization_name') }}</span><strong>{{ $org['name'] ?? '—' }}</strong></div>
                            <div class="col-12"><span class="text-muted d-block">{{ __('messages.address') }}</span><strong>{{ $org['address'] ?? '—' }}</strong></div>
                            <div class="col-sm-6"><span class="text-muted d-block">{{ __('messages.oked') }}</span><strong>{{ $org['oked'] ?? '—' }}</strong></div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fs-6"><i class="bi bi-shield-check me-2 text-primary"></i>{{ __('messages.step_policy_info') }}</h5>
                        <a href="{{ route('osgor.step3', ['locale' => getCurrentLocale()]) }}" class="btn btn-sm btn-outline-secondary">{{ __('messages.edit') }}</a>
                    </div>
                    <div class="card-body p-3 small">
                        @php
                            $insuranceAmount = (float)($step3['insurance_amount'] ?? 0);
                            $premium         = $insuranceAmount * 0.05710;
                        @endphp
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <span class="text-muted d-block">FOT ({{ __('messages.insurance_amount') }})</span>
                                <strong>{{ number_format($insuranceAmount, 2, '.', ' ') }} UZS</strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block">{{ __('messages.policy_price') }}</span>
                                <strong class="text-success">{{ number_format($premium, 2, '.', ' ') }} UZS</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Final submit form: all session data as hidden inputs → existing osgor.application route --}}
                <form action="{{ route('osgor.application', ['locale' => getCurrentLocale()]) }}" method="POST" id="osgor-final-form">
                    @csrf

                    {{-- Step 1 --}}
                    <input type="hidden" name="payment_start_date" value="{{ $step1['payment_start_date'] ?? '' }}">
                    <input type="hidden" name="payment_end_date"   value="{{ $step1['payment_end_date'] ?? '' }}">

                    {{-- Step 2: Organization --}}
                    @foreach(($step2['organization'] ?? []) as $key => $value)
                        <input type="hidden" name="organization[{{ $key }}]" value="{{ $value }}">
                    @endforeach

                    {{-- Step 3: Policy --}}
                    <input type="hidden" name="insurance_amount" value="{{ $step3['insurance_amount'] ?? 0 }}">

                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <a href="{{ route('osgor.step3', ['locale' => getCurrentLocale()]) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> {{ __('messages.prev_step') }}
                        </a>
                        <button type="submit" class="btn btn-success btn-lg px-5" id="submit-btn">
                            <span class="btn-text">
                                <i class="bi bi-check-circle me-2"></i>{{ __('messages.proceed_to_payment') }}
                            </span>
                            <span class="btn-loading d-none">
                                <span class="spinner-border spinner-border-sm me-2"></span>{{ __('messages.loading') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                @php
                    $insuranceAmount = (float)($step3['insurance_amount'] ?? 0);
                    $premium         = $insuranceAmount * 0.05710;
                @endphp
                <div class="card shadow-sm border-success">
                    <div class="card-header bg-success text-white fw-semibold">{{ __('messages.policy_price') }}</div>
                    <div class="card-body p-4 text-center">
                        <div class="fs-2 fw-bold text-success">
                            {{ number_format($premium, 2, '.', ' ') }}
                        </div>
                        <div class="text-muted">UZS</div>
                        <hr>
                        <div class="small text-muted">
                            FOT: {{ number_format($insuranceAmount, 2, '.', ' ') }} UZS
                        </div>
                        <div class="small text-muted">
                            {{ __('messages.insurance_rate') }}: 5.71%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('osgor-final-form')?.addEventListener('submit', function () {
    var btn = document.getElementById('submit-btn');
    if (btn) {
        btn.querySelector('.btn-text').classList.add('d-none');
        btn.querySelector('.btn-loading').classList.remove('d-none');
        btn.disabled = true;
    }
});
</script>
@endpush
