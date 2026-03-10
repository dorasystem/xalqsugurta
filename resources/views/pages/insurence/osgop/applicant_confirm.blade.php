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
            <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white" style="font-size:11px;">
                <i class="bi bi-check"></i>
            </div>
            <span class="text-sm text-blue-600 font-medium">{{ __('messages.applicant') }}</span>
        </div>
        <div class="flex-1" style="height:1px;background:#bfdbfe;"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white" style="font-size:11px;font-weight:700;">2</div>
            <span class="text-sm font-semibold text-blue-600">{{ __('messages.confirm_details') }}</span>
        </div>
        <div class="flex-1" style="height:1px;background:#e5e7eb;"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-400" style="font-size:11px;font-weight:700;">3</div>
            <span class="text-sm text-gray-400">{{ __('messages.vehicle_details') }}</span>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── Form Column ── --}}
        <div class="col-lg-8">

            <x-insurence.error-block />

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 d-flex align-items-center justify-content-center" style="width:36px;height:36px;min-width:36px;border-radius:10px;">
                        @if($applicant['type'] === 'organization')
                            <i class="bi bi-building text-blue-600"></i>
                        @else
                            <i class="bi bi-person text-blue-600"></i>
                        @endif
                    </div>
                    <span class="font-semibold text-gray-800">
                        @if($applicant['type'] === 'organization')
                            {{ __('messages.organization_info_title') }}
                        @else
                            {{ __('messages.person_info_title') }}
                        @endif
                    </span>
                </div>

                <div class="p-5">
                    @if($applicant['type'] === 'organization')
                        @php $org = $applicant['organization'] @endphp
                        <div class="mb-4">
                            <p class="text-xs text-gray-400 font-medium mb-1">{{ __('messages.organization_name') }}</p>
                            <p class="font-bold text-gray-900 mb-0">{{ $org['name'] }}</p>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <p class="text-xs text-gray-400 font-medium mb-1">{{ __('messages.inn') }}</p>
                                <p class="font-semibold text-gray-800 mb-0">{{ $org['inn'] }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-xs text-gray-400 font-medium mb-1">{{ __('messages.oked') }}</p>
                                <p class="font-semibold text-gray-800 mb-0">{{ $org['oked'] ?: '—' }}</p>
                            </div>
                        </div>
                        <div class="mb-4">
                            <p class="text-xs text-gray-400 font-medium mb-1">{{ __('messages.representative_name') ?? 'Rahbar' }}</p>
                            <p class="text-gray-800 mb-0">{{ $org['representativeName'] }} <span class="text-gray-400">({{ $org['position'] }})</span></p>
                        </div>
                        @if($org['address'])
                        <div class="mb-4">
                            <p class="text-xs text-gray-400 font-medium mb-1">{{ __('messages.address') }}</p>
                            <p class="text-gray-800 mb-0">{{ $org['address'] }}</p>
                        </div>
                        @endif
                    @else
                        @php $p = $applicant['person'] @endphp
                        <div class="mb-4">
                            <p class="text-xs text-gray-400 font-medium mb-1">{{ __('messages.full_name') }}</p>
                            <p class="font-bold text-gray-900 mb-0">{{ $p['lastname'] }} {{ $p['firstname'] }} {{ $p['middlename'] }}</p>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <p class="text-xs text-gray-400 font-medium mb-1">{{ __('insurance.passport.series_number') }}</p>
                                <p class="font-semibold text-gray-800 mb-0">{{ $p['passport_seria'] }}{{ $p['passport_number'] }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-xs text-gray-400 font-medium mb-1">PINFL</p>
                                <p class="font-semibold text-gray-800 mb-0">{{ $p['pinfl'] }}</p>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <p class="text-xs text-gray-400 font-medium mb-1">{{ __('insurance.passport.birth_date') }}</p>
                                <p class="text-gray-800 mb-0">{{ $p['birth_date'] }}</p>
                            </div>
                            @if($p['phone'] ?? null)
                            <div class="col-sm-6">
                                <p class="text-xs text-gray-400 font-medium mb-1">{{ __('messages.phone_number') }}</p>
                                <p class="text-gray-800 mb-0">{{ $p['phone'] }}</p>
                            </div>
                            @endif
                        </div>
                    @endif

                    <div class="d-flex align-items-start gap-2 px-4 py-3 rounded-xl" style="background:#eff6ff;">
                        <i class="bi bi-info-circle text-blue-500 mt-1 shrink-0"></i>
                        <p class="text-xs mb-0" style="color:#1d4ed8;">{{ __('messages.verify_data_accuracy') ?? 'Ma\'lumotlar to\'g\'riligini tekshiring' }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('osgop.confirmApplicant', ['locale' => getCurrentLocale()]) }}" method="POST">
                @csrf
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('osgop.index', ['locale' => getCurrentLocale()]) }}"
                       class="d-flex align-items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-decoration-none"
                       style="border:1.5px solid #d1d5db;background:#fff;color:#374151;">
                        <i class="bi bi-arrow-left"></i>{{ __('messages.back') }}
                    </a>
                    <button type="submit"
                        class="flex-1 py-2 d-flex align-items-center justify-content-center gap-2 text-white font-semibold rounded-xl text-sm border-0"
                        style="background:#2563eb;">
                        {{ __('messages.confirm_and_continue') }} <i class="bi bi-arrow-right"></i>
                    </button>
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
