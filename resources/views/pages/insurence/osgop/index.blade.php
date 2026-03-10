@extends('layouts.app')
@section('title', __('insurance.osgop.page_title'))

@section('content')
<section class="py-8 bg-gray-50 min-h-screen">
<div class="container">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-5">
        <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shrink-0">
            <i class="bi bi-shield-check text-white"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-900 mb-0">{{ __('insurance.osgop.title') }}</h1>
            <p class="text-sm text-gray-500 mb-0">{{ __('insurance.osgop.description') }}</p>
        </div>
    </div>

    {{-- Steps --}}
    <div class="flex items-center gap-2 mb-5">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white" style="font-size:11px;font-weight:700;">1</div>
            <span class="text-sm font-semibold text-blue-600">{{ __('messages.applicant') }}</span>
        </div>
        <div class="flex-1" style="height:1px;background:#e5e7eb;"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-400" style="font-size:11px;font-weight:700;">2</div>
            <span class="text-sm text-gray-400">{{ __('messages.vehicle_details') }}</span>
        </div>
        <div class="flex-1" style="height:1px;background:#e5e7eb;"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-400" style="font-size:11px;font-weight:700;">3</div>
            <span class="text-sm text-gray-400">{{ __('messages.insurance_summary') }}</span>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── Form Column ── --}}
        <div class="col-lg-8">

            <x-insurence.error-block />

            {{-- Type Toggle --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-1 d-flex mb-4" style="gap:4px;">
                <button type="button" id="btn_org" onclick="switchType('organization')"
                    class="w-50 d-flex align-items-center justify-content-center gap-2 py-2 border-0 rounded-xl text-sm font-semibold"
                    style="background:#2563eb;color:#fff;">
                    <i class="bi bi-building"></i> {{ __('messages.organization') }}
                </button>
                <button type="button" id="btn_person" onclick="switchType('person')"
                    class="w-50 d-flex align-items-center justify-content-center gap-2 py-2 border-0 rounded-xl text-sm font-semibold"
                    style="background:transparent;color:#6b7280;">
                    <i class="bi bi-person"></i> {{ __('messages.person') }}
                </button>
            </div>

            {{-- Organization Form --}}
            <div id="org-section">
                <form action="{{ route('osgop.storeCompanyApplicant', ['locale' => getCurrentLocale()]) }}" method="POST">
                    @csrf
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
                        <label for="inn" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('messages.inn') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="inn" name="inn" inputmode="numeric" maxlength="9"
                            placeholder="123456789"
                            class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('inn') border-red-400 bg-red-50 @else border-gray-200 @enderror"
                            value="{{ old('inn') }}">
                        @error('inn')
                            <p class="mt-1 text-xs text-red-500 d-flex align-items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <x-insurence.offerta-agreement :product="$product" />
                    <button type="submit" class="w-full py-2 text-white font-semibold rounded-xl d-flex align-items-center justify-content-center gap-2 text-sm border-0" style="background:#2563eb;">
                        {{ __('messages.next_step') }} <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>

            {{-- Person Form --}}
            <div id="person-section" style="display:none;">
                <form action="{{ route('osgop.storeIndividualApplicant', ['locale' => getCurrentLocale()]) }}" method="POST">
                    @csrf
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('insurance.passport.series') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="passport_seria" maxlength="4" placeholder="AA"
                                    style="text-transform:uppercase;"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('passport_seria') border-red-400 bg-red-50 @else border-gray-200 @enderror"
                                    value="{{ old('passport_seria') }}">
                                @error('passport_seria')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('insurance.passport.number') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="passport_number" inputmode="numeric" maxlength="7" placeholder="1234567"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('passport_number') border-red-400 bg-red-50 @else border-gray-200 @enderror"
                                    value="{{ old('passport_number') }}">
                                @error('passport_number')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('insurance.passport.birth_date') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="birth_date"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('birth_date') border-red-400 bg-red-50 @else border-gray-200 @enderror"
                                    value="{{ old('birth_date') }}">
                                @error('birth_date')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('messages.phone_number') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" name="phone" inputmode="tel" placeholder="+998 90 123 45 67"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('phone') border-red-400 bg-red-50 @else border-gray-200 @enderror"
                                    value="{{ old('phone') }}">
                                @error('phone')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <x-insurence.offerta-agreement :product="$product" />
                    <button type="submit" class="w-full py-2 text-white font-semibold rounded-xl d-flex align-items-center justify-content-center gap-2 text-sm border-0" style="background:#2563eb;">
                        {{ __('messages.next_step') }} <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>

        </div>

        {{-- ── Sidebar ── --}}
        <div class="col-lg-4">

            <div class="rounded-2xl text-white p-5 mb-4" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                <div class="w-10 h-10 rounded-xl mb-3 d-flex align-items-center justify-content-center" style="background:rgba(255,255,255,0.2);">
                    <i class="bi bi-shield-check text-white fs-5"></i>
                </div>
                <h3 class="font-bold fs-6 mb-1">{{ __('insurance.osgop.title') }}</h3>
                <p class="mb-0 text-sm opacity-75">{{ __('insurance.osgop.description') }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-receipt text-blue-600"></i>
                    <span class="text-sm font-semibold text-gray-700">{{ __('messages.insurance_summary') }}</span>
                </div>
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-xs text-gray-400">{{ __('messages.insurance_sum') }}</span>
                        <span class="text-sm font-bold text-gray-800">0 UZS</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-xs text-gray-400">{{ __('messages.insurance_premium') }}</span>
                        <span class="text-sm font-bold text-green-600">0 UZS</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
</section>
@endsection

@push('scripts')
<script>
function switchType(type) {
    var isOrg = type === 'organization';
    document.getElementById('org-section').style.display    = isOrg ? '' : 'none';
    document.getElementById('person-section').style.display = isOrg ? 'none' : '';

    var btnOrg    = document.getElementById('btn_org');
    var btnPerson = document.getElementById('btn_person');

    btnOrg.style.background = isOrg ? '#2563eb' : 'transparent';
    btnOrg.style.color      = isOrg ? '#fff'     : '#6b7280';
    btnPerson.style.background = isOrg ? 'transparent' : '#2563eb';
    btnPerson.style.color      = isOrg ? '#6b7280'     : '#fff';
}

document.addEventListener('DOMContentLoaded', function () {
    switchType('{{ old('insurant_type', 'organization') }}');
});
</script>
@endpush
