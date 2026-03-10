@extends('layouts.app')
@section('title', __('insurance.property.page_title'))

@section('content')
<section class="py-8 bg-gray-50 min-h-screen">
<div class="container">

    <x-insurence.page-header icon="bi-house" :title="__('insurance.property.page_title')" :subtitle="__('insurance.property.subtitle')" />

    {{-- Steps --}}
    <div class="flex items-center gap-2 mb-5">
        @foreach([1 => __('messages.applicant'), 2 => __('messages.property_info'), 3 => __('messages.confirm_details')] as $n => $lbl)
            <div class="flex items-center gap-2">
                @if($n < 3)
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-white" style="font-size:11px;background:#22c55e;"><i class="bi bi-check"></i></div>
                    <span class="text-sm font-medium" style="color:#16a34a;">{{ $lbl }}</span>
                @else
                    <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white" style="font-size:11px;font-weight:700;">3</div>
                    <span class="text-sm font-semibold text-blue-600">{{ $lbl }}</span>
                @endif
            </div>
            @if($n < 3)<div class="flex-1" style="height:1px;background:#bbf7d0;"></div>@endif
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-8">

            <x-insurence.error-block />

            {{-- Applicant card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#f0fdf4;">
                        <i class="bi bi-person" style="color:#16a34a;"></i>
                    </div>
                    <span class="font-semibold text-gray-800">{{ __('messages.applicant_info_title') }}</span>
                </div>
                <div class="row g-2">
                    @foreach([
                        [__('messages.full_name'), $applicant['lastname'] . ' ' . $applicant['firstname'] . ' ' . ($applicant['middlename'] ?? '')],
                        [__('insurance.passport.series') . ' / ' . __('insurance.passport.number'), $applicant['passport_seria'] . $applicant['passport_number']],
                        [__('insurance.passport.birth_date'), $applicant['birth_date']],
                        [__('messages.phone_number'), $applicant['phone']],
                    ] as [$lbl, $val])
                    <div class="col-sm-6">
                        <p class="text-xs text-gray-400 mb-0">{{ $lbl }}</p>
                        <p class="text-sm font-semibold text-gray-800 mb-0">{{ trim($val) ?: '—' }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Property info card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                        <i class="bi bi-house text-blue-600"></i>
                    </div>
                    <span class="font-semibold text-gray-800">{{ __('messages.property_info') }}</span>
                </div>
                <div class="row g-2">
                    @foreach([
                        [__('messages.cadaster_number'), $property['cadasterNumber']],
                        [__('messages.address'), $property['shortAddress']],
                        [__('messages.area'), ($property['objectArea'] ? $property['objectArea'] . ' м²' : '—')],
                        [__('messages.property_object_type'), ($property['tipText'] ?? '—')],
                        [__('messages.property_object_view'), ($property['vidText'] ?? '—')],
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
                        [__('messages.insurance_sum'), number_format($calculation['insurance_amount'], 0, '.', ' ') . ' UZS'],
                        [__('messages.insurance_premium'), number_format($calculation['insurance_premium'], 0, '.', ' ') . ' UZS'],
                        [__('messages.start_date'), $calculation['payment_start_date']],
                        [__('messages.end_date'), $calculation['payment_end_date']],
                    ] as [$lbl, $val])
                    <div class="col-sm-6">
                        <p class="text-xs text-gray-400 mb-0">{{ $lbl }}</p>
                        <p class="text-sm font-semibold text-gray-800 mb-0">{{ $val }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('property.getProperty', ['locale' => getCurrentLocale()]) }}"
                   class="d-flex align-items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-decoration-none"
                   style="border:1.5px solid #d1d5db;background:#fff;color:#374151;">
                    <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
                </a>
                <form action="{{ route('property.storeApplication', ['locale' => getCurrentLocale()]) }}" method="POST">
                    @csrf
                    <button type="submit" class="d-flex align-items-center gap-2 px-5 py-2 text-white font-semibold rounded-xl text-sm border-0" style="background:#2563eb;">
                        {{ __('messages.proceed_to_payment') }} <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>

        </div>
        <div class="col-lg-4">
            <x-insurence.insurance-sidebar
                :title="__('insurance.property.product_name')"
                :description="__('insurance.property.subtitle')"
                :insuranceSum="number_format($calculation['insurance_amount'], 0, '.', ' ') . ' UZS'"
                :insurancePremium="number_format($calculation['insurance_premium'], 0, '.', ' ') . ' UZS'"
            />
        </div>
    </div>
</div>
</section>
@endsection
