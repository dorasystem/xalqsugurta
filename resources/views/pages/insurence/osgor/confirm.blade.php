@extends('layouts.app')
@section('title', __('insurance.osgor.page_title'))

@section('content')
<section class="py-8 bg-gray-50 min-h-screen">
<div class="container">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-5">
        <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shrink-0">
            <i class="bi bi-person-badge text-white"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-900 mb-0">{{ __('insurance.osgor.title') }}</h1>
            <p class="text-sm text-gray-500 mb-0">{{ __('insurance.osgor.description') }}</p>
        </div>
    </div>

    {{-- Steps --}}
    <div class="flex items-center gap-2 mb-5">
        @foreach([1 => __('messages.applicant'), 2 => __('messages.insurance_summary'), 3 => __('messages.confirm_details')] as $num => $label)
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white" style="font-size:11px;{{ $num < 3 ? 'background:#22c55e;' : 'background:#2563eb;font-weight:700;' }}">
                    @if($num < 3)<i class="bi bi-check"></i>@else {{ $num }}@endif
                </div>
                <span class="text-sm {{ $num < 3 ? 'font-medium' : 'font-semibold text-blue-600' }}" style="{{ $num < 3 ? 'color:#16a34a;' : '' }}">{{ $label }}</span>
            </div>
            @if($num < 3)
                <div class="flex-1" style="height:1px;background:{{ $num < 2 ? '#bbf7d0' : '#bbf7d0' }};"></div>
            @endif
        @endforeach
    </div>

    <div class="row g-4">

        {{-- ── Data Column ── --}}
        <div class="col-lg-8">

            <x-insurence.error-block />

            {{-- Organization card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                        <i class="bi bi-building text-blue-600"></i>
                    </div>
                    <span class="font-semibold text-gray-800">{{ __('messages.organization_info_title') }}</span>
                </div>
                <div class="row g-2">
                    @foreach([
                        [__('messages.inn'), $applicant['inn']],
                        [__('messages.organization_name'), $applicant['name']],
                        [__('messages.representative_name'), $applicant['representativeName']],
                        [__('messages.address'), $applicant['address']],
                        [__('messages.oked'), $applicant['oked']],
                        [__('messages.position'), $applicant['position']],
                        [__('messages.phone_number'), $applicant['phone']],
                    ] as [$lbl, $val])
                    <div class="col-sm-6">
                        <p class="text-xs text-gray-400 mb-0">{{ $lbl }}</p>
                        <p class="text-sm font-semibold text-gray-800 mb-0">{{ $val ?: '—' }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Calculation card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                        <i class="bi bi-calculator text-blue-600"></i>
                    </div>
                    <span class="font-semibold text-gray-800">{{ __('messages.insurance_summary') }}</span>
                </div>
                <div class="row g-2">
                    @foreach([
                        [__('messages.fond_oplaty_truda'), number_format($calculation['fot'], 0, '.', ' ') . ' UZS'],
                        [__('messages.insurance_sum'), number_format($calculation['insurance_sum'], 0, '.', ' ') . ' UZS'],
                        [__('messages.insurance_premium'), number_format($calculation['insurance_premium'], 0, '.', ' ') . ' UZS'],
                        [__('messages.insurance_rate'), $calculation['insurance_rate'] . '%'],
                        [__('messages.start_date'), $calculation['start_date']],
                        [__('messages.end_date'), $calculation['end_date']],
                    ] as [$lbl, $val])
                    <div class="col-sm-6">
                        <p class="text-xs text-gray-400 mb-0">{{ $lbl }}</p>
                        <p class="text-sm font-semibold text-gray-800 mb-0">{{ $val }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('osgor.getCalculator', ['locale' => getCurrentLocale()]) }}"
                   class="d-flex align-items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-decoration-none"
                   style="border:1.5px solid #d1d5db;background:#fff;color:#374151;">
                    <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
                </a>
                <form action="{{ route('osgor.storeApplication', ['locale' => getCurrentLocale()]) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="d-flex align-items-center gap-2 px-5 py-2 text-white font-semibold rounded-xl text-sm border-0"
                        style="background:#2563eb;">
                        {{ __('messages.proceed_to_payment') }} <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>

        </div>

        {{-- ── Sidebar ── --}}
        <div class="col-lg-4">
            <div class="rounded-2xl text-white p-5 mb-4" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                <div class="w-10 h-10 rounded-xl mb-3 d-flex align-items-center justify-content-center" style="background:rgba(255,255,255,0.2);">
                    <i class="bi bi-person-badge fs-5"></i>
                </div>
                <h3 class="font-bold fs-6 mb-1">{{ __('insurance.osgor.title') }}</h3>
                <p class="mb-0 text-sm opacity-75">{{ __('insurance.osgor.description') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-receipt text-blue-600"></i>
                    <span class="text-sm font-semibold text-gray-700">{{ __('messages.insurance_summary') }}</span>
                </div>
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-xs text-gray-400">{{ __('messages.insurance_sum') }}</span>
                        <span class="text-sm font-bold text-gray-800">{{ number_format($calculation['insurance_sum'], 0, '.', ' ') }} UZS</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-xs text-gray-400">{{ __('messages.insurance_premium') }}</span>
                        <span class="text-sm font-bold text-green-600">{{ number_format($calculation['insurance_premium'], 0, '.', ' ') }} UZS</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</section>
@endsection
