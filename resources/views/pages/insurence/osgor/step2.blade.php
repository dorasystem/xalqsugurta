@extends('layouts.app')
@section('title', __('insurance.osgor.page_title'))

@section('content')
<section class="container-fluid product-page py-4">
    <div class="container">

        <x-insurence.multi-step-stepper :totalSteps="4" :currentStep="2" :labels="[
            __('messages.step_contract_info'),
            __('messages.step_organization'),
            __('messages.step_policy_info'),
            __('messages.step_review'),
        ]" />

        <div class="row g-4 mt-1">
            <div class="col-lg-8">
                <x-insurence.error-block />

                <form action="{{ route('osgor.step2.store', ['locale' => getCurrentLocale()]) }}" method="POST" novalidate>
                    @csrf

                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h4 class="card-title mb-0 fs-5">
                                <span class="badge bg-primary me-2">2</span>
                                {{ __('messages.step_organization') }}
                            </h4>
                        </div>
                        <div class="card-body p-4">

                            {{-- INN search --}}
                            <div class="row g-2 mb-3">
                                <div class="col">
                                    <label class="form-label fw-semibold">
                                        {{ __('messages.inn') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="org_inn" name="organization[inn]" inputmode="numeric"
                                        maxlength="9" placeholder="{{ __('messages.inn_placeholder') }}"
                                        class="form-control @error('organization.inn') is-invalid @enderror"
                                        value="{{ old('organization.inn', $data['organization']['inn'] ?? '') }}"
                                        autocomplete="off">
                                    @error('organization.inn')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-auto d-flex align-items-end">
                                    <button type="button" id="org_search_btn" class="btn btn-primary">
                                        <span class="btn-text"><i class="bi bi-search"></i></span>
                                        <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm"></span></span>
                                    </button>
                                </div>
                            </div>
                            <div id="org_search_error" class="alert alert-danger d-none py-2 small"></div>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">{{ __('messages.organization_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="org_name" name="organization[name]"
                                        class="form-control @error('organization.name') is-invalid @enderror"
                                        value="{{ old('organization.name', $data['organization']['name'] ?? '') }}"
                                        placeholder="{{ __('messages.organization_name_placeholder') }}" required>
                                    @error('organization.name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">{{ __('messages.address') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="org_address" name="organization[address]"
                                        class="form-control @error('organization.address') is-invalid @enderror"
                                        value="{{ old('organization.address', $data['organization']['address'] ?? '') }}"
                                        placeholder="{{ __('messages.address_placeholder') }}" required>
                                    @error('organization.address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.oked') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="org_oked" name="organization[oked]"
                                        class="form-control @error('organization.oked') is-invalid @enderror"
                                        value="{{ old('organization.oked', $data['organization']['oked'] ?? '') }}"
                                        placeholder="{{ __('messages.oked_placeholder') }}" required>
                                    @error('organization.oked')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.phone_number') }} <span class="text-danger">*</span></label>
                                    <input type="tel" id="org_phone" name="organization[phone]" inputmode="numeric"
                                        class="form-control @error('organization.phone') is-invalid @enderror"
                                        value="{{ old('organization.phone', $data['organization']['phone'] ?? '') }}"
                                        placeholder="+998 90 123 45 67" maxlength="17" required>
                                    @error('organization.phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.region') }} <span class="text-danger">*</span></label>
                                    <select name="organization[region_id]" id="org_region_id"
                                        class="form-select @error('organization.region_id') is-invalid @enderror" required>
                                        <option value="">{{ __('messages.select_region') }}</option>
                                        @foreach (($regions ?? []) as $region)
                                            <option value="{{ $region['id'] }}"
                                                {{ old('organization.region_id', $data['organization']['region_id'] ?? '') == $region['id'] ? 'selected' : '' }}>
                                                @if(App::getLocale() === 'ru'){{ $region['name'] ?? $region['nameRu'] ?? '' }}
                                                @elseif(App::getLocale() === 'en'){{ $region['nameEn'] ?? $region['name'] ?? '' }}
                                                @else{{ $region['nameUz'] ?? $region['name'] ?? '' }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('organization.region_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.district') }} <span class="text-danger">*</span></label>
                                    <select name="organization[district_id]" id="org_district_id"
                                        class="form-select @error('organization.district_id') is-invalid @enderror" required>
                                        <option value="">{{ __('messages.select_district') }}</option>
                                    </select>
                                    @error('organization.district_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <input type="hidden" name="organization[ownership_form_id]"
                                value="{{ old('organization.ownership_form_id', $data['organization']['ownership_form_id'] ?? '130') }}">
                        </div>

                        <div class="card-footer bg-transparent d-flex justify-content-between gap-2 py-3">
                            <a href="{{ route('osgor.step1', ['locale' => getCurrentLocale()]) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> {{ __('messages.prev_step') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                {{ __('messages.next_step') }} <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                @php $s1 = session('osgor.step1') @endphp
                @if($s1)
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold small">{{ __('messages.step_contract_info') }}</div>
                    <div class="card-body p-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ __('messages.start_date') }}</span>
                            <strong>{{ $s1['payment_start_date'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('messages.end_date') }}</span>
                            <strong>{{ $s1['payment_end_date'] }}</strong>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var currentLocale = '{{ App::getLocale() }}';
    var oldDistrictId = '{{ old("organization.district_id", $data["organization"]["district_id"] ?? "") }}';

    // Phone formatter
    function fmtPhone(val) {
        var d = (val || '').replace(/\D/g, '');
        if (d.startsWith('998')) d = d.slice(0, 12); else if (d.length > 9) d = '998' + d.slice(-9);
        var s = d.length <= 9 ? d.padStart(9,'0') : d.slice(-9);
        return d ? '+998 ' + s.slice(0,2) + ' ' + s.slice(2,5) + ' ' + s.slice(5,7) + ' ' + s.slice(7,9) : '';
    }
    var phoneEl = document.getElementById('org_phone');
    if (phoneEl) {
        phoneEl.value = fmtPhone(phoneEl.value);
        phoneEl.addEventListener('input', function () { this.value = fmtPhone(this.value); });
    }

    // Districts
    var regionSel   = document.getElementById('org_region_id');
    var districtSel = document.getElementById('org_district_id');
    var allDistricts = [];

    function getDistName(d) {
        if (currentLocale === 'ru') return d.name || d.nameRu || d.nameUz || '';
        if (currentLocale === 'en') return d.nameEn || d.name || d.nameUz || '';
        return d.nameUz || d.name || '';
    }

    function populateDistricts(regionId, selectId) {
        districtSel.innerHTML = '<option value="">{{ __("messages.select_district") }}</option>';
        if (!regionId) return;
        allDistricts.filter(function (d) { return d.regionId == regionId; }).forEach(function (d) {
            var opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = getDistName(d);
            if (String(d.id) === String(selectId)) opt.selected = true;
            districtSel.appendChild(opt);
        });
    }

    fetch('{{ route("get-districts") }}').then(function (r) { return r.json(); }).then(function (data) {
        if (data.success && data.data) {
            allDistricts = data.data;
            if (regionSel.value) populateDistricts(regionSel.value, oldDistrictId);
        }
    }).catch(function () {});

    regionSel.addEventListener('change', function () { populateDistricts(this.value, ''); });

    // INN search
    var innEl     = document.getElementById('org_inn');
    var searchBtn = document.getElementById('org_search_btn');
    var errEl     = document.getElementById('org_search_error');

    function setField(id, val) { var el = document.getElementById(id); if (el && val) el.value = val; }

    async function doInnSearch(inn) {
        if (!inn || inn.length !== 9) { errEl.textContent = '{{ __("messages.inn_invalid") }}'; errEl.classList.remove('d-none'); return; }
        errEl.classList.add('d-none');
        searchBtn.querySelector('.btn-text').classList.add('d-none');
        searchBtn.querySelector('.btn-loading').classList.remove('d-none');
        searchBtn.disabled = true;
        try {
            var res = await fetch('/get-company-info', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ inn: inn, product_name: 'osgor_company_info' })
            });
            var data = await res.json();
            if (!res.ok || !data.success) { errEl.textContent = data.message || '{{ __("messages.company_not_found") }}'; errEl.classList.remove('d-none'); return; }
            var c = data.data?.result || data.data;
            if (c) {
                setField('org_name', c.name);
                setField('org_address', c.address);
                setField('org_oked', (c.oked || '') + (c.okedTitle ? ' ' + c.okedTitle : ''));
                if (c.phone) phoneEl.value = fmtPhone(c.phone);
                if (c.regionId) {
                    regionSel.value = c.regionId;
                    populateDistricts(c.regionId, c.districtId || '');
                }
            }
        } catch (e) { errEl.textContent = '{{ __("messages.error_occurred") }}'; errEl.classList.remove('d-none'); }
        finally {
            searchBtn.querySelector('.btn-text').classList.remove('d-none');
            searchBtn.querySelector('.btn-loading').classList.add('d-none');
            searchBtn.disabled = false;
        }
    }

    if (searchBtn) searchBtn.addEventListener('click', function () { doInnSearch(innEl?.value.trim()); });
    if (innEl) innEl.addEventListener('input', function () { if (this.value.trim().length === 9) doInnSearch(this.value.trim()); });
});
</script>
@endpush
