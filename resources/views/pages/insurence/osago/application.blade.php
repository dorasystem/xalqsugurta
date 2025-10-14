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
                                        <i class="fas fa-file-alt"></i>
                                        {{ __('insurance.accident.title') }}
                                    </h3>
                                    <p class="text-muted mb-0">{{ __('insurance.accident.subtitle') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">

                            {{-- Applicant Information --}}
                            <div class="row mb-4">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-user me-2 text-primary"></i>
                                    {{ __('insurance.accident.applicant_info') }}
                                </h5>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm h-100">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.accident.fields.last_name') }}</td>
                                                <td>{{ $data['applicant']['person']['fullName']['lastname'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.first_name') }}</td>
                                                <td>{{ $data['applicant']['person']['fullName']['firstname'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.middle_name') }}</td>
                                                <td>{{ $data['applicant']['person']['fullName']['middlename'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.passport_series') }}</td>
                                                <td>{{ $data['applicant']['person']['passportData']['seria'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm h-100">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.accident.fields.passport_number') }}</td>
                                                <td>{{ $data['applicant']['person']['passportData']['number'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.birth_date') }}</td>
                                                <td>{{ $data['applicant']['person']['birthDate'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.accident.fields.address') }}
                                                </td>
                                                <td>{{ $data['applicant']['address'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.accident.fields.phone') }}
                                                </td>
                                                <td>{{ $data['applicant']['person']['phoneNumber'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Owner Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-shield-alt me-2 text-warning"></i>
                                        {{ __('insurance.osago.owner_info') }}
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.accident.fields.last_name') }}</td>
                                                <td>
                                                    {{ $data['owner']['person']['fullName']['lastname'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.first_name') }}</td>
                                                <td>{{ $data['owner']['person']['fullName']['firstname'] }}
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
                                                    {{ __('insurance.accident.fields.middle_name') }}</td>
                                                <td>{{ $data['owner']['person']['fullName']['middlename'] }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.osago.applicantIsOwner') }}
                                                </td>
                                                <td
                                                    class="text-{{ $data['owner']['applicantIsOwner'] == true ? 'success' : 'danger' }} fw-bold">
                                                    {{ $data['owner']['applicantIsOwner'] == true ? __('insurance.osago.yes_applicantIsOwner') : __('insurance.osago.no_applicantIsOwner') }}
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
                                        <i class="fas fa-user-shield me-2 text-success"></i>
                                        {{ __('insurance.osago.vehicle_info') }}
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
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
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.osago.vehicle_number') }}</td>
                                                <td>{{ $data['vehicle']['govNumber'] }}
                                                </td>
                                            </tr>
                                            {{-- <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.passport_series') }}</td>
                                                <td>{{ $data['insuredInfo']['seria'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr> --}}
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.osago.vehicle_bodyNumber') }}</td>
                                                <td>{{ $data['vehicle']['bodyNumber'] }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.osago.vehicle_engineNumber') }}</td>
                                                <td>{{ $data['vehicle']['engineNumber'] }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.osago.vehicle_registrationInfo') }}
                                                </td>
                                                <td>{{ $data['vehicle']['techPassport']['seria'] }}
                                                    {{ $data['vehicle']['techPassport']['number'] }}
                                                </td>
                                            </tr>
                                            {{-- <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.accident.fields.phone') }}
                                                </td>
                                                <td>{{ $data['phone'] ?? __('insurance.accident.not_specified') }}
                                                </td>
                                            </tr> --}}
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
                                                    {{ number_format($data['cost']['insurancePremium']) }} UZS</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.start_date') }}</td>
                                                <td>{{ $data['details']['startDate'] }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">
                                                    {{ __('insurance.accident.fields.end_date') }}</td>
                                                <td>{{ $data['details']['endDate'] }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    @php
                                        if ($data['cost']['contractTermConclusionId'] == 1) {
                                            $period = __('insurance.osago.1_year');
                                        } elseif ($data['cost']['contractTermConclusionId'] == 0.7) {
                                            $period = __('insurance.osago.6_month');
                                        } elseif ($data['cost']['contractTermConclusionId'] == 0.4) {
                                            $period = __('insurance.osago.3_month');
                                        } else {
                                            $period = '';
                                        }

                                        if ($data['details']['driverNumberRestriction']) {
                                        } else {
                                            $limit = __('insurance.osago.limited_drivers');
                                            $limit = __('insurance.osago.unlimited_drivers');
                                        }
                                    @endphp
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">
                                                    {{ __('insurance.osago.insurance_amount') }}
                                                </td>
                                                <td class="text-success fw-bold">
                                                    {{ number_format($data['cost']['sumInsured']) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.osago.driver_limit') }}
                                                </td>
                                                <td class="fw-bold bg-light">
                                                    {{ $limit }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">{{ __('insurance.osago.insurance_period') }}
                                                </td>
                                                <td class="fw-bold bg-light">
                                                    {{ $period }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            @forelse ($data['drivers'] as $id => $driver)
                                {{-- Drivers info if exist --}}
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="border-bottom pb-2 mb-3">
                                            <i class="fas fa-shield-alt me-2 text-warning"></i>
                                            {{ $id }} - {{ __('insurance.osago.driver_info') }}
                                        </h5>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                <tr>
                                                    <td class="fw-bold bg-light" style="width: 40%;">
                                                        {{ __('insurance.osago.full_name') }}</td>
                                                    <td class="text-success fw-bold">
                                                        {{ $driver }} UZS</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">
                                                        {{ __('insurance.osago.birth_date') }}</td>
                                                    <td>{{ $data['details']['startDate'] }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">
                                                        {{ __('insurance.osago.license_info') }}</td>
                                                    <td>{{ $data['details']['endDate'] }}
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
                                                        {{ __('insurance.osago.license_issueDate') }}
                                                    </td>
                                                    <td class="text-success fw-bold">
                                                        {{ number_format($data['cost']['sumInsured']) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">{{ __('insurance.osago.kinship') }}
                                                    </td>
                                                    <td class="fw-bold bg-light">
                                                        {{ $limit }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">
                                                        {{ __('insurance.osago.issuedBy') }}
                                                    </td>
                                                    <td class="fw-bold bg-light">
                                                        {{ $period }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @empty
                            @endforelse

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
