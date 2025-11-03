@extends('layouts.app')
@section('title', 'Property Insurance - Uy-joy sug\'urtasi')

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
                    {{ __('insurance.property.errors.error') }}
                </h4>
                <p class="mb-2">{{ __('insurance.property.errors.errors_title') }}</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <section class="container-fluid product-page py-4" id="property-main">
        <div class="container">
            <form action="{{ route('property.application', ['locale' => getCurrentLocale()]) }}" method="post">
                @csrf
                <div class="row g-4">
                    <div class="col-md-8">
                        {{-- Applicant Information --}}
                        <div id="applicant-info" class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('messages.applicant_info_title') }}</h4>
                            </div>
                            <div class="card-body">
                                <div id="applicant-info-search">
                                    <div class="row align-items-start">
                                        <x-inputs.input_form :type="'text'" :class="'col-md-2'" :idFor="'applicant-passport-series'"
                                            :name="'applicant[passportSeries]'" :placeholder="'messages.passport_series_placeholder'" :label="'messages.passport_series'" />

                                        <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'applicant-passport-number'"
                                            :name="'applicant[passportNumber]'" :placeholder="'messages.passport_number_placeholder'" :label="'messages.passport_number'" />

                                        <x-inputs.input_form :type="'date'" :class="'col-md-4'" :idFor="'applicant-birth-date'"
                                            :name="'applicant[birthDate]'" :placeholder="'messages.birth_date_placeholder'" :label="'messages.birth_date'" />

                                        <x-inputs.button :class="'col-md-3'" :button="'applicant-information-search-btn'" />
                                    </div>
                                </div>
                            </div>
                            <div id="applicant-info-display"
                                class="card-footer {{ old('applicant.lastName') ? '' : 'd-none' }}">
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-last-name'" :name="'applicant[lastName]'"
                                        :label="'messages.last_name'" :placeholder="'messages.last_name_placeholder'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-first-name'" :name="'applicant[firstName]'"
                                        :label="'messages.first_name'" :placeholder="'messages.first_name_placeholder'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-middle-name'" :name="'applicant[middleName]'"
                                        :label="'messages.middle_name'" :placeholder="'messages.middle_name_placeholder'" />
                                </div>

                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-6'" :idFor="'applicant-address'" :name="'applicant[address]'"
                                        :label="'messages.address'" :placeholder="'messages.address_placeholder'" />

                                    <x-inputs.input_form :type="'number'" :class="'col-md-6'" :idFor="'applicant-phone-number'"
                                        :name="'applicant[phoneNumber]'" :label="'messages.phone_number'" :placeholder="'messages.phone_number_placeholder'" />
                                </div>

                                <div class="row">
                                    <x-inputs.input_form :type="'number'" :class="'col-md-6'" :idFor="'applicant-pinfl'"
                                        :name="'applicant[pinfl]'" :label="'messages.owner_pinfl'" :placeholder="'messages.owner_pinfl_placeholder'" />
                                </div>

                                {{-- Hidden fields for additional applicant data --}}
                                <input type="hidden" id="applicant-birth-place" name="applicant[birthPlace]"
                                    value="{{ old('applicant.birthPlace') }}">
                                <input type="hidden" id="applicant-birth-country" name="applicant[birthCountry]"
                                    value="{{ old('applicant.birthCountry', 'УЗБЕКИСТАН') }}">
                                <input type="hidden" id="applicant-gender" name="applicant[gender]"
                                    value="{{ old('applicant.gender', '1') }}">
                                <input type="hidden" id="applicant-region-id" name="applicant[regionId]"
                                    value="{{ old('applicant.regionId') }}">
                                <input type="hidden" id="applicant-district-id" name="applicant[districtId]"
                                    value="{{ old('applicant.districtId') }}">

                                <div class="form-check">
                                    <input class="form-check-input" value="1" type="checkbox" id="is-applicant-owner"
                                        name="is_applicant_owner" {{ old('is_applicant_owner') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is-applicant-owner">
                                        Men mulk egasiman (Ariza beruvchi ma'lumotlarini avtomatik to'ldirish)
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Property Owner Information --}}
                        <div id="owner-info" class="card d-none">
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

                                    <x-inputs.input_form :type="'number'" :class="'col-md-6'" :idFor="'owner-phone-number'"
                                        :name="'owner[phoneNumber]'" :label="'messages.phone_number'" :placeholder="'messages.phone_number_placeholder'" />
                                </div>

                                <div class="row">
                                    <x-inputs.input_form :type="'number'" :class="'col-md-6'" :idFor="'owner-pinfl'"
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
                                <h4 class="card-title">Kadastr ma'lumotlari</h4>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-end">
                                    <div class="col-md-9">
                                        <label class="form-label" for="cadaster-number">Kadastr raqami</label>
                                        <input type="text" class="form-control" id="cadaster-number"
                                            name="property[cadasterNumber]" placeholder="11:11:10:01:03:0499" required
                                            value="{{ old('property.cadasterNumber') }}">
                                        <small class="form-text text-muted">Format: XX:XX:XX:XX:XX:XXXX</small>
                                    </div>
                                    <x-inputs.button :class="'col-md-3'" :button="'cadaster-search-btn'" />
                                </div>
                            </div>
                        </div>

                        {{-- Property Information Display --}}
                        <div id="property-info" class="card d-none">
                            <div class="card-header">
                                <h4 class="card-title">Mulk ma'lumotlari</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-8'" :idFor="'property-address'" :name="'property[address]'"
                                        :label="'To\'liq manzil'" :placeholder="'manzil'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'property-name'" :name="'property[name]'"
                                        :label="'Mulk nomi'" :placeholder="'mulk nomi'" />
                                </div>
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-6'" :idFor="'property-short-address'" :name="'property[shortAddress]'"
                                        :label="'Qisqa manzil'" :placeholder="'manzil'" />

                                    <x-inputs.input_info :class="'col-md-6'" :idFor="'property-street'" :name="'property[street]'"
                                        :label="'Ko\'cha'" :placeholder="'ko\'cha'" />
                                </div>
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'property-tip-text'" :name="'property[tipText]'"
                                        :label="'Ob\'ekt turi'" :placeholder="'turi'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'property-vid-text'" :name="'property[vidText]'"
                                        :label="'Ob\'ekt ko\'rinishi'" :placeholder="'ko\'rinishi'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'property-area'" :name="'property[objectArea]'"
                                        :label="'Umumiy maydon (m²)'" :placeholder="'maydon'" />
                                </div>
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'property-region'" :name="'property[region]'"
                                        :label="'Viloyat'" :placeholder="'viloyat'" />

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'property-district'" :name="'property[district]'"
                                        :label="'Tuman'" :placeholder="'tuman'" />

                                    <div class="col-md-4">
                                        <label class="form-label" for="property-cost-display">Qiymati (UZS)</label>
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
                                            name="insurance_amount" min="50000000" max="500000000" step="10000000"
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

                const applicantInfoBtn = document.getElementById('applicant-information-search-btn');
                const passportSeria = document.getElementById('applicant-passport-series');
                const passportNumber = document.getElementById('applicant-passport-number');
                const birthDate = document.getElementById('applicant-birth-date');
                const isApplicantOwner = document.getElementById('is-applicant-owner');
                const ownerInfoBtn = document.getElementById('owner-information-search-btn');
                const cadasterSearchBtn = document.getElementById('cadaster-search-btn');
                const cadasterNumber = document.getElementById('cadaster-number');

                // Toggle owner section based on checkbox
                const toggleOwnerAsApplicant = () => {
                    const ownerSection = document.getElementById('owner-info');
                    const cadasterSection = document.getElementById('cadaster-search');
                    
                    if (ownerSection) {
                        if (isApplicantOwner.checked) {
                            // Copy visible fields from applicant to owner
                            document.getElementById('owner-passport-series').value = passportSeria.value;
                            document.getElementById('owner-passport-number').value = passportNumber.value;
                            document.getElementById('owner-birth-date').value = birthDate.value;
                            document.getElementById('owner-last-name').value = document.getElementById('applicant-last-name').value;
                            document.getElementById('owner-first-name').value = document.getElementById('applicant-first-name').value;
                            document.getElementById('owner-middle-name').value = document.getElementById('applicant-middle-name').value;
                            document.getElementById('owner-address').value = document.getElementById('applicant-address').value;
                            document.getElementById('owner-phone-number').value = document.getElementById('applicant-phone-number').value;

                            // Copy hidden fields
                            document.getElementById('owner-pinfl').value = document.getElementById('applicant-pinfl').value;
                            document.getElementById('owner-birth-place').value = document.getElementById('applicant-birth-place').value;
                            document.getElementById('owner-birth-country').value = document.getElementById('applicant-birth-country').value;
                            document.getElementById('owner-gender').value = document.getElementById('applicant-gender').value;
                            document.getElementById('owner-region-id').value = document.getElementById('applicant-region-id').value;
                            document.getElementById('owner-district-id').value = document.getElementById('applicant-district-id').value;

                            document.getElementById('owner-info-display').classList.remove('d-none');
                            cadasterSection.classList.remove('d-none');
                        } else {
                            // Clear owner fields
                            document.getElementById('owner-passport-series').value = '';
                            document.getElementById('owner-passport-number').value = '';
                            document.getElementById('owner-birth-date').value = '';
                            document.getElementById('owner-last-name').value = '';
                            document.getElementById('owner-first-name').value = '';
                            document.getElementById('owner-middle-name').value = '';
                            document.getElementById('owner-address').value = '';
                            document.getElementById('owner-phone-number').value = '';

                            // Clear hidden fields
                            document.getElementById('owner-pinfl').value = '';
                            document.getElementById('owner-birth-place').value = '';
                            document.getElementById('owner-birth-country').value = '';
                            document.getElementById('owner-gender').value = '';
                            document.getElementById('owner-region-id').value = '';
                            document.getElementById('owner-district-id').value = '';

                            document.getElementById('owner-info-display').classList.add('d-none');
                            cadasterSection.classList.add('d-none');
                            document.getElementById('property-info').classList.add('d-none');
                            document.getElementById('calculation').classList.add('d-none');
                        }
                    }
                };

                // Listen for checkbox changes
                if (isApplicantOwner) {
                    isApplicantOwner.addEventListener('change', toggleOwnerAsApplicant);
                }

                // Initialize on page load
                toggleOwnerAsApplicant();

                // If old values exist, show the display sections
                @if (old('applicant.lastName'))
                    document.getElementById('applicant-info-display').classList.remove('d-none');
                    document.getElementById('owner-info').classList.remove('d-none');
                @endif

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

                // Applicant information search functionality
                if (applicantInfoBtn) {
                    applicantInfoBtn.addEventListener('click', async function(e) {
                        e.preventDefault();
                        clearErrors();

                        // Show loading state
                        applicantInfoBtn.disabled = true;
                        applicantInfoBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>';

                        try {
                            const response = await fetch('/get-person-info-by-birthdate', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    passport_series: passportSeria.value,
                                    passport_number: passportNumber.value,
                                    birthDate: birthDate.value
                                })
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                if (response.status === 422 && data.errors) {
                                    Object.keys(data.errors).forEach(field => {
                                        const message = data.errors[field][0];
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
                                const personInfo = data.result;

                                document.getElementById('applicant-last-name').value = personInfo.lastNameLatin || '';
                                document.getElementById('applicant-first-name').value = personInfo.firstNameLatin || '';
                                document.getElementById('applicant-middle-name').value = personInfo.middleNameLatin || '';
                                document.getElementById('applicant-address').value = personInfo.address || '';

                                // Populate hidden fields
                                document.getElementById('applicant-pinfl').value = personInfo.currentPinfl || '';
                                document.getElementById('applicant-birth-place').value = personInfo.birthPlace || '';
                                document.getElementById('applicant-birth-country').value = personInfo.countryName || 'УЗБЕКИСТАН';
                                document.getElementById('applicant-gender').value = personInfo.sex || '1';
                                document.getElementById('applicant-region-id').value = personInfo.regionId || '';
                                document.getElementById('applicant-district-id').value = personInfo.districtId || '';

                                document.getElementById('applicant-info-display').classList.remove('d-none');
                                document.getElementById('owner-info').classList.remove('d-none');
                            } else {
                                alert(data.message || 'Ma\'lumot topilmadi');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Tarmoq xatosi yuz berdi');
                        } finally {
                            applicantInfoBtn.disabled = false;
                            applicantInfoBtn.innerHTML = "<svg width='20' height='20'><use xlink:href='#icon-search'></use></svg>";
                        }
                    });
                }

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
                                    birthDate: ownerBirthDate.value
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
                                    alert(data.message || 'Xatolik yuz berdi');
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
                                alert(data.message || 'Ma\'lumot topilmadi');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Tarmoq xatosi yuz berdi');
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
                        cadasterSearchBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __("messages.loading") ?? "Yuklanmoqda..." }}';

                        try {
                            const response = await fetch('/fetch-cadaster', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    cadasterNumber: cadasterNumber.value
                                })
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                if (response.status === 422) {
                                    showError('cadaster-number', data.message || 'Kadastr raqami noto\'g\'ri');
                                } else {
                                    alert(data.message || 'Xatolik yuz berdi');
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
                                alert(data.message || 'Ma\'lumot topilmadi');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Tarmoq xatosi yuz berdi');
                        } finally {
                            // Restore button state
                            cadasterSearchBtn.disabled = false;
                            cadasterSearchBtn.innerHTML = '{{ __("messages.search") ?? "Qidirish" }}';
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
                    premiumAmountCalc = (0.2 * insuredAmountCalc) / 100;

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
            });
        </script>
    @endpush
@endsection