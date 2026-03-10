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
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white" style="font-size:11px;background:#22c55e;">
                <i class="bi bi-check"></i>
            </div>
            <span class="text-sm font-medium" style="color:#16a34a;">{{ __('messages.applicant') }}</span>
        </div>
        <div class="flex-1" style="height:1px;background:#bbf7d0;"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white" style="font-size:11px;font-weight:700;">2</div>
            <span class="text-sm font-semibold text-blue-600">{{ __('messages.vehicle_details') }}</span>
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

            {{-- Applicant Summary --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#f0fdf4;border-radius:10px;">
                    @if($applicant['type'] === 'organization')
                        <i class="bi bi-building" style="color:#16a34a;"></i>
                    @else
                        <i class="bi bi-person" style="color:#16a34a;"></i>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    @if($applicant['type'] === 'organization')
                        @php $org = $applicant['organization'] @endphp
                        <p class="font-semibold text-gray-900 text-sm mb-0 text-truncate">{{ $org['name'] }}</p>
                        <p class="text-xs text-gray-400 mb-0">{{ __('messages.inn') }}: {{ $org['inn'] }} &bull; {{ $org['representativeName'] }}</p>
                    @else
                        @php $p = $applicant['person'] @endphp
                        <p class="font-semibold text-gray-900 text-sm mb-0">{{ $p['lastname'] }} {{ $p['firstname'] }} {{ $p['middlename'] }}</p>
                        <p class="text-xs text-gray-400 mb-0">PINFL: {{ $p['pinfl'] }} &bull; {{ $p['passport_seria'] }}{{ $p['passport_number'] }}</p>
                    @endif
                </div>
                <div class="d-flex align-items-center justify-content-center rounded-circle shrink-0" style="width:24px;height:24px;background:#f0fdf4;">
                    <i class="bi bi-check" style="color:#16a34a;font-size:12px;"></i>
                </div>
            </div>

            {{-- Vehicle Form --}}
            <form action="{{ route('osgop.storeVehicle', ['locale' => getCurrentLocale()]) }}"
                  method="POST" novalidate>
                @csrf

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;border-radius:10px;">
                            <i class="bi bi-truck text-blue-600"></i>
                        </div>
                        <span class="font-semibold text-gray-800">{{ __('messages.vehicle_details') ?? 'Transport vositasi ma\'lumotlari' }}</span>
                    </div>

                    <div class="p-5">
                        <div class="row g-3">

                            <div class="col-md-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('messages.gov_number') ?? 'Davlat raqami' }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="vehicle[gov_number]"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('vehicle.gov_number') border-red-400 bg-red-50 @else border-gray-200 @enderror"
                                    placeholder="01A123AA" maxlength="10" style="text-transform:uppercase;"
                                    value="{{ old('vehicle.gov_number', $vehicle['gov_number'] ?? '') }}"
                                    required>
                                @error('vehicle.gov_number')
                                    <p class="mt-1 text-xs text-red-500 d-flex align-items-center gap-1">
                                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('messages.tech_passport_series') ?? 'TP seriya' }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="vehicle[tech_passport_seria]"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('vehicle.tech_passport_seria') border-red-400 bg-red-50 @else border-gray-200 @enderror"
                                    placeholder="AA" maxlength="4" style="text-transform:uppercase;"
                                    value="{{ old('vehicle.tech_passport_seria', $vehicle['tech_passport_seria'] ?? '') }}"
                                    required>
                                @error('vehicle.tech_passport_seria')
                                    <p class="mt-1 text-xs text-red-500 d-flex align-items-center gap-1">
                                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('messages.tech_passport_number') ?? 'TP raqami' }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="vehicle[tech_passport_number]"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('vehicle.tech_passport_number') border-red-400 bg-red-50 @else border-gray-200 @enderror"
                                    placeholder="1234567" maxlength="10" inputmode="numeric"
                                    value="{{ old('vehicle.tech_passport_number', $vehicle['tech_passport_number'] ?? '') }}"
                                    required>
                                @error('vehicle.tech_passport_number')
                                    <p class="mt-1 text-xs text-red-500 d-flex align-items-center gap-1">
                                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <div class="px-5 py-4 border-top d-flex align-items-center justify-content-between" style="background:#f9fafb;">
                        <a href="{{ route('osgop.index', ['locale' => getCurrentLocale()]) }}"
                           class="d-flex align-items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-decoration-none"
                           style="border:1.5px solid #d1d5db;background:#fff;color:#374151;">
                            <i class="bi bi-arrow-left"></i>{{ __('messages.back') }}
                        </a>
                        <button type="submit"
                            class="d-flex align-items-center gap-2 px-4 py-2 text-white font-semibold rounded-xl text-sm border-0"
                            style="background:#2563eb;">
                            {{ __('messages.next_step') }} <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

            </form>
        </div>

        {{-- ── Sidebar ── --}}
        <div class="col-lg-4">

            <div class="rounded-2xl text-white p-5 mb-4" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                <div class="d-flex align-items-center justify-content-center rounded-xl mb-3" style="width:40px;height:40px;background:rgba(255,255,255,0.2);">
                    <i class="bi bi-shield-check fs-5"></i>
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
