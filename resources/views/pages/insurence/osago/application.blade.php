@extends('layouts.app')
@section('title', __('insurance.osago.page_title'))

@push('styles')
    <link href="{{ asset('assets/css/form.css') }}" rel="stylesheet">
@endpush

@section('content')
    <x-insurence.steps :activeStep="2" />
    <section class="container-fluid product-page py-4">
        <div class="container">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>{{ __('errors.error') }}</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>{{ __('errors.errors_title') }}</strong>
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
                                        <i class="fas fa-file-alt"></i>
                                        {{ __('insurance.osago.page_title') }}
                                    </h3>
                                    <p class="text-muted mb-0">{{ __('insurance.osago.subtitle') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">

                            {{-- Applicant Information --}}
                            <div class="row mb-4">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-user text-primary"></i>
                                    {{ __('insurance.osago.applicant_info') }}
                                </h5>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm h-100">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('messages.last_name') }}</td>
                                                <td>{{ $data['applicant']['person']['fullName']['lastname'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('messages.first_name') }}</td>
                                                <td>{{ $data['applicant']['person']['fullName']['firstname'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('messages.middle_name') }}</td>
                                                <td>{{ $data['applicant']['person']['fullName']['middlename'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('messages.passport_series') }}</td>
                                                <td>{{ $data['applicant']['person']['passportData']['seria'] ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm h-100">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('messages.passport_number') }}</td>
                                                <td>{{ $data['applicant']['person']['passportData']['number'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('messages.birth_date') }}</td>
                                                <td>{{ $data['applicant']['person']['birthDate'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('messages.address') }}</td>
                                                <td>{{ $data['applicant']['address'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('messages.phone_number') }}</td>
                                                <td>{{ $data['applicant']['person']['phoneNumber'] ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Owner Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-shield-alt text-warning"></i>
                                        {{ __('insurance.osago.owner_info') }}
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm h-100">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('messages.last_name') }}</td>
                                                <td>{{ $data['owner']['person']['fullName']['lastname'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('messages.first_name') }}</td>
                                                <td>{{ $data['owner']['person']['fullName']['firstname'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('messages.middle_name') }}</td>
                                                <td>{{ $data['owner']['person']['fullName']['middlename'] ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm h-100">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.osago.applicantIsOwner') }}</td>
                                                <td class="text-{{ ($data['owner']['applicantIsOwner'] ?? 'false') === 'true' ? 'success' : 'danger' }} fw-bold">
                                                    {{ ($data['owner']['applicantIsOwner'] ?? 'false') === 'true' ? __('insurance.osago.yes_applicantIsOwner') : __('insurance.osago.no_applicantIsOwner') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Vehicle Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-user-shield text-success"></i>
                                        {{ __('insurance.osago.vehicle_info') }}
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm h-100">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.osago.vehicle_model') }}</td>
                                                <td>{{ $data['vehicle']['modelCustomName'] }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.osago.vehicle_year') }}</td>
                                                <td>{{ $data['vehicle']['issueYear'] }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.osago.vehicle_number') }}</td>
                                                <td>{{ $data['vehicle']['govNumber'] ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm h-100">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.osago.vehicle_bodyNumber') }}</td>
                                                <td>{{ $data['vehicle']['bodyNumber'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.osago.vehicle_engineNumber') }}</td>
                                                <td>{{ $data['vehicle']['engineNumber'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.osago.vehicle_registrationInfo') }}</td>
                                                <td>
                                                    {{ ($data['vehicle']['techPassport']['seria'] ?? '') . ' ' . ($data['vehicle']['techPassport']['number'] ?? '') }}
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
                                        <i class="fas fa-shield-alt text-warning"></i>
                                        {{ __('insurance.osago.insurance_info') }}
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm h-100">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.osago.insurance_premium') }}</td>
                                                <td class="text-success fw-bold">
                                                    {{ number_format($data['cost']['insurancePremium'] ?? 0) }} UZS</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('messages.start_date') }}</td>
                                                <td>{{ $data['details']['startDate'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('messages.end_date') }}</td>
                                                <td>{{ $data['details']['endDate'] ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    @php
                                        $periodId = $data['cost']['contractTermConclusionId'] ?? 1;
                                        $period = match($periodId) {
                                            1 => __('messages.1_year'),
                                            '0.7' => __('messages.6_months'),
                                            '0.4' => __('messages.3_months'),
                                            default => '-'
                                        };
                                        $limit = ($data['details']['driverNumberRestriction'] ?? false)
                                            ? __('insurance.osago.limited_drivers')
                                            : __('insurance.osago.unlimited_drivers');
                                    @endphp
                                    <table class="table table-bordered table-sm h-100">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.osago.sum_insured') }}</td>
                                                <td class="text-success fw-bold">
                                                    {{ number_format($data['cost']['sumInsured'] ?? 0) }} UZS</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.osago.driver_limit') }}</td>
                                                <td>{{ $limit }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.osago.insurance_period') }}</td>
                                                <td>{{ $period }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            @if (!empty($data['drivers']))
                                @foreach ($data['drivers'] as $driver)
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h5 class="border-bottom pb-2 mb-3">
                                                <i class="fas fa-user-tie text-info"></i>
                                                {{ $loop->iteration }} - {{ __('insurance.osago.driver_info') }}
                                            </h5>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-bordered table-sm h-100">
                                                <tbody>
                                                    <tr>
                                                        <td class="fw-bold bg-light" style="width: 40%;">
                                                            {{ __('messages.full_name') }}</td>
                                                        <td>
                                                            {{ ($driver['fullName']['lastname'] ?? '') . ' ' . ($driver['fullName']['firstname'] ?? '') . ' ' . ($driver['fullName']['middlename'] ?? '') }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold bg-light">{{ __('messages.birth_date') }}</td>
                                                        <td>{{ $driver['birthDate'] ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold bg-light">{{ __('insurance.osago.license_info') }}</td>
                                                        <td>{{ ($driver['licenseSeria'] ?? '') . ' ' . ($driver['licenseNumber'] ?? '') }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-bordered table-sm h-100">
                                                <tbody>
                                                    <tr>
                                                        <td class="fw-bold bg-light" style="width: 40%;">
                                                            {{ __('insurance.osago.license_issueDate') }}</td>
                                                        <td>{{ $driver['licenseIssueDate'] ?? '-' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            {{-- Confirmation and Actions --}}
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="confirm-data" required>
                                        <label class="form-check-label" for="confirm-data">
                                            <strong>{{ __('insurance.confirmation.confirm_data') }}</strong>
                                        </label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="confirm-offer" required>
                                        <label class="form-check-label" for="confirm-offer">
                                            <strong>{{ __('insurance.confirmation.confirm_offer') }}</strong>
                                        </label>
                                    </div>

                                    <form action="{{ route('osago.storage', ['locale' => getCurrentLocale()]) }}"
                                        method="POST" id="storage-form">
                                        @csrf

                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="{{ route('osago.main', ['locale' => getCurrentLocale()]) }}"
                                                class="btn btn-secondary">
                                                <i class="fas fa-arrow-left me-2"></i>
                                                {{ __('messages.back') }}
                                            </a>

                                            <button type="submit" class="btn btn-success" id="confirm-application"
                                                disabled>
                                                <i class="fas fa-check me-2"></i>
                                                {{ __('messages.proceed_to_payment') }}
                                            </button>
                                        </div>
                                    </form>
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
                    confirmBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>';
                    confirmBtn.disabled = true;
                });

                // Initialize button state
                toggleConfirmButton();
            });
        </script>
    @endpush
@endsection
