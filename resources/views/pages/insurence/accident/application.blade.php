@extends('layouts.app')
@section('title', __('insurance.accident.page_title'))

@push('styles')
    <link href="{{ asset('assets/css/form.css') }}" rel="stylesheet">
@endpush

@section('content')
    <section class="container-fluid product-page py-4">
        <div class="container">
            {{-- Error Messages --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>{{ __('insurance.accident.errors.error') }}</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>{{ __('insurance.accident.errors.errors_title') }}</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="card-title mb-0">
                                        <i class="fas fa-file-alt me-2"></i>
                                        {{ __('insurance.accident.title') }}
                                    </h3>
                                    <p class="text-muted mb-0">{{ __('insurance.accident.subtitle') }}</p>
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
                                                href="{{ route('accident.application.view', ['locale' => 'uz']) }}">
                                                <img src="{{ asset('assets/images/flags/uz.png') }}" alt="UZ"
                                                    class="me-2" style="width: 20px;"> O'zbek
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('accident.application.view', ['locale' => 'ru']) }}">
                                                <img src="{{ asset('assets/images/flags/ru.png') }}" alt="RU"
                                                    class="me-2" style="width: 20px;"> Русский
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('accident.application.view', ['locale' => 'en']) }}">
                                                <img src="{{ asset('assets/images/flags/en.png') }}" alt="EN"
                                                    class="me-2" style="width: 20px;"> English
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Applicant Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-user me-2 text-primary"></i>
                                        {{ __('insurance.accident.applicant_info') }}
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.accident.fields.last_name') }}</td>
                                                <td>{{ $applicationData['applicantData']['lastName'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.first_name') }}</td>
                                                <td>{{ $applicationData['applicantData']['firstName'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.middle_name') }}</td>
                                                <td>{{ $applicationData['applicantData']['middleName'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.passport_series') }}</td>
                                                <td>{{ $applicationData['applicantData']['seria'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.accident.fields.passport_number') }}</td>
                                                <td>{{ $applicationData['applicantData']['number'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.birth_date') }}</td>
                                                <td>{{ $applicationData['applicantData']['birthDate'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.accident.fields.address') }}
                                                </td>
                                                <td>{{ $applicationData['applicantData']['address'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.accident.fields.phone') }}
                                                </td>
                                                <td>{{ $applicationData['phone'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Insured Person Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-user-shield me-2 text-success"></i>
                                        {{ __('insurance.accident.insured_info') }}
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.accident.fields.last_name') }}</td>
                                                <td>{{ $applicationData['insuredInfo']['lastName'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.first_name') }}</td>
                                                <td>{{ $applicationData['insuredInfo']['firstName'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.middle_name') }}</td>
                                                <td>{{ $applicationData['insuredInfo']['middleName'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.passport_series') }}</td>
                                                <td>{{ $applicationData['insuredInfo']['seria'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.accident.fields.passport_number') }}</td>
                                                <td>{{ $applicationData['insuredInfo']['number'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.birth_date') }}</td>
                                                <td>{{ $applicationData['insuredInfo']['birthDate'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.accident.fields.address') }}
                                                </td>
                                                <td>{{ $applicationData['insuredInfo']['address'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.accident.fields.phone') }}
                                                </td>
                                                <td>{{ $applicationData['phone'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Insurance Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-shield-alt me-2 text-warning"></i>
                                        {{ __('insurance.accident.insurance_info') }}
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.accident.fields.insurance_amount') }}</td>
                                                <td class="text-success fw-bold">
                                                    {{ number_format($applicationData['insuranceAmount'] ?? 0) }} UZS</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.start_date') }}</td>
                                                <td>{{ $applicationData['paymentStartDate'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.accident.fields.end_date') }}</td>
                                                <td>{{ $applicationData['paymentEndDate'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.accident.fields.premium') }}
                                                </td>
                                                <td class="text-success fw-bold">
                                                    {{ number_format((($applicationData['insuranceAmount'] ?? 0) * 0.3) / 100) }}
                                                    UZS</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Public Offer --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-file-contract me-2 text-info"></i>
                                        {{ __('insurance.accident.public_offer.title') }}
                                    </h5>
                                    <div class="card border-info">
                                        <div class="card-body">
                                            <h6 class="card-title text-info">
                                                <i class="fas fa-gavel me-2"></i>
                                                {{ __('insurance.accident.public_offer.heading') }}
                                            </h6>
                                            <div class="text-justify"
                                                style="max-height: 300px; overflow-y: auto; font-size: 0.9rem;">
                                                <p><strong>1.
                                                        {{ __('insurance.accident.public_offer.section_1') }}</strong></p>
                                                <ul>
                                                    @foreach (__('insurance.accident.public_offer.section_1_items') as $item)
                                                        <li>{{ $item }}</li>
                                                    @endforeach
                                                </ul>

                                                <p><strong>2.
                                                        {{ __('insurance.accident.public_offer.section_2') }}</strong></p>
                                                <ul>
                                                    @foreach (__('insurance.accident.public_offer.section_2_items') as $item)
                                                        <li>{{ $item }}</li>
                                                    @endforeach
                                                    <li>{{ __('insurance.accident.public_offer.section_2_max_amount') }}
                                                        {{ number_format($applicationData['insuranceAmount'] ?? 0) }} UZS
                                                    </li>
                                                </ul>

                                                <p><strong>3.
                                                        {{ __('insurance.accident.public_offer.section_3') }}</strong></p>
                                                <ul>
                                                    @foreach (__('insurance.accident.public_offer.section_3_items') as $item)
                                                        <li>{{ $item }}</li>
                                                    @endforeach
                                                </ul>

                                                <p><strong>4.
                                                        {{ __('insurance.accident.public_offer.section_4') }}</strong></p>
                                                <ul>
                                                    <li>{{ __('insurance.accident.public_offer.section_4_premium') }}
                                                        {{ number_format((($applicationData['insuranceAmount'] ?? 0) * 0.3) / 100) }}
                                                        UZS</li>
                                                    <li>{{ __('insurance.accident.public_offer.section_4_period') }}
                                                        {{ $applicationData['paymentStartDate'] ?? '' }}
                                                        {{ __('insurance.accident.public_offer.section_4_from_to') }}
                                                        {{ $applicationData['paymentEndDate'] ?? '' }}
                                                        {{ __('insurance.accident.public_offer.section_4_to') }}</li>
                                                    <li>{{ __('insurance.accident.public_offer.section_4_payment') }}</li>
                                                </ul>

                                                <p><strong>5.
                                                        {{ __('insurance.accident.public_offer.section_5') }}</strong></p>
                                                <ul>
                                                    @foreach (__('insurance.accident.public_offer.section_5_items') as $item)
                                                        <li>{{ $item }}</li>
                                                    @endforeach
                                                </ul>

                                                <p><strong>6.
                                                        {{ __('insurance.accident.public_offer.section_6') }}</strong></p>
                                                <ul>
                                                    @foreach (__('insurance.accident.public_offer.section_6_items') as $item)
                                                        <li>{{ $item }}</li>
                                                    @endforeach
                                                </ul>

                                                <div class="alert alert-info mt-3">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <strong>{{ __('insurance.accident.public_offer.attention') }}</strong>
                                                    {{ __('insurance.accident.public_offer.attention_text') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Confirmation and Actions --}}
                            <div class="row">
                                <div class="col-12">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="confirm-data"
                                                    required>
                                                <label class="form-check-label" for="confirm-data">
                                                    <strong>{{ __('insurance.accident.confirmation.confirm_data') }}</strong>
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="confirm-offer"
                                                    required>
                                                <label class="form-check-label" for="confirm-offer">
                                                    <strong>{{ __('insurance.accident.confirmation.confirm_offer') }}</strong>
                                                </label>
                                            </div>
                                            <form
                                                action="{{ route('accident.storage', ['locale' => getCurrentLocale()]) }}"
                                                method="POST" id="storage-form">
                                                @csrf
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-success"
                                                        id="confirm-application" disabled>
                                                        <i class="fas fa-check me-2"></i>
                                                        {{ __('insurance.accident.confirmation.proceed_to_payment') }}
                                                    </button>
                                                    <a href="{{ route('accident.main', ['locale' => getCurrentLocale()]) }}"
                                                        class="btn btn-secondary">
                                                        <i class="fas fa-arrow-left me-2"></i>
                                                        {{ __('insurance.accident.confirmation.back') }}
                                                    </a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const confirmData = document.getElementById('confirm-data');
                const confirmOffer = document.getElementById('confirm-offer');
                const confirmBtn = document.getElementById('confirm-application');

                // Enable/disable confirm button based on checkboxes
                function toggleConfirmButton() {
                    if (confirmData.checked && confirmOffer.checked) {
                        confirmBtn.disabled = false;
                        confirmBtn.classList.remove('btn-secondary');
                        confirmBtn.classList.add('btn-success');
                    } else {
                        confirmBtn.disabled = true;
                        confirmBtn.classList.remove('btn-success');
                        confirmBtn.classList.add('btn-secondary');
                    }
                }

                confirmData.addEventListener('change', toggleConfirmButton);
                confirmOffer.addEventListener('change', toggleConfirmButton);

                // Form submit handler
                const storageForm = document.getElementById('storage-form');
                storageForm.addEventListener('submit', function(e) {
                    // Show loading state
                    confirmBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('insurance.accident.confirmation.creating_order') }}';
                    confirmBtn.disabled = true;
                });

                // Initialize button state
                toggleConfirmButton();
            });
        </script>
    @endpush
@endsection
