@extends('layouts.app')
@section('title', __('insurance.accident.page_title'))

@push('styles')
    <link href="{{ asset('assets/css/form.css') }}" rel="stylesheet">
@endpush

@section('content')
    <x-insurence.steps :activeStep="2" />
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
                                    <i class="fas fa-user text-primary"></i>
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
                                                <td>+{{ $data['applicant']['person']['phoneNumber'] ?? __('insurance.accident.not_specified') }}
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
                                        <i class="fas fa-shield-alt text-warning"></i>
                                        {{ __('insurance.osago.owner_info') }}
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm h-100">
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
                                    <table class="table table-bordered table-sm h-100">
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
                                    <table class="table table-bordered table-sm h-100">
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
                                        <i class="fas fa-shield-alt text-warning"></i>
                                        {{ __('insurance.accident.insurance_info') }}
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm h-100">
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
                                            $limit = __('insurance.osago.limited_drivers');
                                        } else {
                                            $limit = __('insurance.osago.unlimited_drivers');
                                        }
                                    @endphp
                                    <table class="table table-bordered table-sm h-100">
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
                                            <i class="fas fa-shield-alt text-warning"></i>
                                            {{ $loop->iteration }} - {{ __('insurance.osago.driver_info') }}
                                        </h5>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-bordered table-sm h-100">
                                            <tbody>
                                                <tr>
                                                    <td class="fw-bold bg-light" style="width: 40%;">
                                                        {{ __('insurance.osago.full_name') }}</td>
                                                    <td class="text-success fw-bold">
                                                        {{ $driver['fullName']['lastname'] }}
                                                        {{ $driver['fullName']['firstname'] }}
                                                        {{ $driver['fullName']['middlename'] }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">
                                                        {{ __('insurance.osago.birth_date') }}</td>
                                                    <td>{{ $driver['birthDate'] }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">
                                                        {{ __('insurance.osago.license_info') }}</td>
                                                    <td>{{ $driver['licenseSeria'] }} {{ $driver['licenseNumber'] }}
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
                                                        {{ __('insurance.osago.license_issueDate') }}
                                                    </td>
                                                    <td class="text-success fw-bold">
                                                        {{ $driver['licenseIssueDate'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">{{ __('insurance.osago.kinship_label') }}
                                                    </td>
                                                    <td class="fw-bold bg-light">
                                                        {{ __("insurance.osago.kinship.{$driver['relative']}") }}
                                                    </td>
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
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="confirm-offer" required>
                                        <label class="form-check-label" for="confirm-offer">
                                            <strong>
                                                <a href="#"
                                                    id="offer-link">{{ __('insurance.accident.confirmation.confirm_offer') }}</a>
                                            </strong>
                                        </label>
                                    </div>


                                    <form action="{{ route('osago.storage', ['locale' => getCurrentLocale()]) }}"
                                        method="POST" id="storage-form">
                                        @csrf

                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="{{ route('kafil.main', ['locale' => getCurrentLocale()]) }}"
                                                class="btn btn-secondary custom-btn">
                                                <i class="fas fa-arrow-left me-2"></i>
                                                {{ __('insurance.accident.confirmation.back') }}
                                            </a>

                                            <button type="submit" class="btn btn-success hover-light-btn"
                                                style="margin-left: 70%" id="confirm-application" disabled>
                                                <i class="fas fa-check me-2"></i>
                                                {{ __('insurance.accident.confirmation.proceed_to_payment') }}
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
                const confirmOffer = document.getElementById('confirm-offer');
                const confirmBtn = document.getElementById('confirm-application');

                function toggleConfirmButton() {
                    confirmBtn.disabled = !confirmOffer.checked;
                }

                confirmOffer.addEventListener('change', toggleConfirmButton);

                const storageForm = document.getElementById('storage-form');
                storageForm.addEventListener('submit', function(e) {
                    confirmBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('insurance.accident.confirmation.creating_order') }}';
                    confirmBtn.disabled = true;
                });

                toggleConfirmButton(); // initialize
            });
        </script>
    @endpush

    <style>
        .hover-light-btn {
            transition: all 0.3s ease;
        }

        .hover-light-btn:hover {
            background-color: white !important;
            /* Hoverda fon oq */
            color: #0d6efd !important;
            /* Matn va icon ko‘k */
            border-color: #0d6efd !important;
            /* Border ham ko‘k */
        }

        .hover-light-btn:hover i {
            color: #0d6efd !important;
            /* Icon rangini ham o‘zgartirish */
        }

        .custom-btn {
            background-color: var(--violet);
            /* Asosiy fon rangi */
            color: white;
            /* Matn oq rangda */
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            /* Icon va matn orasidagi bo‘sh joy */
            border-radius: 0.25rem;
            border: 1px solid var(--violet);
            padding: 0.5rem 1rem;
            min-width: 100px;
            transition: all 0.3s ease;
        }

        .custom-btn:hover {
            background-color: white;
            /* Hover paytida fon oq bo‘ladi */
            color: var(--violet);
            /* Matn va icon violet rangga o‘tadi */
            border-color: var(--violet);
            text-decoration: none;
        }
    </style>
@endsection
