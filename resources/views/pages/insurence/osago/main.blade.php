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

                                <x-inputs.input_form :class="'col-md-3'" :idFor="'gov_number'" :name="'gov_number'" :placeholder="'messages.gov_number_placeholder'"
                                    :label="'messages.gov_number'" />

                                <x-inputs.input_form :class="'col-md-2'" :idFor="'tech_passport_series'" :name="'tech_passport_series'" :placeholder="'messages.tech_passport_series_placeholder'"
                                    :label="'messages.tech_passport_series'" />

                                <x-inputs.input_form :class="'col-md-3'" :idFor="'tech_passport_number'" :name="'tech_passport_number'"
                                    :placeholder="'messages.tech_passport_number_placeholder'" :label="'messages.tech_passport_number'" />

                                <x-inputs.button :class="'col-md-3'" :button="'vehicle-search-btn'" />

                            </div>
                        </div>

                        <div id="vehicle-info-display" class="card-footer d-none">
                            <div class="row">

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'brand'" :name="'brand'"
                                    :label="'messages.brand'" :placeholder="'messages.brand_placeholder'" />

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'car_type'" :name="'car_type'"
                                    :label="'messages.car_type'" :placeholder="'messages.car_type_placeholder'" />

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'car_year'" :name="'car_year'"
                                    :label="'messages.car_year'" :placeholder="'messages.car_year_placeholder'" />

                            </div>

                            <div class="row">

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'registration_region'" :name="'registration_region'"
                                    :label="'messages.registration_region'" :placeholder="'messages.registration_region_placeholder'" />

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'car_owner'" :name="'car_owner'"
                                    :label="'messages.car_owner'" :placeholder="'messages.car_owner_placeholder'" />

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'model'" :name="'model'"
                                    :label="'messages.model'" :placeholder="'messages.model_placeholder'" />

                            </div>
                        </div>

                    </div>

                    <div id="owner-info" class="card d-none">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.owner_info_title') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <x-inputs.input_form :class="'col-md-2'" :idFor="'insurance-passport-series'" :name="'passport_series'"
                                    :placeholder="'messages.passport_series_placeholder'" :label="'messages.passport_series'" />

                                <x-inputs.input_form :class="'col-md-3'" :idFor="'insurance-passport-number'" :name="'passport_number'"
                                    :placeholder="'messages.passport_number_placeholder'" :label="'messages.passport_number'" />

                                <x-inputs.input_form :class="'col-md-4'" :idFor="'insurance-pinfl'" :name="'pinfl'"
                                    :placeholder="'messages.owner_pinfl_placeholder'" :label="'messages.owner_pinfl'" :disabled="true" />

                                <x-inputs.button :class="'col-md-2'" :button="'owner-information-search-btn'" />

                            </div>

                            <div class="row d-none" id="insurance-driver-full-information">

                                <x-inputs.input_info :class="'col-md-2'" :idFor="'insurance-last-name'" :name="'last_name'"
                                    :label="'messages.owner_last_name'" :placeholder="'messages.owner_last_name_placeholder'" />

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'insurance-first-name'" :name="'first_name'"
                                    :label="'messages.owner_first_name'" :placeholder="'messages.owner_first_name_placeholder'" />

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'insurance-middle-name'" :name="'middle_name'"
                                    :label="'messages.owner_middle_name'" :placeholder="'messages.owner_middle_name_placeholder'" />

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
                                    <x-inputs.input_form :class="'col-md-2'" :idFor="'applicant-passport-series'" :name="'applicant_passport_series'"
                                        :placeholder="'messages.applicant_passport_series_placeholder'" :label="'messages.applicant_passport_series'" />

                                    <x-inputs.input_form :class="'col-md-3'" :idFor="'applicant-passport-number'" :name="'applicant_passport_number'"
                                        :placeholder="'messages.applicant_passport_number_placeholder'" :label="'messages.applicant_passport_number'" />

                                    <x-inputs.input_form :class="'col-md-4'" :idFor="'applicant-pinfl'" :name="'applicant_pinfl'"
                                        :placeholder="'messages.applicant_pinfl_placeholder'" :label="'messages.applicant_pinfl'" />

                                    <x-inputs.button :class="'col-md-2'" :button="'applicant-information-search-btn'" />
                                </div>
                            </div>  

                            <div id="applicant-info-display" class="d-none">
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-last-name'" :name="'applicant_last_name'"
                                        :label="'messages.applicant_last_name'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-first-name'" :name="'applicant_first_name'"
                                        :label="'messages.applicant_first_name'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-middle-name'" :name="'applicant_middle_name'"
                                        :label="'messages.applicant_middle_name'" />
                                </div>

                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-address'" :name="'applicant_address'"
                                        :label="'messages.applicant_address'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-phone-number'" :name="'applicant_phone_number'"
                                        :label="'messages.applicant_phone_number'" />
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
                                        <select class="form-select" id="insurance_period" name="insurance_period" required>
                                            <option value="1_year">{{ __('messages.1_year') }}</option>
                                            <option value="6_months">{{ __('messages.6_months') }}</option>
                                            <option value="3_months">{{ __('messages.3_months') }}</option>
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
    <script>
        
    </script>
    @push('scripts')
        <script>
            // window.translations = {
            //     // Messages
            //     search: "{{ __('messages.search') }}",
            //     searching: "{{ __('messages.searching') }}",
            //     vehicle_data_loaded: "{{ __('messages.vehicle_data_loaded') }}",
            //     owner_data_loaded: "{{ __('messages.owner_data_loaded') }}",
            //     applicant_data_loaded: "{{ __('messages.applicant_data_loaded') }}",
            //     owner_copied_as_applicant: "{{ __('messages.owner_copied_as_applicant') }}",
            //     applicant_fields_cleared: "{{ __('messages.applicant_fields_cleared') }}",
            //     policy_calculated: "{{ __('messages.policy_calculated') }}",
            //     next_step_clicked: "{{ __('messages.next_step_clicked') }}",
            //     add_driver_soon: "{{ __('messages.add_driver_soon') }}",
            //     load_owner_first: "{{ __('messages.load_owner_first') }}",

            //     // Errors
            //     all_fields_required: "{{ __('messages.all_fields_required') }}",
            //     pinfl_mismatch: "{{ __('messages.pinfl_mismatch') }}",
            //     api_error: "{{ __('messages.api_error') }}",
            //     request_error: "{{ __('messages.request_error') }}",
            //     policy_calculation_error: "{{ __('messages.policy_calculation_error') }}",
            //     select_policy_start_date: "{{ __('messages.select_policy_start_date') }}",
            //     calculate_policy_first: "{{ __('messages.calculate_policy_first') }}",

            //     // Validation
            //     validation_3_letters: "{{ __('messages.validation_3_letters') }}",
            //     validation_7_digits: "{{ __('messages.validation_7_digits') }}",
            //     validation_year_range: "{{ __('messages.validation_year_range') }}",

            //     // Period text
            //     period_1_year: "{{ __('messages.1_year') }}",
            //     period_6_months: "{{ __('messages.6_months') }}",
            //     period_3_months: "{{ __('messages.3_months') }}",

            //     // Currency
            //     sum: "{{ __('messages.sum') }}"
            // };
        </script>
        @vite(['resources/js/pages/insurence/main.js'])
    @endpush
@endsection
