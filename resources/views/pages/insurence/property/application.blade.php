@extends('layouts.app')
@section('title', __('insurance.property.page_title'))

@section('content')

<section class="py-5 bg-gray-50" style="min-height: 100vh;">
    <div class="container">

    <x-insurence.page-header
        icon="bi-file-earmark-check"
        :title="__('insurance.property.page_title')"
        :subtitle="__('insurance.confirmation.confirm_data')"
    />

    @if (session('error') || $errors->any())
        <div class="alert alert-danger mb-4">
            @if (session('error'))
                <div>{{ session('error') }}</div>
            @endif
            @foreach ($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    @endif


    <div class="row g-4">

            {{-- ── LEFT COLUMN ─────────────────────────────────────── --}}
            <div class="col-lg-8">

                {{-- Property Information --}}
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-uppercase text-muted mb-3 small letter-spacing-1">
                            <i class="fas fa-home me-2 text-primary"></i>{{ __('messages.property_info') }}
                        </h6>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('messages.cadaster_number') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['propertyData']['cadasterNumber'] ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('messages.address') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['propertyData']['address'] ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('messages.area') }} (m²)</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['propertyData']['objectArea'] ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('messages.property_cost') }}</p>
                                <p class="fw-semibold mb-0">
                                    {{ $applicationData['propertyData']['cost'] ? number_format($applicationData['propertyData']['cost']) . ' UZS' : '—' }}
                                </p>
                            </div>
                            @if (!empty($applicationData['propertyData']['tipText']))
                            <div class="col-sm-12">
                                <p class="text-muted small mb-0">{{ __('messages.property_object_type') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['propertyData']['tipText'] }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Applicant Information --}}
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-uppercase text-muted mb-3 small letter-spacing-1">
                            <i class="fas fa-user me-2 text-primary"></i>{{ __('messages.applicant_info_title') }}
                        </h6>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('insurance.person.last_name') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['applicantData']['lastName'] ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('insurance.person.first_name') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['applicantData']['firstName'] ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('insurance.person.middle_name') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['applicantData']['middleName'] ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('insurance.person.telephone_number') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['applicantData']['phoneNumber'] ?? '—' }}</p>
                            </div>
                            @if (!empty($applicationData['applicantData']['passportSeries']))
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('messages.passport_series') }} / {{ __('messages.passport_number') }}</p>
                                <p class="fw-semibold mb-0">
                                    {{ $applicationData['applicantData']['passportSeries'] }}
                                    {{ $applicationData['applicantData']['passportNumber'] }}
                                </p>
                            </div>
                            @endif
                            @if (!empty($applicationData['applicantData']['birthDate']))
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('messages.birth_date') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['applicantData']['birthDate'] }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Owner Information --}}
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-uppercase text-muted mb-3 small letter-spacing-1">
                            <i class="fas fa-user-check me-2 text-primary"></i>{{ __('messages.property_owner') }}
                        </h6>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('insurance.person.last_name') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['ownerData']['lastName'] ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('insurance.person.first_name') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['ownerData']['firstName'] ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('insurance.person.middle_name') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['ownerData']['middleName'] ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('insurance.person.telephone_number') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['ownerData']['phoneNumber'] ?? '—' }}</p>
                            </div>
                            @if (!empty($applicationData['ownerData']['passportSeries']))
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('messages.passport_series') }} / {{ __('messages.passport_number') }}</p>
                                <p class="fw-semibold mb-0">
                                    {{ $applicationData['ownerData']['passportSeries'] }}
                                    {{ $applicationData['ownerData']['passportNumber'] }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Insurance Details --}}
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-uppercase text-muted mb-3 small letter-spacing-1">
                            <i class="fas fa-shield-alt me-2 text-primary"></i>{{ __('messages.insurance_details_title') }}
                        </h6>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('messages.insurance_sum') }}</p>
                                <p class="fw-bold fs-5 text-primary mb-0">
                                    {{ number_format($applicationData['insuranceAmount'] ?? 0) }} UZS
                                </p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('messages.insurance_premium') }} (0.2%)</p>
                                <p class="fw-bold fs-5 text-success mb-0">
                                    {{ number_format($applicationData['insurancePremium'] ?? 0) }} UZS
                                </p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('messages.start_date') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['paymentStartDate'] ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0">{{ __('messages.end_date') }}</p>
                                <p class="fw-semibold mb-0">{{ $applicationData['paymentEndDate'] ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-between gap-3">
                    <a href="{{ route('property.main', ['locale' => getCurrentLocale()]) }}"
                       class="btn btn-outline-secondary px-4">
                        <i class="fas fa-arrow-left me-2"></i>{{ __('insurance.confirmation.back') }}
                    </a>

                    <form action="{{ route('property.storage', ['locale' => getCurrentLocale()]) }}"
                          method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success px-5" id="btn-proceed">
                            <i class="fas fa-credit-card me-2"></i>{{ __('insurance.confirmation.proceed_to_payment') }}
                            <span id="proceed-spinner" class="d-none ms-2">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            </span>
                        </button>
                    </form>
                </div>

            </div>{{-- /col-lg-8 --}}

            {{-- ── RIGHT COLUMN: Sidebar ─────────────────────────── --}}
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 80px;">
                    <x-insurence.insurance-sidebar
                        :title="__('insurance.property.product_name')"
                        :description="__('insurance.property.subtitle')"
                        :insuranceSum="number_format($applicationData['insuranceAmount'] ?? 0) . ' UZS'"
                        :insurancePremium="number_format($applicationData['insurancePremium'] ?? 0) . ' UZS'"
                    />

                    <div class="bg-white rounded-3 border shadow-sm p-4 mt-3">
                        <div class="d-flex align-items-center gap-2 text-success mb-2">
                            <i class="fas fa-check-circle"></i>
                            <span class="fw-semibold small">{{ __('insurance.confirmation.confirm_data') }}</span>
                        </div>
                        <p class="text-muted small mb-0">{{ __('messages.verify_data_accuracy') }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
document.querySelector('form[action*="storage"]')?.addEventListener('submit', function () {
    const btn = document.getElementById('btn-proceed');
    const spin = document.getElementById('proceed-spinner');
    if (btn) btn.disabled = true;
    if (spin) spin.classList.remove('d-none');
});
</script>
@endpush
