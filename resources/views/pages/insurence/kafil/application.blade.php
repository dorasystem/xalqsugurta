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

                                    <p class="text-muted mb-0" style="font-size: 25px">Aholi xonadonida suyultirilgan gaz
                                        ballonidan foydalanish
                                        oqibatida yuz berishi mumkin bo‘lgan qaltisliklardan sug‘urtalash</p>
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
                            {{-- Confirmation and Actions --}}
                            <div class="row">
                                <div class="col-12">
                                    <div>
                                        <div class="card-body">
                                            {{-- <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="confirm-data" required>
                                                <label class="form-check-label" for="confirm-data">
                                                    <strong>{{ __('insurance.accident.confirmation.confirm_data') }}</strong>
                                                </label>
                                            </div> --}}
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="confirm-offer" required>
                                                <label class="form-check-label" for="confirm-offer">
                                                    <strong>
                                                        <a href="#"
                                                            id="offer-link">{{ __('insurance.accident.confirmation.confirm_offer') }}</a>
                                                    </strong>
                                                </label>
                                            </div>

                                            <form
                                                action="{{ route('accident.storage', ['locale' => getCurrentLocale()]) }}"
                                                method="POST" id="storage-form">
                                                @csrf
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('kafil.main', ['locale' => getCurrentLocale()]) }}"
                                                        class="btn btn-secondary custom-btn">
                                                        <i class="fas fa-arrow-left me-2"></i>
                                                        {{ __('insurance.accident.confirmation.back') }}
                                                    </a>

                                                    <button type="submit" class="btn btn-success hover-light-btn" style="margin-left: 70%"
                                                        id="confirm-application" disabled>
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
