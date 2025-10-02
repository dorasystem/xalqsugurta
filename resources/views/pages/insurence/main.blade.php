@extends('layouts.app')
@section('title', 'OSAGO')

@push('styles')
    <link href="{{ asset('assets/css/form.css') }}" rel="stylesheet">
@endpush

@section('content')
    <x-insurence.steps />
    <section class="container-fluid product-page py-4" id="osago-main" style="">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-8">
                    <div id="vehicle-info" class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.vehicle_info_title') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row gap-1">

                                <x-inputs.input_form :col=3 :name="'gov_number'" :placeholder="'messages.gov_number_placeholder'"  :label="'messages.tech_passport_series'"/>

                                <x-inputs.input_form :col=2 :name="'tech_passport_series'" :placeholder="'messages.tech_passport_series_placeholder'"  :label="'messages.gov_number'"/>

                                <x-inputs.input_form :col=3 :name="'tech_passport_number'" :placeholder="'messages.tech_passport_number_placeholder'" :label="'messages.tech_passport_number'" />
                                
                                <x-inputs.button :class="'col-md-3'" :button="'vehicle-search-btn'" />




                            </div>
                        </div>

                        <div id="vehicle-info-display" class="card-footer d-none">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="brand" class="form-label">{{ __('messages.brand') }}</label>
                                    <input type="text" id="brand"
                                        placeholder="{{ __('messages.brand_placeholder') }}" name="brand"
                                        class="form-input" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="car_type" class="form-label">{{ __('messages.car_type') }}</label>
                                    <input type="text" id="car_type"
                                        placeholder="{{ __('messages.car_type_placeholder') }}" name="car_type"
                                        class="form-input" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="car_year" class="form-label">{{ __('messages.car_year') }}</label>
                                    <input type="text" id="car_year"
                                        placeholder="{{ __('messages.car_year_placeholder') }}" name="car_year"
                                        class="form-input" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="registration_region"
                                        class="form-label">{{ __('messages.registration_region') }}</label>
                                    <input type="text" id="registration_region"
                                        placeholder="{{ __('messages.registration_region_placeholder') }}"
                                        name="registration_region" class="form-input" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="car_owner" class="form-label">{{ __('messages.car_owner') }}</label>
                                    <input type="text" id="car_owner"
                                        placeholder="{{ __('messages.car_owner_placeholder') }}" name="car_owner"
                                        class="form-input" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="model" class="form-label">{{ __('messages.model') }}</label>
                                    <input type="text" id="model"
                                        placeholder="{{ __('messages.model_placeholder') }}" name="model"
                                        class="form-input" readonly>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div id="owner-info" class="card d-none">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.owner_info_title') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2 d-flex flex-column justify-content-end">
                                    <label for="insurance-passport-series"
                                        class="form-label">{{ __('messages.passport_series') }}</label>
                                    <input type="text" id="insurance-passport-series"
                                        placeholder="{{ __('messages.passport_series_placeholder') }}"
                                        name="passport_series" class="form-input">
                                </div>
                                <div class="col-md-3 d-flex flex-column  justify-content-end">
                                    <label for="insurance-passport-number"
                                        class="form-label">{{ __('messages.passport_number') }}</label>
                                    <input type="text" id="insurance-passport-number"
                                        placeholder="{{ __('messages.passport_number_placeholder') }}"
                                        name="passport_number" class="form-input">
                                </div>
                                <div class="col-md-4 d-flex flex-column justify-content-end">
                                    <label for="insurance-pinfl"
                                        class="form-label">{{ __('messages.owner_pinfl') }}</label>
                                    <input type="text" id="insurance-pinfl" disabled
                                        placeholder="{{ __('messages.owner_pinfl_placeholder') }}" name="pinfl"
                                        class="form-input">
                                </div>
                                <div class="col-md-2 d-flex flex-column justify-content-end">
                                    <button id="owner-information-search-btn" class="btn btn-icon"><svg width="20"
                                            height="20">
                                            <use xlink:href="#icon-search"></use>
                                        </svg></button>
                                </div>
                            </div>
                            <div class="row d-none" id="insurance-driver-full-information">
                                <div class="col-md-2 px-3 d-flex flex-column justify-content-end">
                                    <label for="insurance-last-name"
                                        class="form-label">{{ __('messages.owner_last_name') }}</label>
                                    <input type="text" id="insurance-last-name"
                                        placeholder="{{ __('messages.owner_last_name_placeholder') }}" name="last_name"
                                        readonly class="form-input">
                                </div>
                                <div class="col-md-4 d-flex flex-column justify-content-end">
                                    <label for="insurance-first-name"
                                        class="form-label">{{ __('messages.owner_first_name') }}</label>
                                    <input type="text" id="insurance-first-name"
                                        placeholder="{{ __('messages.owner_first_name_placeholder') }}" name="first_name"
                                        readonly class="form-input">
                                </div>
                                <div class="col-md-4 d-flex flex-column justify-content-end">
                                    <label for="insurance-middle-name"
                                        class="form-label">{{ __('messages.owner_middle_name') }}</label>
                                    <input type="text" id="insurance-middle-name"
                                        placeholder="{{ __('messages.owner_middle_name_placeholder') }}"
                                        name="middle_name" readonly class="form-input">
                                </div>
                            </div>
                            <!-- Hidden fields for storing data -->
                            <input type="hidden" id="pinfl-hidden" name="pinfl_hidden">
                            <input type="hidden" id="applicant-passport-data" name="applicant_passport_data">
                        </div>
                    </div>

                    <div id="applicant-info" class="card d-none">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.applicant_info_title') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-check ">
                                <input class="form-check-input" type="checkbox" id="is-applicant-owner">
                                <label class="form-check-label" for="is-applicant-owner">
                                    {{ __('messages.is_applicant_owner') }}
                                </label>
                            </div>

                            <div id="applicant-info-search">
                                <div class="row">
                                    <div class="col-md-2 d-flex flex-column justify-content-end">
                                        <label for="applicant-passport-series"
                                            class="form-label">{{ __('messages.applicant_passport_series') }}</label>
                                        <input type="text" id="applicant-passport-series"
                                            placeholder="{{ __('messages.applicant_passport_series_placeholder') }}"
                                            name="applicant_passport_series" class="form-input">
                                    </div>
                                    <div class="col-md-3 d-flex flex-column justify-content-end">
                                        <label for="applicant-passport-number"
                                            class="form-label">{{ __('messages.applicant_passport_number') }}</label>
                                        <input type="text" id="applicant-passport-number"
                                            placeholder="{{ __('messages.applicant_passport_number_placeholder') }}"
                                            name="applicant_passport_number" class="form-input">
                                    </div>
                                    <div class="col-md-4 d-flex flex-column justify-content-end">
                                        <label for="applicant-pinfl"
                                            class="form-label">{{ __('messages.applicant_pinfl') }}</label>
                                        <input type="text" id="applicant-pinfl"
                                            placeholder="{{ __('messages.applicant_pinfl_placeholder') }}"
                                            name="applicant_pinfl" class="form-input">
                                    </div>
                                    <div class="col-md-2 d-flex flex-column justify-content-end">
                                        <button type="button" id="applicant-information-search-btn"
                                            class="btn btn-icon">
                                            <svg width="20" height="20">
                                                <use xlink:href="#icon-search"></use>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="applicant-info-display" class="d-none">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="applicant-last-name"
                                            class="form-label">{{ __('messages.applicant_last_name') }}</label>
                                        <input type="text" id="applicant-last-name" name="applicant_last_name"
                                            readonly class="form-input">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="applicant-first-name"
                                            class="form-label">{{ __('messages.applicant_first_name') }}</label>
                                        <input type="text" id="applicant-first-name" name="applicant_first_name"
                                            readonly class="form-input">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="applicant-middle-name"
                                            class="form-label">{{ __('messages.applicant_middle_name') }}</label>
                                        <input type="text" id="applicant-middle-name" name="applicant_middle_name"
                                            readonly class="form-input">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="applicant-address"
                                            class="form-label">{{ __('messages.applicant_address') }}</label>
                                        <input type="text" id="applicant-address" name="applicant_address" readonly
                                            class="form-input">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="applicant-phone-number"
                                            class="form-label">{{ __('messages.applicant_phone_number') }}</label>
                                        <input type="text" id="applicant-phone-number" name="applicant_phone_number"
                                            readonly class="form-input">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div id="policy-calculation" class="card">
                        <div class="card-header">
                            <h4>{{ __('messages.policy_calculation') }}</h4>
                        </div>
                        <div class="card-body">
                            <!-- Policy calculation form -->
                            <form id="policy-calculation-form" method="POST"
                                action="{{ route('osago.calculation', app()->getLocale()) }}">
                                @csrf

                                <!-- Policy details -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="policy_start_date"
                                            class="form-label">{{ __('messages.policy_start_date') }}</label>
                                        <input type="date" class="form-control" id="policy_start_date"
                                            name="policy_start_date" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="policy_end_date"
                                            class="form-label">{{ __('messages.policy_end_date') }}</label>
                                        <input type="date" class="form-control" id="policy_end_date"
                                            name="policy_end_date" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="insurance_period"
                                            class="form-label">{{ __('messages.insurance_period') }}</label>
                                        <select class="form-select" id="insurance_period" name="insurance_period"
                                            required>
                                            <option value="1_year">{{ __('messages.period_1_year') }}</option>
                                            <option value="6_months">{{ __('messages.period_6_months') }}</option>
                                            <option value="3_months">{{ __('messages.period_3_months') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="discount_option"
                                            class="form-label">{{ __('messages.discount_option_title') }}</label>
                                        <select class="form-select" id="discount_option" name="discount_option" required>
                                            <option value="1.0">{{ __('messages.no_discount') }}</option>
                                            <option value="0.5">{{ __('messages.war_participants') }}</option>
                                            <option value="0.5">{{ __('messages.labor_veterans') }}</option>
                                            <option value="0.5">{{ __('messages.concentration_camp_survivors') }}
                                            </option>
                                            <option value="0.5">{{ __('messages.military_injured') }}</option>
                                            <option value="0.5">{{ __('messages.military_victims') }}</option>
                                            <option value="0.5">{{ __('messages.pensioner') }}</option>
                                            <option value="0.5">{{ __('messages.disabled') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="cases"
                                            class="form-label">{{ __('messages.incidents_option_title') }}</label>
                                        <select class="form-select" id="cases" name="cases" required>
                                            <option value="1">{{ __('messages.first_time_no_cases') }}</option>
                                            <option value="2">{{ __('messages.one_case') }}</option>
                                            <option value="2.5">{{ __('messages.two_cases') }}</option>
                                            <option value="3">{{ __('messages.three_or_more_cases') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('messages.driver_limit') }}</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="driver_limit"
                                                id="driver_limited" value="limited" checked>
                                            <label class="form-check-label" for="driver_limited">
                                                {{ __('messages.limited_drivers') }}
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="driver_limit"
                                                id="driver_unlimited" value="unlimited">
                                            <label class="form-check-label" for="driver_unlimited">
                                                {{ __('messages.unlimited_drivers') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit button -->
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary-custom" id="calculate-policy-btn">
                                        <svg width="20" height="20">
                                            <use xlink:href="#icon-calculator"></use>
                                        </svg>
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
                <x-insurence.calculate />
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            window.translations = {
                // Messages
                search: "{{ __('messages.search') }}",
                searching: "{{ __('messages.searching') }}",
                vehicle_data_loaded: "{{ __('messages.vehicle_data_loaded') }}",
                owner_data_loaded: "{{ __('messages.owner_data_loaded') }}",
                applicant_data_loaded: "{{ __('messages.applicant_data_loaded') }}",
                owner_copied_as_applicant: "{{ __('messages.owner_copied_as_applicant') }}",
                applicant_fields_cleared: "{{ __('messages.applicant_fields_cleared') }}",
                policy_calculated: "{{ __('messages.policy_calculated') }}",
                next_step_clicked: "{{ __('messages.next_step_clicked') }}",
                add_driver_soon: "{{ __('messages.add_driver_soon') }}",
                load_owner_first: "{{ __('messages.load_owner_first') }}",

                // Errors
                all_fields_required: "{{ __('messages.all_fields_required') }}",
                pinfl_mismatch: "{{ __('messages.pinfl_mismatch') }}",
                api_error: "{{ __('messages.api_error') }}",
                request_error: "{{ __('messages.request_error') }}",
                policy_calculation_error: "{{ __('messages.policy_calculation_error') }}",
                select_policy_start_date: "{{ __('messages.select_policy_start_date') }}",
                calculate_policy_first: "{{ __('messages.calculate_policy_first') }}",

                // Validation
                validation_3_letters: "{{ __('messages.validation_3_letters') }}",
                validation_7_digits: "{{ __('messages.validation_7_digits') }}",
                validation_year_range: "{{ __('messages.validation_year_range') }}",

                // Period text
                period_1_year: "{{ __('messages.1_year') }}",
                period_6_months: "{{ __('messages.6_months') }}",
                period_3_months: "{{ __('messages.3_months') }}",

                // Currency
                sum: "{{ __('messages.sum') }}"
            };
        </script>
        @vite(['resources/js/pages/insurence/main.js'])
    @endpush
@endsection
