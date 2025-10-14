@extends('layouts.app')
@section('title', 'OSAGO')

@push('styles')
    <link href="{{ asset('assets/css/form.css') }}" rel="stylesheet">
@endpush

@section('content')
    <x-insurence.steps />
    <section class="container-fluid product-page py-4" id="osago-main" style="">
        <div class="container">
            <form id="policy-calculation-form" method="POST" action="{{ route('osago.calculation', app()->getLocale()) }}">
                @csrf
                <div class="row g-4">
                    <div class="col-md-8">
                        <div id="vehicle-info" class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('messages.vehicle_info_title') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">

                                    <x-inputs.input_form :type="'text'" :class="'col-md-3'" :idFor="'gov_number'"
                                        :name="'gov_number'" :placeholder="'messages.gov_number_placeholder'" :label="'messages.gov_number'" />

                                    <x-inputs.input_form :type="'text'" :class="'col-md-3'" :idFor="'tech_passport_series'"
                                        :name="'tech_passport_series'" :placeholder="'messages.tech_passport_series_placeholder'" :label="'messages.tech_passport_series'" />

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

                                    <input type="hidden" name="other_info" id="other_info">

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

                                    <x-inputs.button :class="'col-md-3'" :button="'owner-information-search-btn'" />

                                </div>
                            </div>

                            <div id="insurance-driver-full-information" class="card-footer d-none">
                                <div class="row">

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'insurance-last-name'" :name="'last_name'"
                                    :label="'messages.last_name'" :placeholder="'messages.last_name_placeholder'" />

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'insurance-first-name'" :name="'first_name'"
                                    :label="'messages.first_name'" :placeholder="'messages.first_name_placeholder'" />

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'insurance-middle-name'" :name="'middle_name'"
                                    :label="'messages.middle_name'" :placeholder="'messages.middle_name_placeholder'" />

                                    <input type="hidden" id="owner-address" name="owner_address">
                                    <input type="hidden" id="owner-infos" name="owner_infos">
                                </div>
                            </div>
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div id="applicant-info" class="card d-none">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('messages.applicant_info_title') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-check ">
                                    <input class="form-check-input" type="checkbox" id="is-applicant-owner"
                                        name="is_applicant_owner">
                                    <label class="form-check-label" for="is-applicant-owner">
                                        {{ __('messages.is_applicant_owner') }}
                                    </label>
                                </div>

                            <div id="applicant-info-search">
                                <div class="row">
                                    <x-inputs.input_form :type="'text'" :class="'col-md-2'" :idFor="'applicant-passport-series'"
                                        :name="'applicant_passport_series'" :placeholder="'messages.passport_series_placeholder'" :label="'messages.passport_series'" />

                                    <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'applicant-passport-number'"
                                        :name="'applicant_passport_number'" :placeholder="'messages.passport_number_placeholder'" :label="'messages.passport_number'" />

                                        <x-inputs.input_form :type="'number'" :class="'col-md-4'" :idFor="'applicant-pinfl'"
                                            :name="'applicant_pinfl'" :placeholder="'messages.applicant_pinfl_placeholder'" :label="'messages.applicant_pinfl'" />

                                        <x-inputs.button :class="'col-md-3'" :button="'applicant-information-search-btn'" />
                                    </div>
                                </div>

                        </div>
                        <div id="applicant-info-display" class="card-footer d-none">
                            <div class="row">
                                <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-last-name'" :name="'applicant_last_name'"
                                    :label="'messages.last_name'" :placeholder="'messages.last_name_placeholder'" />

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-first-name'" :name="'applicant_first_name'"
                                    :label="'messages.first_name'" :placeholder="'messages.first_name_placeholder'" />

                                <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-middle-name'" :name="'applicant_middle_name'"
                                    :label="'messages.middle_name'" :placeholder="'messages.middle_name_placeholder'" />
                                <input type="hidden" name="applicant_infos" id="applicant-infos">
                            </div>

                            <div class="row">
                                <x-inputs.input_info :class="'col-md-6'" :idFor="'applicant-address'" :name="'applicant_address'"
                                    :label="'messages.address'" :placeholder="'messages.address_placeholder'" />

                                <x-inputs.input_form :type="'number'" :class="'col-md-6'" :idFor="'applicant-phone-number'"
                                    :name="'applicant_phone_number'" :label="'messages.phone_number'" :placeholder="'messages.phone_number_placeholder'" />
                            </div>
                        </div>

                        </div>

                        <div id="policy-calculation" class="card d-none">
                            <div class="card-header">
                                <h4>{{ __('messages.policy_calculation') }}</h4>
                            </div>
                            <div class="card-body">
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
                                        <select class="form-select" id="insurance_period" name="insurance_period" required
                                            readonly>
                                            <option value="1" selected>{{ __('messages.1_year') }}</option>
                                            <option value="0.7">{{ __('messages.6_months') }}</option>
                                            <option value="0.4">{{ __('messages.3_months') }}</option>
                                        </select>
                                        <input type="hidden" id="insurance-infos" name="insurance_infos">
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
                                                id="driver_limited" value="limited">
                                            <label class="form-check-label" for="driver_limited">
                                                {{ __('messages.limited_drivers') }}
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="driver_limit"
                                                id="driver_unlimited" value="unlimited" checked>
                                            <label class="form-check-label" for="driver_unlimited">
                                                {{ __('messages.unlimited_drivers') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit button -->
                                <div class="d-flex justify-content-end gap-2">
                                    <button class="btn btn-primary-custom" id="calculate-policy-btn">
                                        <i class="bi bi-calculator"></i>
                                    </button>
                                </div>
                            </div>
                        </div>


                        <div id="limited-drivers-info" class="card">
                            <!--d-none qo'shish kerak!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!-->
                            <div class="card-header">
                                <h4 class="card-title">{{ __('messages.applicant_info_title') }}</h4>
                            </div>
                            <div class="card-body">
                                <!--Driver info if chooses limited drivers-->
                                <div id="driver-info-search">
                                    <div class="row">
                                        <x-inputs.input_form :type="'text'" :class="'col-md-2'" :idFor="'driver-passport-series'"
                                            :name="'driver_passport_series'" :placeholder="'messages.passport_series_placeholder'" :label="'messages.passport_series'" />

                                        <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'driver-passport-number'"
                                            :name="'driver_passport_number'" :placeholder="'messages.passport_number_placeholder'" :label="'messages.passport_number'" />

                                        <x-inputs.input_form :type="'number'" :class="'col-md-4'" :idFor="'driver-pinfl'"
                                            :name="'driver_pinfl'" :placeholder="'messages.owner_pinfl_placeholder'" :label="'messages.owner_pinfl'" />

                                        <x-inputs.button :class="'col-md-3'" :button="'driver-information-search-btn'" />
                                    </div>
                                </div>
                            </div>

                            <div id="driver-info-display" class="">
                                {{-- <div class="card-footer">
                                    <h4 class="card-title">{{ __('messages.driver_info_title') }}</h4>
                                    <div class="row mb-1">
                                        <x-inputs.input_info :class="'col-md-6'" :idFor="'driver-full-name'" :name="'driver_full_name'"
                                            :label="'messages.driver_full_name'" :placeholder="'messages.driver_full_name_placeholder'" />
                                        <x-inputs.input_info :class="'col-md-3'" :idFor="'driver-license'" :name="'driver_license'"
                                            :label="'messages.driver_license'" :placeholder="'messages.driver_license_placeholder'" />
                                        <x-inputs.input_info :class="'col-md-3'" :idFor="'driver-license-valid'" :name="'driver_license_valid'"
                                            :label="'messages.driver_license_valid'" :placeholder="'messages.driver_license_valid_placeholder'" />
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="{{ __('messages.kinship') }}" class="form-label">
                                                {{ __('messages.kinship') }}
                                            </label>
                                            <select class="form-select" id="kinship" name="kinship" required>
                                                <option value="0" selected>@lang('messages.vehicle_owner')</option>
                                                <option value="0">@lang('messages.not_a_relative')</option>
                                                <option value="1">@lang('messages.father')</option>
                                                <option value="2">@lang('messages.mother')</option>
                                                <option value="3">@lang('messages.husband')</option>
                                                <option value="4">@lang('messages.wife')</option>
                                                <option value="5">@lang('messages.son')</option>
                                                <option value="6">@lang('messages.daughter')</option>
                                                <option value="7">@lang('messages.older_brother')</option>
                                                <option value="8">@lang('messages.younger_brother')</option>
                                                <option value="9">@lang('messages.elder_sister')</option>
                                                <option value="10">@lang('messages.younger_sister')</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 d-flex justify-content-end">
                                            <x-inputs.button :class="''" :button="'driver-information-search-btn'" />
                                        </div>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                    <x-insurence.calculate />
                </div>
            </form>
        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let regionIdC;
            let periodC;
            let vehicleTypeC;
            let limitedC = 3;
            let driverIdCounter = 0;

            const searchBtn = document.getElementById('vehicle-search-btn');
            const ownerInfoBtn = document.getElementById('owner-information-search-btn');
            const applicantInfoCheck = document.getElementById('is-applicant-owner');
            const applicantInfoBtn = document.getElementById('applicant-information-search-btn');

            const startInput = document.getElementById('policy_start_date');
            const endInput = document.getElementById('policy_end_date');
            const periodSelect = document.getElementById('insurance_period');
            const driverSearchBtn = document.getElementById('driver-information-search-btn');

            // Update end date when start date or period changes
            startInput.addEventListener('change', updateEndDate);
            periodSelect.addEventListener('change', updateEndDate);

            searchBtn.addEventListener('click', async function() {
                const govNumber = document.getElementById('gov_number');
                const techPassportSeries = document.getElementById('tech_passport_series');
                const techPassportNumber = document.getElementById('tech_passport_number');
                let otherInfo = document.getElementById('other_info');

                // Validate inputs
                if (!govNumber || !techPassportSeries || !techPassportNumber) {
                    alert('Please fill in all fields');
                    return;
                }

                // Prepare data
                const data = {
                    gov_number: govNumber.value,
                    tech_passport_series: techPassportSeries.value,
                    tech_passport_number: techPassportNumber.value
                };
                govNumber.setAttribute('readonly', true);
                techPassportSeries.setAttribute('readonly', true);
                techPassportNumber.setAttribute('readonly', true);

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
                        otherInfo = {
                            'techPassportIssueDate': result.data.result.techPassportIssueDate.split(
                                "T")[0],
                            'typeId': result.data.result.vehicleTypeId,
                            'bodyNumber': result.data.result.bodyNumber,
                        };

                        document.getElementById('other_info').value = JSON.stringify(otherInfo);

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
                    console.log('Person Info:', result);
                    if (result.data != null) {
                        // Get all input elements
                        const lastName = document.getElementById('insurance-last-name');
                        const firstName = document.getElementById('insurance-first-name');
                        const middleName = document.getElementById('insurance-middle-name');
                        const address = document.getElementById('owner-address');
                        const ownerInfos = document.getElementById('owner-infos');
                        let ownerInfo = getNececcaryInfo(result);
                        console.log(ownerInfo);
                        ownerInfos.value = JSON.stringify(ownerInfo);
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
                    console.log('Person Info:', result);
                    if (result.data != null) {
                        const policyCalculation = document.getElementById('policy-calculation');
                        policyCalculation.classList.remove('d-none');
                        // Get all input elements
                        const lastName = document.getElementById('applicant-last-name');
                        const firstName = document.getElementById('applicant-first-name');
                        const middleName = document.getElementById('applicant-middle-name');
                        const address = document.getElementById('applicant-address');
                        let applicantInfos = document.getElementById('applicant-infos');

                        // // Populate the fields
                        lastName.value = result.data.result.lastNameLatin || '';
                        firstName.value = result.data.result.firstNameLatin || '';
                        middleName.value = result.data.result.middleNameLatin || '';
                        address.value = result.data.result.address || '';

                        let applicantInfoResult = getNececcaryInfo(result);
                        applicantInfos.value = JSON.stringify(applicantInfoResult);

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

            function calculatePolicy() {

                var govNumber = document.getElementById('gov_number').value.trim();
                govNumber = govNumber.substring(0, 2);
                let periodSelect = document.getElementById('insurance_period');
                periodC = periodSelect.value;

                if (govNumber == '01' || govNumber == '10') {
                    regionIdC = 1.4;
                } else {
                    regionIdC = 1.2;
                }

                let insuranceAmount = 40000000;
                let calcDiscount = vehicleTypeC * regionIdC * periodC * limitedC;

                let amount = (calcDiscount * insuranceAmount) / 100;

                // console.log('amount', amount,'period', periodC,'limitedC', limitedC,'regionId', regionIdC,'vehicleType', vehicleTypeC);
                if (isNaN(amount) || amount === 0) {
                    amount = 168000;
                }
                document.getElementById('insurance-infos').value = JSON.stringify({
                    "amount": amount.toLocaleString('en-US', {
                        minimumFractionDigits: 2
                    }),
                    "period": periodC,
                    "insuranceAmount": insuranceAmount
                })

                document.getElementById('amount').innerHTML = amount.toLocaleString('en-US', {
                    minimumFractionDigits: 2
                })
                document.getElementById('premium').innerHTML = insuranceAmount.toLocaleString('en-US', {
                    minimumFractionDigits: 2
                })

            }

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

            const unlimitedRadio = document.getElementById('driver_unlimited');
            const limitedDriver = document.getElementById('driver_limited');

            unlimitedRadio.addEventListener('change', () => showDriverAddButton(
                'unlimited'));
            limitedDriver.addEventListener('change', () => showDriverAddButton(
                'limited'));

            function showDriverAddButton(radio) {
                // const addButton = document.getElementById('driver-add-button');

                if (radio === 'limited') {
                    // addButton.classList.remove('d-none'); // show the button
                    periodSelect.removeAttribute('disabled');
                    limitedC = 1;
                } else if (radio === 'unlimited') { // hide the button
                    periodSelect.value = 1;
                    periodSelect.toggleAttribute('disabled');
                    limitedC = 3;
                }
                calculatePolicy();
            }

            driverSearchBtn.addEventListener('click', async function() {
                let driverPassportSeries = document.getElementById('driver-passport-series')
                    .value;
                let driverPassportNumber = document.getElementById('driver-passport-number')
                    .value;
                let driverPinfl = document.getElementById('driver-pinfl').value;

                const driverInfo = document.getElementById('driver-info-display');

                if (!driverPassportSeries || !driverPassportNumber || !driverPinfl) {
                    alert('Заполните данные водителя');
                    return;
                }

                const driverData = {
                    passport_series: driverPassportSeries,
                    passport_number: driverPassportNumber,
                    pinfl: driverPinfl,
                };
                const data = {
                    senderPinfl: driverPinfl,
                    passport_series: driverPassportSeries,
                    passport_number: driverPassportNumber,
                    pinfl: driverPinfl,
                    isConsent: "Y"
                };

                driverSearchBtn.disabled = true;
                driverSearchBtn.innerHTML = '<span>Loading...</span>';

                try {
                    const result = await sendPostRequest('/get-driver-info', driverData);
                    console.log('Driver Info:', result);

                    const shortResult = result.data.result;

                    const driverInfoDisplay = document.getElementById('driver-info-display');

                    const existing = driverInfoDisplay.querySelectorAll('.card-footer').length;
                    if (result.success && existing < 5) {

                        document.getElementById('driver-passport-series').value = '';
                        document.getElementById('driver-passport-number').value = '';
                        document.getElementById('driver-pinfl').value = '';

                        const existing = driverInfoDisplay.querySelectorAll('.card-footer')
                            .length;
                        console.log('existing', existing);


                        if (existing < 5) {
                            // Increment the counter to get a unique ID
                            driverIdCounter++;
                            const uniqueId = driverIdCounter;

                            const result1 = await sendPostRequest('/get-person-info', data);
                            let driverSelfInfo = {
                                'pinfl': driverData.pinfl,
                                'seria': driverData.passport_series,
                                'number': driverData.passport_number,
                                'issuedBy': result1.data.result.issuedBy,
                                'issueDate': result1.data.result.startDate,
                                'firstname': result1.data.result.firstNameLatin,
                                'lastname': result1.data.result.lastNameLatin,
                                'middlename': result1.data.result.middleNameLatin,
                                'licenseNumber': result.data.result.DriverInfo.licenseNumber,
                                'licenseSeria': result.data.result.DriverInfo.licenseSeria,
                                'birthDate': result.data.result.DriverInfo.pOwnerDate,
                                'licenseIssueDate': result.data.result.DriverInfo.issueDate.split(
                                    "T")[0],
                            }
                            let string = JSON.stringify(driverSelfInfo).replace(/"/g, '&quot;').replace(
                                /'/g, '&#39;');
                            // Calculate display number (1-5 based on current count)

                            const driverHtml = `
                                <div class="card-footer mb-3" data-id="${uniqueId}">
                                    <h4 class="card-title">@lang('messages.driver_info_title')</h4>
                                
                                    <div class="row mb-2">
                                        <div class="col-md-5">
                                            <label for="driver-${uniqueId}-full-name" class="form-label">@lang('messages.driver_full_name')</label>
                                            <input type="text" class="form-input" id="driver-${uniqueId}-full-name" name="driver_full_name[${uniqueId}]" value="${shortResult.DriverInfo.pOwner.split(' ')[0] + ' ' + shortResult.DriverInfo.pOwner.split(' ')[1]}" readonly />
                                            <input type="hidden" name="driver_full_info[${uniqueId}]" value="${string}">    
                                        </div>
                                        <div class="col-md-4">
                                            <label for="driver-${uniqueId}-kinship" class="form-label">
                                                @lang('messages.kinship')
                                            </label>
                                            <select class="form-select" id="driver-${uniqueId}-kinship" name="kinship[${uniqueId}]"
                                                required>
                                                <option value="0" selected>@lang('messages.vehicle_owner')</option>
                                                <option value="0">@lang('messages.not_a_relative')</option>
                                                <option value="1">@lang('messages.father')</option>
                                                <option value="2">@lang('messages.mother')</option>
                                                <option value="3">@lang('messages.husband')</option>
                                                <option value="4">@lang('messages.wife')</option>
                                                <option value="5">@lang('messages.son')</option>
                                                <option value="6">@lang('messages.daughter')</option>
                                                <option value="7">@lang('messages.older_brother')</option>
                                                <option value="8">@lang('messages.younger_brother')</option>
                                                <option value="9">@lang('messages.elder_sister')</option>
                                                <option value="10">@lang('messages.younger_sister')</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex flex-column align-items-end justify-content-end">
                                            <button type="button" id="delete-${uniqueId}" class="btn btn-icon btn-danger btn-sm" data-target="${uniqueId}">
                                            <span class="text-danger">Delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;

                            driverInfoDisplay.innerHTML += driverHtml;
                        } else {
                            alert('You can add only 5 drivers');
                        }
                    } else {
                        if (result?.message?.error?.error_code) {
                            alert(result?.message?.error?.error_message || 'Driver not found');
                        } else {
                            alert('You can add only 5 person')
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error: ' + (error.message || 'Something went wrong'));
                } finally {
                    driverSearchBtn.disabled = false;
                    driverSearchBtn.innerHTML =
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

            function escapeHtml(str = '') {
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-danger[data-target]');
                if (button) {
                    const targetId = button.getAttribute('data-target');
                    if (confirm('Haydovchini o\'chirmoqchimisiz?')) {
                        const card = document.querySelector('.card-footer[data-id="' + targetId +
                            '"]');
                        if (card) {
                            card.remove();
                        }
                    }
                }
            });

            function getNececcaryInfo(result) {
                return {
                    'regionId': result.data.result.regionId,
                    'districtId': result.data.result.districtId,
                    'issuedBy': result.data.result.issuedBy,
                    'issueDate': result.data.result.startDate,
                    'gender': result.data.result.gender,
                    'birthDate': result.data.result.birthDate,
                    'address': result.data.result.address || ''
                };
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
