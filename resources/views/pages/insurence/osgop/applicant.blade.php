@extends('layouts.app')
@section('title', __('insurance.osgop.page_title'))

@section('content')
<section class="container-fluid product-page py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">

                <x-insurence.error-block />

                @php $type = $raw['type'] ?? 'organization'; @endphp

                <form action="{{ route('osgop.confirmApplicant', ['locale' => getCurrentLocale()]) }}" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="insurant_type" value="{{ $type }}">

                    @if($type === 'organization')
                    {{-- ── Organization form ─────────────────────────────────── --}}
                    @php $org = $raw['organization'] ?? [] @endphp

                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 fs-6 fw-semibold">
                                <i class="bi bi-building text-primary me-2"></i>{{ __('messages.organization_info_title') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.inn') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="organization[inn]"
                                        class="form-control @error('organization.inn') is-invalid @enderror"
                                        value="{{ old('organization.inn', $org['inn'] ?? '') }}" required>
                                    @error('organization.inn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.oked') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="organization[oked]"
                                        class="form-control @error('organization.oked') is-invalid @enderror"
                                        value="{{ old('organization.oked', $org['oked'] ?? '') }}" required>
                                    @error('organization.oked')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('messages.organization_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="organization[name]"
                                        class="form-control @error('organization.name') is-invalid @enderror"
                                        value="{{ old('organization.name', $org['name'] ?? '') }}" required>
                                    @error('organization.name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        {{ __('messages.representative_name') ?? 'Vakil ismi' }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="organization[representative_name]"
                                        class="form-control @error('organization.representative_name') is-invalid @enderror"
                                        value="{{ old('organization.representative_name', $org['representative_name'] ?? '') }}" required>
                                    @error('organization.representative_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        {{ __('messages.position') ?? 'Lavozim' }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="organization[position]"
                                        class="form-control @error('organization.position') is-invalid @enderror"
                                        value="{{ old('organization.position', $org['position'] ?? '') }}" required>
                                    @error('organization.position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('messages.address') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="organization[address]"
                                        class="form-control @error('organization.address') is-invalid @enderror"
                                        value="{{ old('organization.address', $org['address'] ?? '') }}" required>
                                    @error('organization.address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.phone_number') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="organization[phone]"
                                        class="form-control @error('organization.phone') is-invalid @enderror"
                                        value="{{ old('organization.phone', $org['phone'] ?? '') }}"
                                        placeholder="+998901234567" required>
                                    @error('organization.phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.region') }} <span class="text-danger">*</span></label>
                                    <select name="organization[region_id]"
                                        class="form-select @error('organization.region_id') is-invalid @enderror" required>
                                        <option value="">{{ __('messages.select_region') }}</option>
                                        @foreach ($regions as $r)
                                        <option value="{{ $r['id'] }}"
                                            {{ old('organization.region_id', $org['region_id'] ?? '') == $r['id'] ? 'selected' : '' }}>
                                            @if(App::getLocale() === 'ru'){{ $r['name'] ?? '' }}
                                            @elseif(App::getLocale() === 'en'){{ $r['nameEn'] ?? $r['name'] ?? '' }}
                                            @else{{ $r['nameUz'] ?? $r['name'] ?? '' }}
                                            @endif
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('organization.region_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <input type="hidden" name="organization[ownership_form_id]"
                                    value="{{ old('organization.ownership_form_id', $org['ownership_form_id'] ?? '130') }}">
                            </div>
                        </div>

                        <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                            <a href="{{ route('osgop.index', ['locale' => getCurrentLocale()]) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>{{ __('messages.prev_step') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                {{ __('messages.next_step') }} <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    @else
                    {{-- ── Person form ────────────────────────────────────────── --}}
                    @php $p = $raw['person'] ?? [] @endphp

                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 fs-6 fw-semibold">
                                <i class="bi bi-person text-primary me-2"></i>{{ __('messages.person_info_title') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('insurance.person.last_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="person[lastname]"
                                        class="form-control @error('person.lastname') is-invalid @enderror"
                                        value="{{ old('person.lastname', $p['lastname'] ?? '') }}" required>
                                    @error('person.lastname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('insurance.person.first_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="person[firstname]"
                                        class="form-control @error('person.firstname') is-invalid @enderror"
                                        value="{{ old('person.firstname', $p['firstname'] ?? '') }}" required>
                                    @error('person.firstname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('insurance.person.middle_name') }}</label>
                                    <input type="text" name="person[middlename]"
                                        class="form-control"
                                        value="{{ old('person.middlename', $p['middlename'] ?? '') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">PINFL <span class="text-danger">*</span></label>
                                    <input type="text" name="person[pinfl]" inputmode="numeric" maxlength="14"
                                        class="form-control @error('person.pinfl') is-invalid @enderror"
                                        value="{{ old('person.pinfl', $p['pinfl'] ?? '') }}" required>
                                    @error('person.pinfl')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">{{ __('insurance.passport.series') }}</label>
                                    <input type="text" name="person[passport_seria]" readonly
                                        class="form-control bg-light"
                                        value="{{ old('person.passport_seria', $p['passport_seria'] ?? '') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">{{ __('insurance.passport.number') }}</label>
                                    <input type="text" name="person[passport_number]" readonly
                                        class="form-control bg-light"
                                        value="{{ old('person.passport_number', $p['passport_number'] ?? '') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('insurance.passport.birth_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="person[birth_date]"
                                        class="form-control @error('person.birth_date') is-invalid @enderror"
                                        value="{{ old('person.birth_date', $p['birth_date'] ?? '') }}" required>
                                    @error('person.birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('messages.gender') ?? 'Jins' }} <span class="text-danger">*</span></label>
                                    <select name="person[gender]"
                                        class="form-select @error('person.gender') is-invalid @enderror" required>
                                        <option value="">—</option>
                                        <option value="m" {{ old('person.gender', $p['gender'] ?? '') === 'm' ? 'selected' : '' }}>{{ __('messages.male') ?? 'Erkak' }}</option>
                                        <option value="f" {{ old('person.gender', $p['gender'] ?? '') === 'f' ? 'selected' : '' }}>{{ __('messages.female') ?? 'Ayol' }}</option>
                                    </select>
                                    @error('person.gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('messages.phone_number') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="person[phone]"
                                        class="form-control @error('person.phone') is-invalid @enderror"
                                        value="{{ old('person.phone', $p['phone'] ?? '') }}"
                                        placeholder="+998901234567" required>
                                    @error('person.phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.region') }} <span class="text-danger">*</span></label>
                                    <select name="person[region_id]"
                                        class="form-select @error('person.region_id') is-invalid @enderror" required>
                                        <option value="">{{ __('messages.select_region') }}</option>
                                        @foreach ($regions as $r)
                                        <option value="{{ $r['id'] }}"
                                            {{ old('person.region_id', $p['region_id'] ?? '') == $r['id'] ? 'selected' : '' }}>
                                            @if(App::getLocale() === 'ru'){{ $r['name'] ?? '' }}
                                            @elseif(App::getLocale() === 'en'){{ $r['nameEn'] ?? $r['name'] ?? '' }}
                                            @else{{ $r['nameUz'] ?? $r['name'] ?? '' }}
                                            @endif
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('person.region_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('messages.address') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="person[address]"
                                        class="form-control @error('person.address') is-invalid @enderror"
                                        value="{{ old('person.address', $p['address'] ?? '') }}" required>
                                    @error('person.address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <input type="hidden" name="person[resident_type]" value="1">
                                <input type="hidden" name="person[country_id]"    value="210">
                            </div>
                        </div>

                        <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                            <a href="{{ route('osgop.index', ['locale' => getCurrentLocale()]) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>{{ __('messages.prev_step') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                {{ __('messages.next_step') }} <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                    @endif

                </form>
            </div>
        </div>
    </div>
</section>
@endsection
