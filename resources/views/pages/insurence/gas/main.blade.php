@extends('layouts.app')
@section('title', __('insurance.gas.page_title'))

@push('styles')
    <link href="{{ asset('assets/css/form.css') }}" rel="stylesheet">
@endpush

@section('content')
    <x-insurence.steps />

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="container">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ __('insurance.gas.errors.error') }}
                </h4>
                <p class="mb-2">{{ __('insurance.gas.errors.errors_title') }}</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

            <section class="container-fluid product-page py-4" id="gas-main">
        <div class="container">
            <form action="{{ route('gas.application', ['locale' => getCurrentLocale()]) }}" method="post">
                @csrf
                <div class="row g-4">
                    <div class="col-md-8">
                        {{-- Property Owner Information --}}
                        <div id="owner-info" class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('messages.owner_info_title') }}</h4>
                            </div>
                            <div class="card-body">
                                <div id="owner-info-search">
                                    <div class="row align-items-start">
                                        <x-inputs.input_form :type="'text'" :class="'col-md-2'" :idFor="'owner-passport-series'"
                                            :name="'owner[passportSeries]'" :placeholder="'messages.passport_series_placeholder'" :label="'messages.passport_series'" />

                                        <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'owner-passport-number'"
                                            :name="'owner[passportNumber]'" :placeholder="'messages.passport_number_placeholder'" :label="'messages.passport_number'" />

                                        <x-inputs.input_form :type="'date'" :class="'col-md-4'" :idFor="'owner-birth-date'"
                                            :name="'owner[birthDate]'" :placeholder="'messages.birth_date_placeholder'" :label="'messages.birth_date'" />

                                        <x-inputs.button :class="'col-md-3'" :button="'owner-information-search-btn'" />
                                    </div>
                                </div>
                            </div>
                            <div id="owner-info-display" class="card-footer {{ old('owner.lastName') ? '' : 'd-none' }}">
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'owner-last-name'" :name="'owner[lastName]'"
                                        :label="'messages.last_name'" :placeholder="'messages.last_name_placeholder'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'owner-first-name'" :name="'owner[firstName]'"
                                        :label="'messages.first_name'" :placeholder="'messages.first_name_placeholder'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'owner-middle-name'" :name="'owner[middleName]'"
                                        :label="'messages.middle_name'" :placeholder="'messages.middle_name_placeholder'" />
                                </div>

                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-6'" :idFor="'owner-address'" :name="'owner[address]'"
                                        :label="'messages.address'" :placeholder="'messages.address_placeholder'" />

                                    <x-inputs.input_form :type="'tel'" :class="'col-md-6'" :idFor="'owner-phone-number'"
                                        :name="'owner[phoneNumber]'" :label="'messages.phone_number'" :placeholder="'messages.phone_number_placeholder'" />
                                </div>

                                <div class="row">
                                    <x-inputs.input_form :type="'number'" :class="'col-md-6 hidden'" :idFor="'owner-pinfl'"
                                        :name="'owner[pinfl]'" :label="'messages.owner_pinfl'" :placeholder="'messages.owner_pinfl_placeholder'" />
                                </div>

                                {{-- Hidden fields for additional owner data --}}
                                <input type="hidden" id="owner-birth-place" name="owner[birthPlace]"
                                    value="{{ old('owner.birthPlace') }}">
                                <input type="hidden" id="owner-birth-country" name="owner[birthCountry]"
                                    value="{{ old('owner.birthCountry', 'УЗБЕКИСТАН') }}">
                                <input type="hidden" id="owner-gender" name="owner[gender]"
                                    value="{{ old('owner.gender', '1') }}">
                                <input type="hidden" id="owner-region-id" name="owner[regionId]"
                                    value="{{ old('owner.regionId') }}">
                                <input type="hidden" id="owner-district-id" name="owner[districtId]"
                                    value="{{ old('owner.districtId') }}">
                            </div>
                        </div>

                        {{-- Cadaster Search Section --}}
                        <div id="cadaster-search" class="card d-none">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('messages.cadaster_info_title') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-9">
                                        <label class="form-label" for="cadaster-number">{{ __('messages.cadaster_number') }}</label>
                                        <input type="text" class="form-control" id="cadaster-number"
                                            name="property[cadasterNumber]" placeholder="11:11:10:01:03:0499" required
                                            value="{{ old('property.cadasterNumber') }}">
                                        <small class="form-text text-muted">{{ __('messages.cadaster_format') }}</small>
                                    </div>
                                    <x-inputs.button :class="'col-md-3'" :button="'cadaster-search-btn'" />
                                </div>
                            </div>
                        </div>

                        {{-- Property Information Display --}}
                        <div id="property-info" class="card d-none">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('messages.property_info_title') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-8'" :idFor="'property-address'" :name="'property[address]'"
                                        :label="'messages.property_full_address'" :placeholder="'messages.address_placeholder'" />

                                    <x-inputs.input_info :class="'col-md-4 hidden'" :idFor="'property-name'" :name="'property[name]'"
                                        :label="'messages.property_name'" :placeholder="'messages.property_name'" />
                                </div>
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-6'" :idFor="'property-short-address'" :name="'property[shortAddress]'"
                                        :label="'messages.property_short_address'" :placeholder="'messages.address_placeholder'" />

                                    <x-inputs.input_info :class="'col-md-6'" :idFor="'property-street'" :name="'property[street]'"
                                        :label="'messages.property_street'" :placeholder="'messages.property_street'" />
                                </div>
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'property-tip-text'" :name="'property[tipText]'"
                                        :label="'messages.property_object_type'" :placeholder="'messages.property_object_type'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'property-vid-text'" :name="'property[vidText]'"
                                        :label="'messages.property_object_view'" :placeholder="'messages.property_object_view'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'property-area'" :name="'property[objectArea]'"
                                        :label="'messages.property_total_area'" :placeholder="'messages.property_total_area'" />
                                </div>
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'property-region'" :name="'property[region]'"
                                        :label="'messages.property_region'" :placeholder="'messages.property_region'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'property-district'" :name="'property[district]'"
                                        :label="'messages.property_district'" :placeholder="'messages.property_district'" />

                                    <div class="col-md-4">
                                        <label class="form-label" for="property-cost-display">{{ __('messages.property_cost') }}</label>
                                        <input type="text" class="form-control" id="property-cost-display" readonly
                                            value="{{ old('property.cost') ? number_format(old('property.cost')) : '' }}">
                                    </div>
                                </div>

                                {{-- Hidden fields --}}
                                <input type="hidden" id="property-tip" name="property[tip]"
                                    value="{{ old('property.tip') }}">
                                <input type="hidden" id="property-vid" name="property[vid]"
                                    value="{{ old('property.vid') }}">
                                <input type="hidden" id="property-area-l" name="property[objectAreaL]"
                                    value="{{ old('property.objectAreaL', '0') }}">
                                <input type="hidden" id="property-area-u" name="property[objectAreaU]"
                                    value="{{ old('property.objectAreaU', '0') }}">
                                <input type="hidden" id="property-region-id" name="property[regionId]"
                                    value="{{ old('property.regionId') }}">
                                <input type="hidden" id="property-district-id" name="property[districtId]"
                                    value="{{ old('property.districtId') }}">
                                <input type="hidden" id="property-dom-num" name="property[domNum]"
                                    value="{{ old('property.domNum') }}">
                                <input type="hidden" id="property-kvartira-num" name="property[kvartiraNum]"
                                    value="{{ old('property.kvartiraNum') }}">
                                <input type="hidden" id="property-neighborhood" name="property[neighborhood]"
                                    value="{{ old('property.neighborhood') }}">
                                <input type="hidden" id="property-neighborhood-id" name="property[neighborhoodId]"
                                    value="{{ old('property.neighborhoodId') }}">
                                <input type="hidden" id="property-cost" name="property[cost]"
                                    value="{{ old('property.cost') }}">
                            </div>
                        </div>

                        {{-- Insurance Calculation --}}
                        <div class="card d-none" id="calculation">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('messages.calculation_title') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label" for="insurance-range">{{ __('messages.insurance_amount') }}</label>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="d-block">
                                                <span id="value_amount">UZS 50,000,000.00</span>
                                            </span>
                                            <span class="d-block">
                                                UZS 500,000,000.00
                                            </span>
                                        </div>
                                        <input class="w-100 range-after--input active" type="range" id="insurance-range"
                                            name="insurance_amount" min="5000000" max="500000000" step="10000000"
                                            value="{{ old('insurance_amount', 100000000) }}"
                                            style="--premium-amount: 'UZS&nbsp;100,000,000.00';">
                                        <input type="hidden" id="insuranceAmount" class="form-control"
                                            value="{{ old('insurance_amount', 100000000) }}">
                                    </div>
                                    <x-inputs.input_form :type="'date'" :class="'col-md-4'" :idFor="'payment-start-date'"
                                        :name="'payment_start_date'" :label="'messages.start_date'" :placeholder="'messages.start_date_placeholder'" />
                                    <x-inputs.input_form :type="'date'" :class="'col-md-4'" :idFor="'payment-end-date'"
                                        :readonly="true" :name="'payment_end_date'" :label="'messages.end_date'" :placeholder="'messages.start_date_placeholder'" />
                                    <x-inputs.button :type="'submit'" :class="'col-md-3'" :button="'payment-btn'" />
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agreement"
                                                name="agreement" required {{ old('agreement') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="agreement">
                                                {{ __('messages.agreement_text', ['default' => 'Men shartlar va qoidalar bilan tanishib chiqdim va roziman']) }}
                                            </label>
                                        </div>
                                    </div>
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

            let insuredAmountCalc = 100000000;
            // Translations for JavaScript
            const translations = {
                errorOccurred: '{{ __('messages.error_occurred') }}',
                dataNotFound: '{{ __('messages.data_not_found') }}',
                networkError: '{{ __('messages.network_error') }}',
                cadasterInvalid: '{{ __('messages.cadaster_invalid') }}',
                loading: '{{ __('messages.loading') }}',
                search: '{{ __('messages.search') }}',
            };

            document.addEventListener('DOMContentLoaded', function() {
                // Bootstrap alert close functionality
                document.querySelectorAll('.btn-close').forEach(button => {
                    button.addEventListener('click', function() {
                        const alert = this.closest('.alert');
                        if (alert) {
                            alert.style.transition = 'opacity 0.15s linear';
                            alert.style.opacity = '0';
                            setTimeout(() => alert.remove(), 150);
                        }
                    });
                });

                // Auto-scroll to first error field
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstError.focus();
                }

                // Phone number formatting function
                function formatPhone(value) {
                    let cleaned = value.replace(/\D/g, '');

                    // Ensure it starts with 998
                    if (cleaned.length > 0 && !cleaned.startsWith('998')) {
                        if (cleaned.startsWith('998')) {
                            // Already correct
                        } else if (cleaned.startsWith('98')) {
                            cleaned = '9' + cleaned;
                        } else if (cleaned.length >= 9) {
                            cleaned = '998' + cleaned;
                        }
                    }

                    // Limit to 12 digits (998 + 9 digits)
                    if (cleaned.length > 12) {
                        cleaned = cleaned.slice(0, 12);
                    }

                    // Add spaces: +998 XX XXX XX XX
                    let formatted = '+';

                    if (cleaned.length > 0) formatted += cleaned.slice(0, 3); // 998
                    if (cleaned.length > 3) formatted += ' ' + cleaned.slice(3, 5); // XX
                    if (cleaned.length > 5) formatted += ' ' + cleaned.slice(5, 8); // XXX
                    if (cleaned.length > 8) formatted += ' ' + cleaned.slice(8, 10); // XX
                    if (cleaned.length > 10) formatted += ' ' + cleaned.slice(10, 12); // XX

                    return formatted;
                }

                // Setup phone formatting for owner phone input
                const ownerPhone = document.getElementById('owner-phone-number');

                if (ownerPhone) {
                    ownerPhone.addEventListener('input', function(e) {
                        const cursorPos = e.target.selectionStart;
                        const oldValue = e.target.value;
                        const newValue = formatPhone(oldValue);

                        if (newValue !== oldValue) {
                            e.target.value = newValue;
                            // Try to maintain cursor position
                            const diff = newValue.length - oldValue.length;
                            e.target.setSelectionRange(Math.max(0, cursorPos + diff), Math.max(0, cursorPos + diff));
                        }
                    });
                }

                const ownerInfoBtn = document.getElementById('owner-information-search-btn');
                const cadasterSearchBtn = document.getElementById('cadaster-search-btn');
                const cadasterNumber = document.getElementById('cadaster-number');

                // If old values exist, show the display sections
                @if (old('owner.lastName'))
                    document.getElementById('owner-info-display').classList.remove('d-none');
                    document.getElementById('cadaster-search').classList.remove('d-none');
                @endif

                @if (old('property.address'))
                    document.getElementById('property-info').classList.remove('d-none');
                    document.getElementById('calculation').classList.remove('d-none');
                @endif

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

                // Owner information search functionality
                if (ownerInfoBtn) {
                    ownerInfoBtn.addEventListener('click', async function(e) {
                        e.preventDefault();
                        clearErrors();

                        const ownerPassportSeria = document.getElementById('owner-passport-series');
                        const ownerPassportNumber = document.getElementById('owner-passport-number');
                        const ownerBirthDate = document.getElementById('owner-birth-date');

                        // Show loading state
                        ownerInfoBtn.disabled = true;
                        ownerInfoBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>';

                        try {
                            const response = await fetch('/get-person-info-by-birthdate', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    passport_series: ownerPassportSeria.value,
                                    passport_number: ownerPassportNumber.value,
                                    birthDate: ownerBirthDate.value,
                                    product_name: 'gazballon_owner_info'
                                })
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                if (response.status === 422 && data.errors) {
                                    Object.keys(data.errors).forEach(field => {
                                        const message = data.errors[field][0];
                                        const fieldMap = {
                                            'passport_series': 'owner-passport-series',
                                            'passport_number': 'owner-passport-number',
                                            'birthDate': 'owner-birth-date'
                                        };
                                        showError(fieldMap[field] || field, message);
                                    });
                                } else {
                                    alert(data.message || translations.errorOccurred);
                                }
                            } else if (data.success && data.result) {
                                const ownerInfo = data.result;

                                document.getElementById('owner-last-name').value = ownerInfo.lastNameLatin || '';
                                document.getElementById('owner-first-name').value = ownerInfo.firstNameLatin || '';
                                document.getElementById('owner-middle-name').value = ownerInfo.middleNameLatin || '';
                                document.getElementById('owner-address').value = ownerInfo.address || '';

                                // Populate hidden fields
                                document.getElementById('owner-pinfl').value = ownerInfo.currentPinfl || '';
                                document.getElementById('owner-birth-place').value = ownerInfo.birthPlace || '';
                                document.getElementById('owner-birth-country').value = ownerInfo.countryName || 'УЗБЕКИСТАН';
                                document.getElementById('owner-gender').value = ownerInfo.sex || '1';
                                document.getElementById('owner-region-id').value = ownerInfo.regionId || '';
                                document.getElementById('owner-district-id').value = ownerInfo.districtId || '';

                                document.getElementById('owner-info-display').classList.remove('d-none');
                                document.getElementById('cadaster-search').classList.remove('d-none');
                            } else {
                                alert(data.message || translations.dataNotFound);
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert(translations.networkError);
                        } finally {
                            ownerInfoBtn.disabled = false;
                            ownerInfoBtn.innerHTML = "<svg width='20' height='20'><use xlink:href='#icon-search'></use></svg>";
                        }
                    });
                }

                // Format cadaster number input
                if (cadasterNumber) {
                    cadasterNumber.addEventListener('input', function(e) {
                        let value = e.target.value.replace(/[^\d]/g, '');
                        if (value.length > 2) value = value.slice(0, 2) + ':' + value.slice(2);
                        if (value.length > 5) value = value.slice(0, 5) + ':' + value.slice(5);
                        if (value.length > 8) value = value.slice(0, 8) + ':' + value.slice(8);
                        if (value.length > 11) value = value.slice(0, 11) + ':' + value.slice(11);
                        if (value.length > 14) value = value.slice(0, 14) + ':' + value.slice(14);
                        if (value.length > 19) value = value.slice(0, 19);
                        e.target.value = value;
                    });
                }

                // Cadaster search functionality
                if (cadasterSearchBtn) {
                    cadasterSearchBtn.addEventListener('click', async function(e) {
                        e.preventDefault();
                        clearErrors();

                        // Show loading state
                        cadasterSearchBtn.disabled = true;
                        cadasterSearchBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + translations.loading;

                        try {
                            const response = await fetch('/fetch-cadaster', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    cadasterNumber: cadasterNumber.value,
                                    product_name: 'gazballon_property_info'
                                })
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                if (response.status === 422) {
                                    showError('cadaster-number', data.message || translations.cadasterInvalid);
                                } else {
                                    alert(data.message || translations.errorOccurred);
                                }
                            } else if (data.success && data.result) {
                                const property = data.result;

                                // Populate property information
                                document.getElementById('property-address').value = property.address || '';
                                document.getElementById('property-name').value = property.name || '';
                                document.getElementById('property-short-address').value = property.shortAddress || '';
                                document.getElementById('property-street').value = property.street || '';
                                document.getElementById('property-tip-text').value = property.tipText || '';
                                document.getElementById('property-vid-text').value = property.vidText || '';
                                document.getElementById('property-area').value = property.objectArea || '';
                                document.getElementById('property-region').value = property.region || '';
                                document.getElementById('property-district').value = property.district || '';

                                // Cost - display formatted, store clean value
                                const costValue = parseInt(property.cost || 0);

                                // Update insuredAmountCalc
                                insuredAmountCalc = costValue;

                                // Update all insurance calculation elements
                                document.getElementById('insuranceAmount').value = insuredAmountCalc;

                                const rangeInput = document.getElementById('insurance-range');
                                if (rangeInput) {
                                    rangeInput.value = insuredAmountCalc;

                                    // Update CSS variable for range input
                                    const formatterDR = new Intl.NumberFormat("ru-RU", {
                                        style: "decimal",
                                        useGrouping: true,
                                        minimumFractionDigits: 0,
                                    });
                                    rangeInput.style.setProperty("--premium-amount", `'${formatterDR.format(insuredAmountCalc)}'`);
                                }

                                // Update displayed values
                                document.getElementById('amount').textContent = formatter.format(insuredAmountCalc);
                                document.getElementById('value_amount').textContent = formatter.format(insuredAmountCalc);

                                // Calculate and display premium (0.5% for gas balloon insurance)
                                premiumAmountCalc = (0.5 * insuredAmountCalc) / 100;
                                const premiumElement = document.getElementById('premium');
                                if (premiumElement) {
                                    premiumElement.textContent = formatter.format(premiumAmountCalc);
                                }


                                document.getElementById('property-cost-display').value = costValue.toLocaleString('uz-UZ');
                                document.getElementById('property-cost').value = costValue;

                                // Populate hidden fields
                                document.getElementById('property-tip').value = property.tip || '';
                                document.getElementById('property-vid').value = property.vid || '';
                                document.getElementById('property-area-l').value = property.objectAreaL || '0';
                                document.getElementById('property-area-u').value = property.objectAreaU || '0';
                                document.getElementById('property-region-id').value = property.regionId || '';
                                document.getElementById('property-district-id').value = property.districtId || '';
                                document.getElementById('property-dom-num').value = property.domNum || '';
                                document.getElementById('property-kvartira-num').value = property.kvartiraNum || '';
                                document.getElementById('property-neighborhood').value = property.neighborhood || '';
                                document.getElementById('property-neighborhood-id').value = property.neighborhoodId || '';

                                // Show property info and calculation sections
                                document.getElementById('property-info').classList.remove('d-none');
                                document.getElementById('calculation').classList.remove('d-none');
                            } else {
                                alert(data.message || translations.dataNotFound);
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert(translations.networkError);
                        } finally {
                            // Restore button state
                            cadasterSearchBtn.disabled = false;
                            cadasterSearchBtn.innerHTML = translations.search;
                        }
                    });
                }

                // Insurance calculation
                let insuredAmountCalc = {{ old('insurance_amount', 100000000) }};
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

                    document.getElementById("insuranceAmount").value = Math.floor(newVal);
                    insuredAmountCalc = document.getElementById("insuranceAmount").value;

                    // Calculate premium (0.2% for property insurance)
                    premiumAmountCalc = (0.5 * insuredAmountCalc) / 100;

                    document.getElementById("amount").innerHTML = formatter.format(insuredAmountCalc);
                    document.getElementById("premium").innerHTML = formatter.format(premiumAmountCalc);
                    document.getElementById("value_amount").innerHTML = formatter.format(insuredAmountCalc);

                    let rangeInput = document.querySelector(".range-after--input");
                    if (rangeInput) {
                        rangeInput.style.setProperty("--premium-amount", `'${formatterDR.format(insuredAmountCalc)}'`);
                    }
                }

                // Date handling
                const paymentStartDate = document.getElementById('payment-start-date');
                const paymentEndDate = document.getElementById('payment-end-date');

                // Set start date to today (if no old value)
                @if (!old('payment_start_date'))
                    const today = new Date();
                    const todayStr = today.toISOString().split('T')[0];
                    paymentStartDate.value = todayStr;
                @endif

                function updateEndDate() {
                    if (paymentStartDate.value) {
                        const startDate = new Date(paymentStartDate.value);
                        const endDate = new Date(startDate);
                        endDate.setFullYear(endDate.getFullYear() + 1);
                        paymentEndDate.value = endDate.toISOString().split('T')[0];
                    }
                }

                updateEndDate();
                paymentStartDate.addEventListener('change', updateEndDate);

                let insuranceRange = document.getElementById('insurance-range');
                if (insuranceRange) {
                    insuranceRange.addEventListener('input', function(e) {
                        showVal(e.target.value);
                    });
                    showVal(insuranceRange.value);
                }

                // Clean phone numbers before form submission
                const form = document.querySelector('form[action*="gas.application"]');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        // Clean owner phone number
                        if (ownerPhone && ownerPhone.value) {
                            ownerPhone.value = ownerPhone.value.replace(/\D/g, '');
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
