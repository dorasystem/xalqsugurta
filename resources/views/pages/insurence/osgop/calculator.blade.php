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
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white" style="font-size:11px;background:#22c55e;">
                <i class="bi bi-check"></i>
            </div>
            <span class="text-sm font-medium" style="color:#16a34a;">{{ __('messages.vehicle_details') }}</span>
        </div>
        <div class="flex-1" style="height:1px;background:#bbf7d0;"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white" style="font-size:11px;font-weight:700;">3</div>
            <span class="text-sm font-semibold text-blue-600">{{ __('messages.insurance_summary') }}</span>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── Form Column ── --}}
        <div class="col-lg-8">

            <x-insurence.error-block />

            {{-- Applicant Summary --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-xl shrink-0" style="width:36px;height:36px;min-width:36px;background:#f0fdf4;border-radius:10px;">
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

            {{-- Vehicle Summary --}}
            @php
                $vType = $vehicleTypes->firstWhere('provider_vehicle_type_id', $vehicle['vehicle_type_id'] ?? null);
            @endphp
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-xl shrink-0" style="width:36px;height:36px;min-width:36px;background:#f0fdf4;border-radius:10px;">
                    <i class="bi bi-truck" style="color:#16a34a;"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-gray-900 text-sm mb-0">
                        {{ $vehicle['gov_number'] ?? '—' }}
                        @if($vehicle['model_custom_name'] ?? null)
                            <span class="text-gray-400 fw-normal ms-1">· {{ $vehicle['model_custom_name'] }}</span>
                        @endif
                    </p>
                    <p class="text-xs text-gray-400 mb-0">
                        TP: {{ ($vehicle['tech_passport_seria'] ?? '') }}{{ ($vehicle['tech_passport_number'] ?? '') }}
                        @if($vType) &bull; {{ $vType->name }} @endif
                        @if($vehicle['number_of_seats'] ?? null) &bull; {{ $vehicle['number_of_seats'] }} {{ __('messages.seats') ?? 'o\'rindiq' }} @endif
                    </p>
                </div>
                <div class="d-flex align-items-center justify-content-center rounded-circle shrink-0" style="width:24px;height:24px;background:#f0fdf4;">
                    <i class="bi bi-check" style="color:#16a34a;font-size:12px;"></i>
                </div>
            </div>

            {{-- Calculator Card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;border-radius:10px;">
                        <i class="bi bi-calculator text-blue-600"></i>
                    </div>
                    <span class="font-semibold text-gray-800">{{ __('messages.insurance_summary') ?? 'Kalkulyator' }}</span>
                </div>

                <div class="p-5">
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('messages.start_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="start_date"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label for="insurance_term_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('messages.insurance_period') ?? 'Muddat' }} <span class="text-red-500">*</span>
                            </label>
                            <select id="insurance_term_id"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition bg-white">
                                <option value="">{{ __('messages.select') ?? '— Tanlang —' }}</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->provider_term_id }}"
                                            data-months="{{ $term->months }}">
                                        {{ $term->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('messages.end_date') }}
                            </label>
                            <input type="date" id="end_date"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-100 text-sm"
                                style="background:#f9fafb;color:#9ca3af;cursor:default;"
                                readonly>
                            <p class="mt-1 text-xs text-gray-400 mb-0">{{ __('messages.auto_calculated') ?? 'Avtomatik hisoblanadi' }}</p>
                        </div>

                    </div>

                    {{-- Error --}}
                    <div id="calc_err" class="d-none mt-4 d-flex align-items-start gap-2 px-4 py-3 rounded-xl" style="background:#fef2f2;border:1px solid #fecaca;">
                        <i class="bi bi-exclamation-circle mt-1 shrink-0" style="color:#ef4444;"></i>
                        <p class="text-sm mb-0" id="calc_err_text" style="color:#dc2626;"></p>
                    </div>

                    <div class="mt-4">
                        <button type="button" id="calc_btn"
                            class="d-flex align-items-center gap-2 px-4 py-2 rounded-xl font-semibold text-sm transition-colors"
                            style="border:2px solid #2563eb;color:#2563eb;background:transparent;"
                            disabled>
                            <span class="btn-text d-flex align-items-center gap-2">
                                <i class="bi bi-calculator"></i>{{ __('messages.calculate') ?? 'Hisoblash' }}
                            </span>
                            <span class="btn-loading d-none d-flex align-items-center gap-2">
                                <span class="spinner-border spinner-border-sm" role="status" style="width:14px;height:14px;border-width:2px;"></span>
                                {{ __('messages.loading') ?? 'Yuklanmoqda...' }}
                            </span>
                        </button>
                    </div>
                </div>

                <div class="px-5 py-4 border-top d-flex align-items-center justify-content-between" style="background:#f9fafb;">
                    <a href="{{ route('osgop.getVehicle', ['locale' => getCurrentLocale()]) }}"
                       class="d-flex align-items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-decoration-none"
                       style="border:1.5px solid #d1d5db;background:#fff;color:#374151;">
                        <i class="bi bi-arrow-left"></i>{{ __('messages.back') }}
                    </a>
                    <form id="confirm_form" method="POST" action="{{ route('osgop.storeApplication', ['locale' => getCurrentLocale()]) }}">
                        @csrf
                        <button type="submit" id="confirm_btn"
                            class="d-flex align-items-center gap-2 px-4 py-2 text-white font-semibold rounded-xl text-sm border-0"
                            style="background:#16a34a;"
                            disabled>
                            {{ __('messages.confirm_and_continue') }} <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>

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
                        <span id="sidebar_sum" class="text-sm font-bold text-gray-800">0 UZS</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-xs text-gray-400">{{ __('messages.insurance_premium') }}</span>
                        <span id="sidebar_premium" class="text-sm font-bold text-green-600">0 UZS</span>
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
document.addEventListener('DOMContentLoaded', function () {
    var csrf       = document.querySelector('meta[name="csrf-token"]').content;
    var calcBtn    = document.getElementById('calc_btn');
    var confirmBtn = document.getElementById('confirm_btn');
    var calcErr    = document.getElementById('calc_err');
    var calcErrTxt = document.getElementById('calc_err_text');
    var startEl    = document.getElementById('start_date');
    var termEl     = document.getElementById('insurance_term_id');
    var endEl      = document.getElementById('end_date');

    var vehicleTypeId = @json((int) ($vehicle['vehicle_type_id'] ?? 0));
    var numberOfSeats = @json((int) ($vehicle['number_of_seats'] ?? 0));

    function calcEndDate() {
        var opt = termEl.options[termEl.selectedIndex];
        if (!startEl.value || !opt || !opt.dataset.months) { endEl.value = ''; return; }
        var d = new Date(startEl.value);
        d.setMonth(d.getMonth() + parseInt(opt.dataset.months));
        d.setDate(d.getDate() - 1);
        endEl.value = d.toISOString().split('T')[0];
    }

    function toggleCalcBtn() {
        var off = !(termEl.value && startEl.value);
        calcBtn.disabled = off;
        calcBtn.style.opacity = off ? '0.4' : '1';
        calcBtn.style.cursor  = off ? 'not-allowed' : 'pointer';
    }

    startEl.addEventListener('change', function () { calcEndDate(); toggleCalcBtn(); });
    termEl.addEventListener('change',  function () { calcEndDate(); toggleCalcBtn(); });
    toggleCalcBtn();

    // Confirm tugmasi boshlang'ich holati
    confirmBtn.style.opacity = '0.4';

    function fmt(n) { return Number(n || 0).toLocaleString('uz-UZ') + ' UZS'; }

    calcBtn.addEventListener('click', async function () {
        calcErr.classList.add('d-none');
        calcBtn.querySelector('.btn-text').classList.add('d-none');
        calcBtn.querySelector('.btn-loading').classList.remove('d-none');
        calcBtn.disabled = true;

        try {
            var res = await fetch('{{ route('osgop.calculate', ['locale' => getCurrentLocale()]) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({
                    insurance_term_id: parseInt(termEl.value),
                    vehicle_type_id:   vehicleTypeId,
                    number_of_seats:   numberOfSeats,
                    start_date:        startEl.value,
                }),
            });
            var data = await res.json();

            if (!res.ok || !data.success) {
                calcErrTxt.textContent = data.message || '{{ __('messages.error_occurred') ?? 'Xatolik yuz berdi' }}';
                calcErr.classList.remove('d-none');
                return;
            }

            var r = data.data;
            document.getElementById('sidebar_premium').textContent = fmt(r.insurancePremium ?? r.premium ?? 0);
            document.getElementById('sidebar_sum').textContent     = fmt(r.insuranceSum     ?? r.sumInsured ?? 0);

            confirmBtn.disabled = false;
            confirmBtn.style.opacity = '1';
        } catch (e) {
            calcErrTxt.textContent = '{{ __('messages.error_occurred') ?? 'Xatolik yuz berdi' }}';
            calcErr.classList.remove('d-none');
        } finally {
            calcBtn.querySelector('.btn-text').classList.remove('d-none');
            calcBtn.querySelector('.btn-loading').classList.add('d-none');
            calcBtn.disabled = false;
            toggleCalcBtn();
        }
    });

    // Confirm submits the form
    confirmBtn.addEventListener('click', function () {
        document.getElementById('confirm_form').submit();
    });

    calcEndDate();
});
</script>
@endpush
