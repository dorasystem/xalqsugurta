@extends('layouts.app')
@section('title', __('insurance.accident.product_name'))

@section('content')
<section class="py-8 bg-gray-50 min-h-screen">
<div class="container">

    <x-insurence.page-header
        icon="bi-person-heart"
        :title="__('insurance.accident.product_name')"
        :subtitle="__('insurance.accident.subtitle')"
    />

    {{-- Steps --}}
    <div class="flex items-center gap-2 mb-5">
        @foreach([1 => __('messages.applicant'), 2 => __('messages.insured_persons'), 3 => __('messages.confirm_details')] as $num => $label)
            <div class="flex items-center gap-2">
                @if($num < 3)
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-white" style="font-size:11px;background:#22c55e;"><i class="bi bi-check"></i></div>
                    <span class="text-sm font-medium" style="color:#16a34a;">{{ $label }}</span>
                @else
                    <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white" style="font-size:11px;font-weight:700;">3</div>
                    <span class="text-sm font-semibold text-blue-600">{{ $label }}</span>
                @endif
            </div>
            @if($num < 3)
                <div class="flex-1" style="height:1px;background:#bbf7d0;"></div>
            @endif
        @endforeach
    </div>

    <div class="row g-4">

        {{-- ── Data Column ── --}}
        <div class="col-lg-8">

            <x-insurence.error-block />

            {{-- Applicant card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                        <i class="bi bi-person text-blue-600"></i>
                    </div>
                    <span class="font-semibold text-gray-800">{{ __('messages.applicant_info_title') }}</span>
                </div>
                <div class="row g-2">
                    @foreach([
                        [__('messages.last_name'), $applicant['lastname']],
                        [__('messages.first_name'), $applicant['firstname']],
                        [__('messages.middle_name'), $applicant['middlename']],
                        [__('messages.passport_series'), $applicant['passport_seria']],
                        [__('messages.passport_number'), $applicant['passport_number']],
                        [__('messages.birth_date'), $applicant['birth_date']],
                        [__('messages.phone_number'), $applicant['phone']],
                    ] as [$lbl, $val])
                    <div class="col-sm-6">
                        <p class="text-xs text-gray-400 mb-0">{{ $lbl }}</p>
                        <p class="text-sm font-semibold text-gray-800 mb-0">{{ $val ?: '—' }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Persons list --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                        <i class="bi bi-people text-blue-600"></i>
                    </div>
                    <span class="font-semibold text-gray-800">{{ __('messages.insured_persons') }} ({{ count($persons) }})</span>
                </div>
                @foreach($persons as $i => $person)
                <div class="px-5 py-3 {{ $i < count($persons)-1 ? 'border-bottom' : '' }}">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="d-flex align-items-center justify-content-center rounded-circle" style="width:22px;height:22px;min-width:22px;background:#eff6ff;font-size:11px;font-weight:700;color:#2563eb;">{{ $i+1 }}</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $person['lastname'] }} {{ $person['firstname'] }}</span>
                    </div>
                    <p class="text-xs text-gray-400 mb-0 ps-4">
                        {{ $person['passport_seria'] }}{{ $person['passport_number'] }} &bull;
                        {{ __('messages.sum_insured') }}: {{ number_format($person['sum_insured'], 0, '.', ' ') }} UZS &bull;
                        {{ __('messages.insurance_premium') }}: {{ number_format($person['insurance_premium'], 0, '.', ' ') }} UZS
                    </p>
                </div>
                @endforeach
            </div>

            {{-- Calculation card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                        <i class="bi bi-calendar-check text-blue-600"></i>
                    </div>
                    <span class="font-semibold text-gray-800">{{ __('messages.insurance_summary') }}</span>
                </div>
                <div class="row g-2">
                    @foreach([
                        [__('messages.start_date'), $calculation['start_date']],
                        [__('messages.end_date'), $calculation['end_date']],
                        [__('messages.insurance_sum'), number_format($calculation['total_sum'], 0, '.', ' ') . ' UZS'],
                        [__('messages.insurance_premium'), number_format($calculation['total_premium'], 0, '.', ' ') . ' UZS'],
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
                <a href="{{ route('accident.getPersons', ['locale' => getCurrentLocale()]) }}"
                   class="d-flex align-items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-decoration-none"
                   style="border:1.5px solid #d1d5db;background:#fff;color:#374151;">
                    <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
                </a>
                <form action="{{ route('accident.storeApplication', ['locale' => getCurrentLocale()]) }}" method="POST">
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
            <x-insurence.insurance-sidebar
                :title="__('insurance.accident.product_name')"
                :description="__('insurance.accident.subtitle')"
                :insuranceSum="number_format($calculation['total_sum'], 0, '.', ' ') . ' UZS'"
                :insurancePremium="number_format($calculation['total_premium'], 0, '.', ' ') . ' UZS'"
            />
        </div>

    </div>
</div>
</section>
@endsection
