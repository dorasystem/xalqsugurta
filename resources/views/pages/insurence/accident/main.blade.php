@extends('layouts.app')
@section('title', 'OSAGO')

@push('styles')
    <link href="{{ asset('assets/css/form.css') }}" rel="stylesheet">
@endpush

@section('content')
    <x-insurence.steps />

    {{-- validation errorlarni chiqarish --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="container-fluid product-page py-4" id="accident-main" style="">
        <div class="container">
            <form action="{{ route('accident.application', ['locale' => getCurrentLocale()]) }}" method="post">
                @csrf
                <div class="row g-4">
                    <div class="col-md-8">
                        <div id="applicant-info" class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('messages.applicant_info_title') }}</h4>
                            </div>
                            <div class="card-body">
                                <div id="applicant-info-search">
                                    <div class="row align-items-start">
                                        <x-inputs.input_form :type="'text'" :class="'col-md-2 ?>'" :idFor="'applicant-passport-series'"
                                            :name="'applicant_passport_series'" :placeholder="'messages.applicant_passport_series_placeholder'" :label="'messages.applicant_passport_series'" />

                                        <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'applicant-passport-number'"
                                            :name="'applicant_passport_number'" :placeholder="'messages.applicant_passport_number_placeholder'" :label="'messages.applicant_passport_number'" />

                                        <x-inputs.input_form :type="'date'" :class="'col-md-4'" :idFor="'applicant-birth-date'"
                                            :name="'applicant_birthDate'" :placeholder="'messages.applicant_birth_date_placeholder'" :label="'insurance.passport.birth_date'" />

                                        <x-inputs.button :class="'col-md-3'" :button="'applicant-information-search-btn'" />
                                    </div>
                                </div>

                            </div>
                            <div id="applicant-info-display" class="card-footer d-none">
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

                                {{-- Hidden fields for additional applicant data --}}
                                <input type="hidden" id="applicant-pinfl" name="applicant_pinfl">
                                <input type="hidden" id="applicant-birth-place" name="applicant_birth_place">
                                <input type="hidden" id="applicant-birth-country" name="applicant_birth_country">
                                <input type="hidden" id="applicant-gender" name="applicant_gender">
                                <input type="hidden" id="applicant-region-id" name="applicant_region_id">
                                <input type="hidden" id="applicant-district-id" name="applicant_district_id">

                                <div class="form-check ">
                                    <input class="form-check-input" value="1" type="checkbox" id="is-applicant-owner"
                                        name="is_applicant_owner">
                                    <label class="form-check-label" for="is-applicant-owner">
                                        {{ __('messages.is_applicant_owner') }}
                                    </label>
                                </div>
                            </div>

                        </div>
                        <div id="client-info" class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('insurance.client.title') }}</h4>
                            </div>
                            <div class="card-body">
                                <div id="client-info-search">
                                    <div class="row align-items-start">
                                        <x-inputs.input_form :type="'text'" :class="'col-md-2'" :idFor="'client-passport-series'"
                                            :name="'client_passport_series'" :placeholder="'insurance.passport.placeholder'" :label="'insurance.passport.series'" />

                                        <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'client-passport-number'"
                                            :name="'client_passport_number'" :placeholder="'insurance.passport.placeholder'" :label="'insurance.passport.number'" />

                                        <x-inputs.input_form :type="'date'" :class="'col-md-4'" :idFor="'client-birth-date'"
                                            :name="'client_birthDate'" :placeholder="'insurance.passport.placeholder'" :label="'insurance.passport.birth_date'" />

                                        <x-inputs.button :class="'col-md-3'" :button="'client-information-search-btn'" />
                                    </div>
                                </div>

                            </div>
                            <div id="client-info-display" class="card-footer d-none">
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'client-last-name'" :name="'client_last_name'"
                                        :label="'insurance.person.last_name'" :placeholder="'insurance.person.placeholder'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'client-first-name'" :name="'client_first_name'"
                                        :label="'insurance.person.first_name'" :placeholder="'insurance.person.placeholder'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'client-middle-name'" :name="'client_middle_name'"
                                        :label="'insurance.person.middle_name'" :placeholder="'insurance.person.placeholder'" />
                                </div>

                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-6'" :idFor="'client-address'" :name="'client_address'"
                                        :label="'insurance.person.address'" :placeholder="'insurance.person.placeholder'" />

                                    <x-inputs.input_form :type="'number'" :class="'col-md-6'" :idFor="'client-phone-number'"
                                        :name="'client_phone_number'" :label="'insurance.person.telephone_number'" :placeholder="'insurance.person.telephone_number_placeholder'" />
                                </div>

                                {{-- Hidden fields for additional client data --}}
                                <input type="hidden" id="client-pinfl" name="client_pinfl">
                                <input type="hidden" id="client-birth-place" name="client_birth_place">
                                <input type="hidden" id="client-birth-country" name="client_birth_country">
                                <input type="hidden" id="client-gender" name="client_gender">
                                <input type="hidden" id="client-region-id" name="client_region_id">
                                <input type="hidden" id="client-district-id" name="client_district_id">
                            </div>
                        </div>

                        <div class="card" id="calculation">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('messages.calculation_title') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label"
                                            for="price-range">{{ __('messages.insurance_amount') }}</label>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="d-block">
                                                <span id="value_amount">UZS 12,000,000.00</span>
                                            </span>
                                            <span class="d-block">
                                                UZS 30,000,000.00
                                            </span>
                                        </div>
                                        <input class="w-100 range-after--input active" type="range" id="price-range"
                                            name="insurance_amount" min="5000000" max="50000000" step="1000000"
                                            value="12000000" style="--premium-amount: 'UZS&nbsp;12,000,000.00';">
                                        </span>
                                        <input type="hidden" id="premiumAmount" class="form-control" value="12000000"
                                            disabled="">
                                    </div>
                                    <x-inputs.input_form :type="'date'" :class="'col-md-4'" :idFor="'payment-start-date'"
                                        :name="'payment_start_date'" :label="'messages.start_date'" :placeholder="'messages.start_date_placeholder'" />
                                    <x-inputs.input_form :type="'date'" :class="'col-md-4'" :idFor="'payment-end-date'"
                                        :disabled="true" :name="'payment_end_date'" :label="'messages.start_date'" :placeholder="'messages.start_date_placeholder'" />
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agreement"
                                                name="agreement" required>
                                            <label class="form-check-label" for="agreement">
                                                {{ __('messages.agreement_text', ['default' => 'Men shartlar va qoidalar bilan tanishib chiqdim va roziman']) }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <x-inputs.button :type="'submit'" :class="'col-md-4'" :button="'payment-btn'" />
                                </div>
                            </div>

                        </div>
                    </div>

                    <x-insurence.calculate />
                </div>
            </form>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const applicantInfoBtn = document.getElementById('applicant-information-search-btn');
                const passportSeria = document.getElementById('applicant-passport-series');
                const passportNumber = document.getElementById('applicant-passport-number');
                const birthDate = document.getElementById('applicant-birth-date');
                const isApplicantOwner = document.getElementById('is-applicant-owner');
                const clientInfoBtn = document.getElementById('client-information-search-btn');

                // Toggle client application section based on checkbox
                const toggleClientApplication = () => {
                    const clientApplicationSection = document.getElementById('client-info');
                    if (clientApplicationSection) {
                        if (isApplicantOwner.checked) {
                            // Copy visible fields
                            document.getElementById('client-passport-series').value = passportSeria.value;
                            document.getElementById('client-passport-number').value = passportNumber.value;
                            document.getElementById('client-birth-date').value = birthDate.value;
                            document.getElementById('client-last-name').value = document.getElementById(
                                'applicant-last-name').value;
                            document.getElementById('client-first-name').value = document.getElementById(
                                'applicant-first-name').value;
                            document.getElementById('client-middle-name').value = document.getElementById(
                                'applicant-middle-name').value;
                            document.getElementById('client-address').value = document.getElementById(
                                'applicant-address').value;
                            document.getElementById('client-phone-number').value = document.getElementById(
                                'applicant-phone-number').value;

                            // Copy hidden fields
                            document.getElementById('client-pinfl').value = document.getElementById(
                                'applicant-pinfl').value;
                            document.getElementById('client-birth-place').value = document.getElementById(
                                'applicant-birth-place').value;
                            document.getElementById('client-birth-country').value = document.getElementById(
                                'applicant-birth-country').value;
                            document.getElementById('client-gender').value = document.getElementById(
                                'applicant-gender').value;
                            document.getElementById('client-region-id').value = document.getElementById(
                                'applicant-region-id').value;
                            document.getElementById('client-district-id').value = document.getElementById(
                                'applicant-district-id').value;

                            document.getElementById('client-info-display').classList.remove('d-none');
                        } else {
                            // Clear visible fields
                            document.getElementById('client-passport-series').value = '';
                            document.getElementById('client-passport-number').value = '';
                            document.getElementById('client-birth-date').value = '';
                            document.getElementById('client-last-name').value = '';
                            document.getElementById('client-first-name').value = '';
                            document.getElementById('client-middle-name').value = '';
                            document.getElementById('client-address').value = '';

                            // Clear hidden fields
                            document.getElementById('client-pinfl').value = '';
                            document.getElementById('client-birth-place').value = '';
                            document.getElementById('client-birth-country').value = '';
                            document.getElementById('client-gender').value = '';
                            document.getElementById('client-region-id').value = '';
                            document.getElementById('client-district-id').value = '';

                            document.getElementById('client-info-display').classList.add('d-none');
                        }
                    }
                };

                // Listen for checkbox changes
                isApplicantOwner.addEventListener('change', toggleClientApplication);

                // Initialize on page load
                toggleClientApplication();

                // Clear previous errors
                const clearErrors = () => {
                    document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                };

                // Display error under input
                const showError = (inputId, message) => {
                    const input = document.getElementById(inputId);
                    if (input) {
                        input.classList.add('is-invalid');
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback d-block';
                        errorDiv.textContent = message;
                        input.parentElement.appendChild(errorDiv);
                    }
                };

                applicantInfoBtn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    clearErrors();

                    // Show loading state
                    applicantInfoBtn.disabled = true;
                    applicantInfoBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('messages.loading') ?? 'Yuklanmoqda...' }}';

                    try {
                        const response = await fetch('/get-person-info-by-birthdate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                passport_series: passportSeria.value,
                                passport_number: passportNumber.value,
                                birthDate: birthDate.value
                            })
                        });

                        const data = await response.json();
                        console.log(data);

                        if (!response.ok) {
                            // Handle validation errors
                            if (response.status === 422 && data.errors) {
                                Object.keys(data.errors).forEach(field => {
                                    const message = data.errors[field][0];
                                    // Map API field names to form field IDs
                                    const fieldMap = {
                                        'passport_series': 'applicant-passport-series',
                                        'passport_number': 'applicant-passport-number',
                                        'birthDate': 'applicant-birth-date'
                                    };
                                    showError(fieldMap[field] || field, message);
                                });
                            } else {
                                alert(data.message || 'Xatolik yuz berdi');
                            }
                        } else if (data.success && data.result) {
                            // Handle success - populate form fields with person data
                            const personInfo = data.result;

                            document.getElementById('applicant-last-name').value = personInfo
                                .lastNameLatin || '';
                            document.getElementById('applicant-first-name').value = personInfo
                                .firstNameLatin || '';
                            document.getElementById('applicant-middle-name').value = personInfo
                                .middleNameLatin || '';
                            document.getElementById('applicant-address').value = personInfo.address || '';

                            // Populate hidden fields with additional data
                            document.getElementById('applicant-pinfl').value = personInfo.pinfl || '';
                            document.getElementById('applicant-birth-place').value = personInfo
                                .birthPlace || '';
                            document.getElementById('applicant-birth-country').value = personInfo
                                .countryName || 'УЗБЕКИСТАН';
                            document.getElementById('applicant-gender').value = personInfo.sex || '1';
                            document.getElementById('applicant-region-id').value = personInfo.regionId ||
                                '';
                            document.getElementById('applicant-district-id').value = personInfo
                                .districtId || '';

                            // Show the applicant info display section
                            document.getElementById('applicant-info-display').classList.remove('d-none');
                        } else {
                            alert(data.message || 'Ma\'lumot topilmadi');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Tarmoq xatosi yuz berdi');
                    } finally {
                        // Restore button state
                        applicantInfoBtn.disabled = false;
                        applicantInfoBtn.innerHTML = '{{ __('messages.search') ?? 'Qidirish' }}';
                    }
                });

                // Client information search functionality
                if (clientInfoBtn) {
                    clientInfoBtn.addEventListener('click', async function(e) {
                        e.preventDefault();
                        clearErrors();

                        const clientPassportSeria = document.getElementById('client-passport-series');
                        const clientPassportNumber = document.getElementById('client-passport-number');
                        const clientBirthDate = document.getElementById('client-birth-date');

                        // Show loading state
                        clientInfoBtn.disabled = true;
                        clientInfoBtn.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('messages.loading') ?? 'Yuklanmoqda...' }}';

                        try {
                            const response = await fetch('/get-person-info-by-birthdate', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]')
                                        .content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    passport_series: clientPassportSeria.value,
                                    passport_number: clientPassportNumber.value,
                                    birthDate: clientBirthDate.value
                                })
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                // Handle validation errors
                                if (response.status === 422 && data.errors) {
                                    Object.keys(data.errors).forEach(field => {
                                        const message = data.errors[field][0];
                                        // Map API field names to form field IDs
                                        const fieldMap = {
                                            'passport_series': 'client-passport-series',
                                            'passport_number': 'client-passport-number',
                                            'birthDate': 'client-birth-date'
                                        };
                                        showError(fieldMap[field] || field, message);
                                    });
                                } else {
                                    alert(data.message || 'Xatolik yuz berdi');
                                }
                            } else if (data.success && data.result) {
                                // Handle success - populate form fields with client data
                                const clientInfo = data.result;

                                document.getElementById('client-last-name').value = clientInfo
                                    .lastNameLatin || '';
                                document.getElementById('client-first-name').value = clientInfo
                                    .firstNameLatin || '';
                                document.getElementById('client-middle-name').value = clientInfo
                                    .middleNameLatin || '';
                                document.getElementById('client-address').value = clientInfo.address || '';

                                // Populate hidden fields with additional data
                                document.getElementById('client-pinfl').value = clientInfo.pinfl || '';
                                document.getElementById('client-birth-place').value = clientInfo
                                    .birthPlace || '';
                                document.getElementById('client-birth-country').value = clientInfo
                                    .countryName || 'УЗБЕКИСТАН';
                                document.getElementById('client-gender').value = clientInfo.sex || '1';
                                document.getElementById('client-region-id').value = clientInfo.regionId ||
                                    '';
                                document.getElementById('client-district-id').value = clientInfo
                                    .districtId || '';

                                // Show the client info display section
                                document.getElementById('client-info-display').classList.remove('d-none');
                            } else {
                                alert(data.message || 'Ma\'lumot topilmadi');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Tarmoq xatosi yuz berdi');
                        } finally {
                            // Restore button state
                            clientInfoBtn.disabled = false;
                            clientInfoBtn.innerHTML = '{{ __('messages.search') ?? 'Qidirish' }}';
                        }
                    });
                }

                // Insurance calculation variables
                let insuredAmountCalc = 12000000;
                let premiumAmountCalc = 0;
                const formatter = new Intl.NumberFormat("uz-UZ", {
                    style: "currency",
                    currency: "UZS",
                    minimumFractionDigits: 0,
                });

                function showVal(newVal) {
                    const formatterDR = new Intl.NumberFormat("ru-RU", {
                        style: "decimal",
                        useGrouping: true,
                        minimumFractionDigits: 0,
                    });

                    // Update hidden input value
                    document.getElementById("premiumAmount").value = Math.floor(newVal);

                    // Update insured amount
                    insuredAmountCalc = document.getElementById("premiumAmount").value;

                    // Calculate premium (0.3% of insured amount)
                    premiumAmountCalc = (0.3 * insuredAmountCalc) / 100;

                    document.getElementById("amount").innerHTML = formatter.format(insuredAmountCalc);
                    document.getElementById("premium").innerHTML = formatter.format(premiumAmountCalc);
                    document.getElementById("value_amount").innerHTML = formatter.format(insuredAmountCalc);

                    // Update the CSS pseudo-element content dynamically
                    let rangeInput = document.querySelector(".range-after--input");
                    rangeInput.style.setProperty("--premium-amount", `'${formatterDR.format(insuredAmountCalc)}'`);

                }

                // Date handling for payment start and end dates
                const paymentStartDate = document.getElementById('payment-start-date');
                const paymentEndDate = document.getElementById('payment-end-date');

                // Set start date to today
                const today = new Date();
                const todayStr = today.toISOString().split('T')[0];
                paymentStartDate.value = todayStr;

                // Calculate end date (1 year from start date)
                function updateEndDate() {
                    if (paymentStartDate.value) {
                        const startDate = new Date(paymentStartDate.value);
                        const endDate = new Date(startDate);
                        endDate.setFullYear(endDate.getFullYear() + 1);
                        paymentEndDate.value = endDate.toISOString().split('T')[0];
                    }
                }

                // Initialize end date on page load
                updateEndDate();

                // Update end date when start date changes
                paymentStartDate.addEventListener('change', updateEndDate);

                let priceRange = document.getElementById('price-range');
                if (priceRange) {
                    priceRange.addEventListener('input', function(e) {
                        showVal(e.target.value);
                    });

                    // Initialize calculation on page load
                    showVal(priceRange.value);
                }

            });
        </script>
    @endpush
@endsection
