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

                                <x-inputs.input_form :type="'text'" :class="'col-md-3'" :idFor="'gov_number'" :name="'gov_number'"
                                    :placeholder="'messages.gov_number_placeholder'" :label="'messages.gov_number'" />

                                <x-inputs.input_form :type="'text'" :class="'col-md-2'" :idFor="'tech_passport_series'" :name="'tech_passport_series'"
                                    :placeholder="'messages.tech_passport_series_placeholder'" :label="'messages.tech_passport_series'" />

                                <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'tech_passport_number'"
                                    :name="'tech_passport_number'" :placeholder="'messages.tech_passport_number_placeholder'" :label="'messages.tech_passport_number'" />

                                <x-inputs.button :class="'col-md-3'" :button="'vehicle-search-btn'" />

                            </div>
                        </div>

                        <div id="vehicle-info-display" class="card-footer d-none">
                            <div class="row">

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'model'" :name="'model'"
                                    :label="'messages.model'" :placeholder="'messages.model_placeholder'" />

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

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'engine_number'" :name="'engine_number'"
                                    :label="'messages.engine_number'" :placeholder="'messages.engine_number_placeholder'" />

                            </div>
                        </div>

                    </div>

                    <div id="owner-info" class="card d-none">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('messages.owner_info_title') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <x-inputs.input_form :type="'text'" :class="'col-md-2'" :idFor="'insurance-passport-series'"
                                    :name="'passport_series'" :placeholder="'messages.passport_series_placeholder'" :label="'messages.passport_series'" />

                                <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'insurance-passport-number'"
                                    :name="'passport_number'" :placeholder="'messages.passport_number_placeholder'" :label="'messages.passport_number'" />

                                <x-inputs.input_form :type="'number'" :class="'col-md-4'" :idFor="'insurance-pinfl'"
                                    :name="'pinfl'" :placeholder="'messages.owner_pinfl_placeholder'" :label="'messages.owner_pinfl'" :disabled="true" />

                                <x-inputs.button :class="'col-md-2'" :button="'owner-information-search-btn'" />

                            </div>

                            <div class="row d-none" id="insurance-driver-full-information">

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'insurance-last-name'" :name="'last_name'"
                                    :label="'messages.owner_last_name'" :placeholder="'messages.owner_last_name_placeholder'" />

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'insurance-first-name'" :name="'first_name'"
                                    :label="'messages.owner_first_name'" :placeholder="'messages.owner_first_name_placeholder'" />

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'insurance-middle-name'" :name="'middle_name'"
                                    :label="'messages.owner_middle_name'" :placeholder="'messages.owner_middle_name_placeholder'" />

                                <input type="hidden" id="owner-address" name="owner_address">
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
                                    <x-inputs.input_form :type="'text'" :class="'col-md-2'" :idFor="'applicant-passport-series'"
                                        :name="'applicant_passport_series'" :placeholder="'messages.applicant_passport_series_placeholder'" :label="'messages.applicant_passport_series'" />

                                    <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'applicant-passport-number'"
                                        :name="'applicant_passport_number'" :placeholder="'messages.applicant_passport_number_placeholder'" :label="'messages.applicant_passport_number'" />

                                    <x-inputs.input_form :type="'number'" :class="'col-md-4'" :idFor="'applicant-pinfl'"
                                        :name="'applicant_pinfl'" :placeholder="'messages.applicant_pinfl_placeholder'" :label="'messages.applicant_pinfl'" />

                                    <x-inputs.button :class="'col-md-2'" :button="'applicant-information-search-btn'" />
                                </div>
                            </div>

                            <div id="applicant-info-display" class="d-none">
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-last-name'" :name="'applicant_last_name'"
                                        :label="'messages.applicant_last_name'" :placeholder="'last_name_placeholder'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-first-name'" :name="'applicant_first_name'"
                                        :label="'messages.applicant_first_name'" :placeholder="'first_name_placeholder'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-middle-name'" :name="'applicant_middle_name'"
                                        :label="'messages.applicant_middle_name'" :placeholder="'middle_name_placeholder'" />
                                </div>

                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-6'" :idFor="'applicant-address'" :name="'applicant_address'"
                                        :label="'messages.applicant_address'" :placeholder="'address_placeholder'" />

                                    <x-inputs.input_form :type="'number'" :class="'col-md-6'" :idFor="'applicant-phone-number'"
                                        :name="'applicant_phone_number'" :label="'messages.applicant_phone_number'" :placeholder="'messages.applicant_phone_number_placeholder'" />
                                </div>
                            </div>

                        </div>
                    </div>

                    <div id="policy-calculation" class="card d-none">
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
                                            <option value="1">{{ __('messages.1_year') }}</option>
                                            <option value="0.7">{{ __('messages.6_months') }}</option>
                                            <option value="0.4">{{ __('messages.3_months') }}</option>
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
                                        <i class="bi bi-calculator"></i>
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
        document.addEventListener('DOMContentLoaded', function() {

            let regionIdC;
            let periodC;
            let vehicleTypeC;
            let limitedC;

            const searchBtn = document.getElementById('vehicle-search-btn');
            const ownerInfoBtn = document.getElementById('owner-information-search-btn');
            const applicantInfoCheck = document.getElementById('is-applicant-owner');
            const applicantInfoBtn = document.getElementById('applicant-information-search-btn');

            const startInput = document.getElementById('policy_start_date');
            const endInput = document.getElementById('policy_end_date');
            const periodSelect = document.getElementById('insurance_period');

            function updateEndDate() {
                // If no start date, clear end date
                if (!startInput.value) {
                    endInput.value = '';
                    return;
                }

                // Get the start date
                const startDate = new Date(startInput.value);

                // Determine how many months to add based on selected period
                const monthsMap = {
                    '1': 12,
                    '0.7': 6,
                    '0.4': 3
                };
                const monthsToAdd = monthsMap[periodSelect.value] || 12;
                periodC = monthsToAdd;

                // Calculate end date: add months, then subtract 1 day
                const endDate = new Date(startDate);
                endDate.setMonth(endDate.getMonth() + monthsToAdd);
                endDate.setDate(endDate.getDate() - 1);

                // Format as YYYY-MM-DD for the input field
                endInput.value = endDate.toISOString().split('T')[0];

                calculatePolicy();
            }

            // Update end date when start date or period changes
            startInput.addEventListener('change', updateEndDate);
            periodSelect.addEventListener('change', updateEndDate);

            searchBtn.addEventListener('click', async function() {
                const govNumber = document.getElementById('gov_number').value;
                const techPassportSeries = document.getElementById('tech_passport_series').value;
                const techPassportNumber = document.getElementById('tech_passport_number').value;

                // Validate inputs
                if (!govNumber || !techPassportSeries || !techPassportNumber) {
                    alert('Please fill in all fields');
                    return;
                }

                // Prepare data
                const data = {
                    gov_number: govNumber,
                    tech_passport_series: techPassportSeries,
                    tech_passport_number: techPassportNumber
                };

                // Disable button during request
                searchBtn.disabled = true;
                searchBtn.innerHTML = '<span>Loading...</span>';

                try {

                    const result = await sendPostRequest('/get-vehicle-info', data);

                    if (result.data != null) {
                        // Get all input elements
                        const engine_number = document.getElementById('engine_number');
                        let carType = document.getElementById('car_type');
                        const carYear = document.getElementById('car_year');
                        const registrationRegion = document.getElementById('registration_region');
                        const carOwner = document.getElementById('car_owner');
                        const model = document.getElementById('model');
                        const insurantPinfl = document.getElementById('insurance-pinfl');

                        // Populate the fields
                        engine_number.value = result.data.result.engineNumber || '';
                        carYear.value = result.data.result.issueYear || '';
                        registrationRegion.value = result.data.result.division || '';
                        carOwner.value = result.data.result.owner || '';
                        model.value = result.data.result.modelName || '';
                        insurantPinfl.value = result.data.result.pinfl || '';
                        switch (result.data.result.vehicleTypeId) {
                            case 2:
                                vehicleTypeC = 0.1;
                                carType.value = '@lang('insurance.car_type_2')';
                                break;
                            case 6:
                                vehicleTypeC = 0.12;
                                carType.value = '@lang('insurance.car_type_6')';
                                break;
                            case 9:
                                vehicleTypeC = 0.12;
                                carType.value = '@lang('insurance.car_type_9')';
                                break;
                            case 15:
                                vehicleTypeC = 0.04;
                                carType.value = '@lang('insurance.car_type_15')';
                                break;
                            default:
                                carType.value = '@lang('insurance.car_not_found')';
                                break;
                        }

                        // Show the vehicle info display (CORRECTED)
                        const vehicleInfoDisplay = document.getElementById('vehicle-info-display');
                        vehicleInfoDisplay.classList.remove('d-none');

                        const vehicleOwnerInfo = document.getElementById('owner-info');
                        vehicleOwnerInfo.classList.remove('d-none');

                        // Optional: Scroll to the displayed info
                        vehicleInfoDisplay.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                    } else {
                        alert(result.message.error.error_message);
                    }

                    console.log('Vehicle Info:', result);

                } catch (error) {
                    console.error('Error:', error);
                    alert('Error: ' + error.message);
                } finally {
                    // Re-enable button
                    searchBtn.disabled = false;
                    searchBtn.innerHTML =
                        '<svg width="20" height="20"><use xlink:href="#icon-search"></use></svg>';
                }
            });

            ownerInfoBtn.addEventListener('click', async function() {
                const insurantPassportSeries = document.getElementById('insurance-passport-series')
                    .value;
                const insurantPassportNumber = document.getElementById('insurance-passport-number')
                    .value;
                const insurantPinfl = document.getElementById('insurance-pinfl').value;

                // Validate required fields
                if (!insurantPassportSeries || !insurantPassportNumber || !insurantPinfl) {
                    alert('Please fill in all fields');
                    return;
                }

                ownerInfoBtn.disabled = true;
                ownerInfoBtn.innerHTML = '<span>Loading...</span>';

                const data = {
                    senderPinfl: insurantPinfl,
                    passport_series: insurantPassportSeries,
                    passport_number: insurantPassportNumber,
                    pinfl: insurantPinfl,
                    isConsent: "Y"
                };
                try {
                    const result = await sendPostRequest('/get-person-info', data);

                    if (result.data != null) {
                        // Get all input elements
                        const lastName = document.getElementById('insurance-last-name');
                        const firstName = document.getElementById('insurance-first-name');
                        const middleName = document.getElementById('insurance-middle-name');
                        const address = document.getElementById('owner-address');

                        // // Populate the fields
                        lastName.value = result.data.result.lastNameLatin || '';
                        firstName.value = result.data.result.firstNameLatin || '';
                        middleName.value = result.data.result.middleNameLatin || '';
                        address.value = result.data.result.address || '';

                        // Show the vehicle info display (CORRECTED)
                        const insuranceDriverFullInformation = document.getElementById(
                            'insurance-driver-full-information');
                        insuranceDriverFullInformation.classList.remove('d-none');

                        const applicantInfo = document.getElementById('applicant-info');
                        applicantInfo.classList.remove('d-none');

                        // Optional: Scroll to the displayed info
                        insuranceDriverFullInformation.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                    } else {
                        alert(result.message.error.error_message);
                    }

                    console.log('Person Info:', result);

                } catch (error) {
                    console.error('Error:', error);
                    alert('Error: ' + error.message);
                } finally {
                    // Re-enable button
                    ownerInfoBtn.disabled = false;
                    ownerInfoBtn.innerHTML =
                        '<svg width="20" height="20"><use xlink:href="#icon-search"></use></svg>';
                }
            });

            applicantInfoCheck.addEventListener('change', function() {

                const applicantLastName = document.getElementById('applicant-last-name');
                const applicantFirstName = document.getElementById('applicant-first-name');
                const applicantMiddleName = document.getElementById('applicant-middle-name');
                const applicantAddress = document.getElementById('applicant-address');
                const lastName = document.getElementById('insurance-last-name');
                const firstName = document.getElementById('insurance-first-name');
                const middleName = document.getElementById('insurance-middle-name');
                const ownerAddress = document.getElementById('owner-address');

                if (applicantInfoCheck.checked) {
                    const applicantInfoSearch = document.getElementById('applicant-info-search');
                    applicantInfoSearch.classList.toggle('d-none');

                    const applicantInfoDisplay = document.getElementById('applicant-info-display');
                    applicantInfoDisplay.classList.remove('d-none');

                    const policyCalculation = document.getElementById('policy-calculation');
                    policyCalculation.classList.remove('d-none');

                    applicantLastName.value = lastName.value || '';
                    applicantFirstName.value = firstName.value || '';
                    applicantMiddleName.value = middleName.value || '';
                    applicantAddress.value = ownerAddress.value || '';

                } else {
                    const applicantInfoSearch = document.getElementById('applicant-info-search');
                    applicantInfoSearch.classList.remove('d-none');

                    const applicantInfoDisplay = document.getElementById('applicant-info-display');
                    applicantInfoDisplay.classList.toggle('d-none');

                    const policyCalculation = document.getElementById('policy-calculation');
                    policyCalculation.classList.toggle('d-none');

                    const insurantPassportSeries = document.getElementById('applicant-passport-series');
                    const insurantPassportNumber = document.getElementById('applicant-passport-number');
                    const insurantPinfl = document.getElementById('applicant-pinfl');

                    insurantPassportSeries.value = '';
                    insurantPassportNumber.value = '';
                    insurantPinfl.value = '';
                }
            })

            applicantInfoBtn.addEventListener('click', async function() {
                const insurantPassportSeries = document.getElementById('applicant-passport-series')
                    .value;
                const insurantPassportNumber = document.getElementById('applicant-passport-number')
                    .value;
                const insurantPinfl = document.getElementById('applicant-pinfl').value;

                // Validate required fields
                if (!insurantPassportSeries || !insurantPassportNumber || !insurantPinfl) {
                    alert('Please fill in all fields');
                    return;
                }

                applicantInfoBtn.disabled = true;
                applicantInfoBtn.innerHTML = '<span>Loading...</span>';

                const data = {
                    senderPinfl: insurantPinfl,
                    passport_series: insurantPassportSeries,
                    passport_number: insurantPassportNumber,
                    pinfl: insurantPinfl,
                    isConsent: "Y"
                };
                try {
                    const result = await sendPostRequest('/get-person-info', data);

                    if (result.data != null) {
                        const policyCalculation = document.getElementById('policy-calculation');
                        policyCalculation.classList.remove('d-none');
                        // Get all input elements
                        const lastName = document.getElementById('applicant-last-name');
                        const firstName = document.getElementById('applicant-first-name');
                        const middleName = document.getElementById('applicant-middle-name');
                        const address = document.getElementById('applicant-address');

                        // // Populate the fields
                        lastName.value = result.data.result.lastNameLatin || '';
                        firstName.value = result.data.result.firstNameLatin || '';
                        middleName.value = result.data.result.middleNameLatin || '';
                        address.value = result.data.result.address || '';

                        // Show the vehicle info display (CORRECTED)
                        const applicantInfo = document.getElementById('applicant-info-display');
                        applicantInfo.classList.remove('d-none');
                    } else {
                        alert(result.message.error.error_message);
                    }

                } catch (error) {
                    console.error('Error:', error);
                    alert('Error: ' + error.message);
                } finally {
                    // Re-enable button
                    applicantInfoBtn.disabled = false;
                    applicantInfoBtn.innerHTML =
                        '<svg width="20" height="20"><use xlink:href="#icon-search"></use></svg>';
                }

            });

            async function sendPostRequest(url, data) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')
                    .getAttribute('content');
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken, // make sure csrfToken is globally available
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Request failed');
                    }

                    return result; // return the successful result
                } catch (error) {
                    console.error('Fetch error:', error);
                    throw error; // let the caller handle it
                }
            }

            function calculatePolicy() {

                var govNumber = document.getElementById('gov_number').value.trim();
                govNumber = govNumber.substring(0, 2);
                const periodSelect = document.getElementById('insurance_period');
                periodC = periodSelect.value;
                let limitedC = typeof window.limitedC !== 'undefined' ? limitedC : 1;

                if (govNumber == '01' || govNumber == '10') {
                    regionIdC = 1.4;
                } else {
                    regionIdC = 1.2;
                }

                let insuranceAmount = 40000000;
                let calcDiscount = vehicleTypeC * regionIdC * periodC * limitedC;

                let amount = (calcDiscount * insuranceAmount) / 100;

                console.log('amount', amount);
                if (isNaN(amount) || amount === 0) {
                    amount = 168000;
                }

                document.getElementById('amount').innerHTML = amount.toLocaleString('en-US', {
                    minimumFractionDigits: 2
                })

            }

        });
    </script>

    {{-- @push('scripts')
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
    @endpush --}}
@endsection
