@extends('layouts.app')
@section('title', 'Property Insurance - Uy-joy sug\'urtasi')

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

                                {{-- Hidden fields --}}
                                <input type="hidden" id="applicant-pinfl" name="applicant[pinfl]"
                                    value="{{ old('applicant.pinfl') }}">
                                <input type="hidden" id="applicant-inn" name="applicant[inn]"
                                    value="{{ old('applicant.inn') }}">
                                <input type="hidden" id="applicant-gender" name="applicant[gender]"
                                    value="{{ old('applicant.gender', '1') }}">
                            </div>
                        </div>

                        {{-- Owner Information --}}
                        <div id="owner-info" class="card {{ old('owner.lastName') ? '' : 'd-none' }}">
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

                                <div class="form-check mt-3">
                                    <input class="form-check-input" value="1" type="checkbox" id="is-owner-applicant"
                                        name="is_owner_applicant" {{ old('is_owner_applicant') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is-owner-applicant">
                                        Men mulk egasiman (Ariza beruvchi ma'lumotlarini avtomatik to'ldirish)
                                    </label>
                                </div>

                                {{-- Hidden fields --}}
                                <input type="hidden" id="owner-type" name="owner[type]" value="{{ old('owner.type') }}">
                                <input type="hidden" id="owner-pinfl" name="owner[pinfl]"
                                    value="{{ old('owner.pinfl') }}">
                                <input type="hidden" id="owner-percent" name="owner[percent]"
                                    value="{{ old('owner.percent') }}">
                                <input type="hidden" id="owner-inn" name="owner[inn]" value="{{ old('owner.inn') }}">
                                <input type="hidden" id="owner-gender" name="owner[gender]"
                                    value="{{ old('owner.gender', '1') }}">
                            </div>
                        </div>

                        {{-- Cadaster Search Section --}}
                        <div id="cadaster-search" class="card {{ old('applicant.lastName') ? '' : 'd-none' }}">
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
                        <div id="property-info" class="card {{ old('property.address') ? '' : 'd-none' }}">
                            <div class="card-header">
                                <h4 class="card-title">Mulk ma'lumotlari</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <x-inputs.input_info :class="'col-md-12'" :idFor="'property-address'" :name="'property[address]'"
                                        :label="'To\'liq manzil'" :placeholder="'manzil'" />
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
                        <div class="card {{ old('property.address') ? '' : 'd-none' }}" id="calculation">
                            <div class="card-header">
                                <h4 class="card-title">Sug'urta hisob-kitobi</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label" for="insurance-range">Sug'urta summasi</label>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="d-block">
                                                <span id="value_amount">UZS 50,000,000.00</span>
                                            </span>
                                            <span class="d-block">
                                                UZS 500,000,000.00
                                            </span>
                                        </div>
                                        <input class="w-100 range-after--input active" type="range"
                                            id="insurance-range" name="insurance_amount" min="50000000" max="500000000"
                                            step="10000000" value="{{ old('insurance_amount', 10000000) }}"
                                            style="--premium-amount: 'UZS&nbsp;100,000,000.00';">
                                        <input type="hidden" id="insuranceAmount" class="form-control"
                                            value="{{ old('insurance_amount', 100000000) }}">
                                    </div>
                                    <x-inputs.input_form :type="'date'" :class="'col-md-6'" :idFor="'payment-start-date'"
                                        :name="'payment_start_date'" :label="'messages.start_date'" :placeholder="'messages.start_date_placeholder'" />
                                    <x-inputs.input_form :type="'date'" :class="'col-md-6'" :idFor="'payment-end-date'"
                                        :readonly="true" :name="'payment_end_date'" :label="'messages.end_date'" :placeholder="'messages.start_date_placeholder'" />
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
                const cadasterSearchBtn = document.getElementById('cadaster-search-btn');
                const cadasterNumber = document.getElementById('cadaster-number');
                const applicantInfoBtn = document.getElementById('applicant-information-search-btn');
                const ownerInfoBtn = document.getElementById('owner-information-search-btn');
                const isOwnerApplicant = document.getElementById('is-owner-applicant');

                // Format cadaster number input
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

                // Toggle owner section based on applicant checkbox
                const toggleOwnerAsApplicant = () => {
                    if (isOwnerApplicant.checked) {
                        // Parse owner name (assuming format: LAST FIRST MIDDLE)
                        const ownerName = document.getElementById('owner-name').value;
                        const nameParts = ownerName.split(' ');

                        // Parse passport (format: "AA 1234567")
                        const ownerPassport = document.getElementById('owner-passport').value;
                        const passportParts = ownerPassport.split(' ');

                        if (passportParts.length >= 2) {
                            document.getElementById('applicant-passport-series').value = passportParts[0];
                            document.getElementById('applicant-passport-number').value = passportParts[1];
                        }

                        if (nameParts.length >= 2) {
                            document.getElementById('applicant-last-name').value = nameParts[0] || '';
                            document.getElementById('applicant-first-name').value = nameParts[1] || '';
                            document.getElementById('applicant-middle-name').value = nameParts.slice(2).join(' ') ||
                                '';
                        }

                        // Copy PINFL/INN and gender
                        const ownerPinfl = document.getElementById('owner-pinfl').value;
                        const ownerGender = document.getElementById('owner-gender').value;
                        document.getElementById('applicant-pinfl').value = ownerPinfl;
                        document.getElementById('applicant-inn').value = ownerPinfl;
                        document.getElementById('applicant-gender').value = ownerGender;

                        // Show applicant info display
                        document.getElementById('applicant-info-display').classList.remove('d-none');
                        document.getElementById('cadaster-search').classList.remove('d-none');
                    } else {
                        // Clear applicant fields
                        document.getElementById('applicant-passport-series').value = '';
                        document.getElementById('applicant-passport-number').value = '';
                        document.getElementById('applicant-birth-date').value = '';
                        document.getElementById('applicant-last-name').value = '';
                        document.getElementById('applicant-first-name').value = '';
                        document.getElementById('applicant-middle-name').value = '';
                        document.getElementById('applicant-address').value = '';
                        document.getElementById('applicant-phone-number').value = '';
                        document.getElementById('applicant-pinfl').value = '';
                        document.getElementById('applicant-inn').value = '';
                        document.getElementById('applicant-gender').value = '1';

                        document.getElementById('applicant-info-display').classList.add('d-none');
                        document.getElementById('cadaster-search').classList.add('d-none');
                    }
                };

                // Listen for checkbox changes
                if (isOwnerApplicant) {
                    isOwnerApplicant.addEventListener('change', toggleOwnerAsApplicant);
                }

                // If old values exist, show the display sections
                @if (old('applicant.lastName'))
                    document.getElementById('applicant-info-display').classList.remove('d-none');
                    document.getElementById('owner-info').classList.remove('d-none');
                    document.getElementById('cadaster-search').classList.remove('d-none');
                @endif

                @if (old('owner.lastName'))
                    document.getElementById('owner-info-display').classList.remove('d-none');
                    document.getElementById('cadaster-search').classList.remove('d-none');
                @endif

                @if (old('property.address'))
                    document.getElementById('property-info').classList.remove('d-none');
                    document.getElementById('calculation').classList.remove('d-none');
                @endif

                // Cadaster search functionality
                cadasterSearchBtn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    clearErrors();

                    // Show loading state
                    cadasterSearchBtn.disabled = true;
                    cadasterSearchBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('messages.loading') ?? 'Yuklanmoqda...' }}';

                    try {
                        const response = await fetch('/fetch-cadaster', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
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
                            document.getElementById('property-short-address').value = property
                                .shortAddress || '';
                            document.getElementById('property-street').value = property.street || '';
                            document.getElementById('property-tip-text').value = property.tipText || '';
                            document.getElementById('property-vid-text').value = property.vidText || '';
                            document.getElementById('property-area').value = property.objectArea || '';
                            document.getElementById('property-region').value = property.region || '';
                            document.getElementById('property-district').value = property.district || '';

                            // Cost - display formatted, store clean value
                            const costValue = parseInt(property.cost || 0);
                            document.getElementById('property-cost-display').value = costValue
                                .toLocaleString('uz-UZ');
                            document.getElementById('property-cost').value = costValue;

                            // Populate hidden fields
                            document.getElementById('property-tip').value = property.tip || '';
                            document.getElementById('property-vid').value = property.vid || '';
                            document.getElementById('property-area-l').value = property.objectAreaL || '0';
                            document.getElementById('property-area-u').value = property.objectAreaU || '0';
                            document.getElementById('property-region-id').value = property.regionId || '';
                            document.getElementById('property-district-id').value = property.districtId ||
                                '';
                            document.getElementById('property-dom-num').value = property.domNum || '';
                            document.getElementById('property-kvartira-num').value = property.kvartiraNum ||
                                '';
                            document.getElementById('property-neighborhood').value = property
                                .neighborhood || '';
                            document.getElementById('property-neighborhood-id').value = property
                                .neighborhoodId ||
                                '';

                            // Show property info
                            document.getElementById('property-info').classList.remove('d-none');

                            // Populate owner information if available
                            if (property.subjects && property.subjects.length > 0) {
                                const owner = property.subjects[0];
                                document.getElementById('owner-name').value = owner.name || '';
                                document.getElementById('owner-passport').value = owner.passport || '';
                                document.getElementById('owner-inn').value = owner.inn || owner.pinfl || '';

                                document.getElementById('owner-type').value = owner.type || '1';
                                document.getElementById('owner-pinfl').value = owner.pinfl || '';
                                document.getElementById('owner-percent').value = owner.percent || '';

                                document.getElementById('owner-info').classList.remove('d-none');
                            }

                            // Show calculation section
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
                        cadasterSearchBtn.innerHTML = '{{ __('messages.search') ?? 'Qidirish' }}';
                    }
                });

                // Applicant information search functionality
                if (applicantInfoBtn) {
                    applicantInfoBtn.addEventListener('click', async function(e) {
                        e.preventDefault();
                        clearErrors();

                        const passportSeria = document.getElementById('applicant-passport-series');
                        const passportNumber = document.getElementById('applicant-passport-number');
                        const birthDate = document.getElementById('applicant-birth-date');

                        // Show loading state
                        applicantInfoBtn.disabled = true;
                        applicantInfoBtn.innerHTML =
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

                                document.getElementById('applicant-last-name').value = personInfo
                                    .lastNameLatin || '';
                                document.getElementById('applicant-first-name').value = personInfo
                                    .firstNameLatin || '';
                                document.getElementById('applicant-middle-name').value = personInfo
                                    .middleNameLatin || '';
                                document.getElementById('applicant-address').value = personInfo.address ||
                                    '';

                                // Populate hidden fields
                                document.getElementById('applicant-pinfl').value = personInfo.pinfl || '';
                                document.getElementById('applicant-inn').value = personInfo.inn || '';
                                document.getElementById('applicant-gender').value = personInfo.sex || '1';

                                document.getElementById('applicant-info-display').classList.remove(
                                    'd-none');

                                // Show owner and cadaster sections
                                document.getElementById('owner-info').classList.remove('d-none');
                                document.getElementById('cadaster-search').classList.remove('d-none');
                            } else {
                                alert(data.message || 'Ma\'lumot topilmadi');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Tarmoq xatosi yuz berdi');
                        } finally {
                            applicantInfoBtn.disabled = false;
                            applicantInfoBtn.innerHTML = '{{ __('messages.search') ?? 'Qidirish' }}';
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
                        ownerInfoBtn.innerHTML =
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

                                document.getElementById('owner-last-name').value = ownerInfo
                                    .lastNameLatin || '';
                                document.getElementById('owner-first-name').value = ownerInfo
                                    .firstNameLatin || '';
                                document.getElementById('owner-middle-name').value = ownerInfo
                                    .middleNameLatin || '';
                                document.getElementById('owner-address').value = ownerInfo.address ||
                                    '';

                                // Populate hidden fields
                                document.getElementById('owner-pinfl').value = ownerInfo.pinfl || '';
                                document.getElementById('owner-inn').value = ownerInfo.inn || '';
                                document.getElementById('owner-gender').value = ownerInfo.sex || '1';

                                document.getElementById('owner-info-display').classList.remove(
                                    'd-none');

                                // Show cadaster section
                                document.getElementById('cadaster-search').classList.remove('d-none');
                            } else {
                                alert(data.message || 'Ma\'lumot topilmadi');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Tarmoq xatosi yuz berdi');
                        } finally {
                            ownerInfoBtn.disabled = false;
                            ownerInfoBtn.innerHTML = '{{ __('messages.search') ?? 'Qidirish' }}';
                        }
                    });
                }

                // Insurance calculation
                let insuredAmountCalc = {{ old('insurance_amount', 10000000) }};
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

                    // Calculate premium (0.5% for property insurance)
                    premiumAmountCalc = (0.5 * insuredAmountCalc) / 100;

                    document.getElementById("amount").innerHTML = formatter.format(insuredAmountCalc);
                    document.getElementById("premium").innerHTML = formatter.format(premiumAmountCalc);
                    document.getElementById("value_amount").innerHTML = formatter.format(insuredAmountCalc);

                    let rangeInput = document.querySelector(".range-after--input");
                    rangeInput.style.setProperty("--premium-amount", `'${formatterDR.format(insuredAmountCalc)}'`);
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
