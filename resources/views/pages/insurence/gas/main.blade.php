@extends('layouts.app')
@section('title', 'Gaz Ballon Sug‘urtasi')

@push('styles')
    <link href="{{ asset('assets/css/form.css') }}" rel="stylesheet">
@endpush

@section('content')
    <x-insurence.steps :activeStep="1" />
    <section class="container-fluid product-page py-4" id="gas-main">
        <div class="container">
            <form id="gas-form" method="POST" action="{{ route('gas.application', app()->getLocale()) }}">
                @csrf
                <div class="row g-4">
                    <div class="col-md-8">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h4 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    {{ __('messages.error') }}
                                </h4>
                                <p class="mb-2">{{ __('messages.validation_errors_found') }}</p>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('messages.applicant_info_title') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <x-inputs.input_form :type="'text'" :class="'col-md-2'" :idFor="'applicant-passport-series'" :name="'applicant[passportSeries]'"
                                        :placeholder="'messages.passport_series_placeholder'" :label="'messages.passport_series'" />

                                    <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'applicant-passport-number'" :name="'applicant[passportNumber]'"
                                        :placeholder="'messages.passport_number_placeholder'" :label="'messages.passport_number'" />

                                    <x-inputs.input_form :type="'number'" :class="'col-md-4'" :idFor="'applicant-pinfl'" :name="'applicant[pinfl]'"
                                        :placeholder="'messages.owner_pinfl_placeholder'" :label="'messages.owner_pinfl'" />

                                    <x-inputs.input_form :type="'date'" :class="'col-md-3'" :idFor="'applicant-birth-date'" :name="'applicant[birthDate]'"
                                        :placeholder="'messages.birth_date_placeholder'" :label="'messages.birth_date'" />
                                </div>

                                <div class="row mt-3">
                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-last-name'" :name="'applicant[lastName]'" :label="'messages.last_name'"
                                        :placeholder="'messages.last_name_placeholder'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-first-name'" :name="'applicant[firstName]'" :label="'messages.first_name'"
                                        :placeholder="'messages.first_name_placeholder'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-middle-name'" :name="'applicant[middleName]'" :label="'messages.middle_name'"
                                        :placeholder="'messages.middle_name_placeholder'" />
                                </div>

                                <div class="row mt-3">
                                    <x-inputs.input_info :class="'col-md-6'" :idFor="'applicant-address'" :name="'applicant[address]'" :label="'messages.address'"
                                        :placeholder="'messages.address_placeholder'" />
                                    <x-inputs.input_form :type="'text'" :class="'col-md-6'" :idFor="'applicant-phone-number'" :name="'applicant[phoneNumber]'"
                                        :label="'messages.phone_number'" :placeholder="'messages.phone_number_placeholder'" />
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('messages.property_insurance_payment') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <x-inputs.input_form :type="'text'" :class="'col-md-6'" :idFor="'cadaster-number'" :name="'property[cadasterNumber]'"
                                        :placeholder="'messages.property_cadaster_placeholder'" :label="'messages.property_cadaster'"/>
                                    <x-inputs.input_form :type="'text'" :class="'col-md-6'" :idFor="'sum-bank'" :name="'cost[sum_bank]'"
                                        :placeholder="'messages.insurance_amount'" :label="'messages.insurance_amount'"/>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label for="policy_start_date" class="form-label">{{ __('messages.start_date') }}</label>
                                        <input type="date" class="form-control @error('details.startDate') is-invalid @enderror"
                                               id="policy_start_date" name="details[startDate]" value="{{ old('details.startDate') }}" required>
                                        @error('details.startDate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="policy_end_date" class="form-label">{{ __('messages.end_date') }}</label>
                                        <input type="date" class="form-control @error('details.endDate') is-invalid @enderror"
                                               id="policy_end_date" name="details[endDate]" value="{{ old('details.endDate') }}" required>
                                        @error('details.endDate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="card p-3">
                                            <div class="d-flex justify-content-between">
                                                <span>{{ __('messages.insurance_sum') }}</span>
                                                <strong id="amount">{{ old('cost.sum_bank') }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>0.5%</span>
                                                <strong>—</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>{{ __('messages.insurance_premium') }}</span>
                                                <strong id="premium">0</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                {{ __('messages.proceed_to_payment') }}
                            </button>
                        </div>
                    </div>

                    <x-insurence.calculate />
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sumBank = document.getElementById('sum-bank');
            const premiumEl = document.getElementById('premium');
            const amountEl = document.getElementById('amount');

            function format(n) { return (Number(n || 0)).toLocaleString('en-US'); }
            function calcPremium(v) { return Math.round(Number(v || 0) * 0.005); }

            function recalc() {
                const v = sumBank?.value || 0;
                amountEl.textContent = format(v);
                premiumEl.textContent = format(calcPremium(v));
            }
            if (sumBank) {
                sumBank.addEventListener('input', recalc);
                recalc();
            }
        });
    </script>
@endpush


