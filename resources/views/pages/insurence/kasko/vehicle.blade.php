@extends('layouts.app')
@section('title', __('insurance.kasko.page_title'))

@section('content')
<section class="py-8 bg-gray-50 min-h-screen">
<div class="container">

    <x-insurence.page-header icon="bi-car-front-fill" :title="__('insurance.kasko.page_title')" :subtitle="__('insurance.kasko.subtitle')" />

    {{-- Steps --}}
    <div class="flex items-center gap-2 mb-5">
        @foreach([1 => __('messages.applicant'), 2 => __('insurance.kasko.vehicle_info'), 3 => __('messages.confirm_details')] as $n => $lbl)
            <div class="flex items-center gap-2">
                @if($n < 2)
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-white" style="font-size:11px;background:#22c55e;"><i class="bi bi-check"></i></div>
                    <span class="text-sm font-medium" style="color:#16a34a;">{{ $lbl }}</span>
                @elseif($n === 2)
                    <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white" style="font-size:11px;font-weight:700;">2</div>
                    <span class="text-sm font-semibold text-blue-600">{{ $lbl }}</span>
                @else
                    <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-400" style="font-size:11px;font-weight:700;">3</div>
                    <span class="text-sm text-gray-400">{{ $lbl }}</span>
                @endif
            </div>
            @if($n < 3)
                <div class="flex-1" style="height:1px;background:{{ $n < 2 ? '#bbf7d0' : '#e5e7eb' }};"></div>
            @endif
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-8">

            <x-insurence.error-block />

            {{-- Applicant summary --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-xl"
                    style="width:36px;height:36px;min-width:36px;background:#f0fdf4;">
                    <i class="bi bi-person-check" style="color:#16a34a;"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 text-sm mb-0">{{ $applicant['lastname'] }} {{ $applicant['firstname'] }}</p>
                    <p class="text-xs text-gray-400 mb-0">{{ $applicant['passport_seria'] }}{{ $applicant['passport_number'] }} &bull; {{ $applicant['phone'] }}</p>
                </div>
                <i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:18px;"></i>
            </div>

            <form action="{{ route('kasko.storeVehicle', ['locale' => getCurrentLocale()]) }}" method="POST" id="kasko_form">
                @csrf

                {{-- ── Vehicle Search ── --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                    <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                            <i class="bi bi-car-front text-blue-600"></i>
                        </div>
                        <span class="font-semibold text-gray-800">{{ __('insurance.kasko.vehicle_info') }}</span>
                    </div>
                    <div class="p-5">

                        {{-- Search row --}}
                        <div class="row g-3 align-items-end mb-3">
                            <div class="col-sm-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('insurance.kasko.gov_number') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="gov_input" placeholder="40Z202EA" maxlength="8"
                                    style="text-transform:uppercase;"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                    value="{{ $vehicle['regnumber'] ?? '' }}">
                            </div>
                            <div class="col-sm-3">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('insurance.kasko.tp_seria') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="tp_seria_input" placeholder="AAC" maxlength="3"
                                    style="text-transform:uppercase;"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                    value="{{ $vehicle['tp_seria'] ?? '' }}">
                            </div>
                            <div class="col-sm-3">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('insurance.kasko.tp_number') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="tp_number_input" placeholder="3332961" maxlength="7" inputmode="numeric"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                    value="{{ $vehicle['tp_number'] ?? '' }}">
                            </div>
                            <div class="col-sm-2">
                                <button type="button" id="vehicle_btn"
                                    class="w-full d-flex align-items-center justify-content-center gap-2 py-2 text-white font-semibold rounded-xl text-sm border-0"
                                    style="background:#2563eb;">
                                    <i class="bi bi-search" id="vehicle_btn_icon"></i>
                                    <span id="vehicle_btn_text">{{ __('messages.search') }}</span>
                                </button>
                            </div>
                        </div>

                        <div id="vehicle_error" class="mb-3 text-xs text-red-500" style="display:none;"></div>

                        {{-- Vehicle result --}}
                        <div id="vehicle_result" style="{{ !empty($vehicle['regnumber']) ? '' : 'display:none;' }}">
                            <div class="rounded-xl overflow-hidden mb-3" style="border:1.5px solid #e5e7eb;background:#fff;">
                                <div class="px-4 py-3 d-flex align-items-center gap-2" style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                                    <i class="bi bi-car-front-fill text-blue-600" style="font-size:16px;"></i>
                                    <span class="text-sm font-bold text-gray-800" id="disp_vehicle_name">
                                        {{ isset($vehicle['brand']) ? $vehicle['brand'] . ' ' . $vehicle['model'] : '' }}
                                    </span>
                                </div>
                                <div class="p-4">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <p class="text-xs text-gray-400 mb-1">{{ __('insurance.kasko.gov_number') }}</p>
                                            <p class="text-sm font-semibold text-gray-800 mb-0" id="disp_regnumber">{{ $vehicle['regnumber'] ?? '' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-xs text-gray-400 mb-1">{{ __('insurance.kasko.year') }}</p>
                                            <p class="text-sm font-semibold text-gray-800 mb-0" id="disp_year">{{ $vehicle['year'] ?? '' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-xs text-gray-400 mb-1">{{ __('insurance.kasko.body_number') }}</p>
                                            <p class="text-sm font-semibold text-gray-800 mb-0 text-break" id="disp_body">{{ $vehicle['body_number'] ?? '' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-xs text-gray-400 mb-1">{{ __('insurance.kasko.engine_number') }}</p>
                                            <p class="text-sm font-semibold text-gray-800 mb-0 text-break" id="disp_engine">{{ $vehicle['engine_number'] ?? '' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Hidden fields --}}
                            <input type="hidden" name="regnumber"    id="h_regnumber"    value="{{ $vehicle['regnumber']     ?? '' }}">
                            <input type="hidden" name="tp_seria"     id="h_tp_seria"     value="{{ $vehicle['tp_seria']      ?? '' }}">
                            <input type="hidden" name="tp_number"    id="h_tp_number"    value="{{ $vehicle['tp_number']     ?? '' }}">
                            <input type="hidden" name="brand"        id="h_brand"        value="{{ $vehicle['brand']         ?? '' }}">
                            <input type="hidden" name="model"        id="h_model"        value="{{ $vehicle['model']         ?? '' }}">
                            <input type="hidden" name="year"         id="h_year"         value="{{ $vehicle['year']          ?? '' }}">
                            <input type="hidden" name="body_number"  id="h_body_number"  value="{{ $vehicle['body_number']   ?? '' }}">
                            <input type="hidden" name="engine_number" id="h_engine_number" value="{{ $vehicle['engine_number'] ?? '' }}">
                            <input type="hidden" name="vehicle_type" id="h_vehicle_type" value="{{ $vehicle['vehicle_type']  ?? 2 }}">
                        </div>

                    </div>
                </div>

                {{-- ── Insurance Calculation ── --}}
                <div id="calc_section" style="{{ !empty($vehicle['regnumber']) ? '' : 'display:none;' }}">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                        <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                                <i class="bi bi-calculator text-blue-600"></i>
                            </div>
                            <p class="font-semibold text-gray-800 mb-0">{{ __('messages.insurance_calculation') }}</p>
                        </div>
                        <div class="p-5">

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="text-sm font-semibold text-gray-700 mb-0">{{ __('messages.insurance_sum') }}</label>
                                    <span class="text-base font-bold text-blue-600" id="amt_display">
                                        {{ number_format($calculation['insurance_amount'] ?? 50000000, 0, '.', ' ') }} UZS
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between text-xs text-gray-400 mb-2">
                                    <span>1 000 000</span><span>500 000 000</span>
                                </div>
                                <input type="range" id="amt_slider" min="1000000" max="500000000" step="1000000"
                                    value="{{ $calculation['insurance_amount'] ?? 50000000 }}" class="w-full">
                                <input type="hidden" name="insurance_amount" id="h_insurance_amount"
                                    value="{{ $calculation['insurance_amount'] ?? 50000000 }}">
                            </div>

                            <div class="rounded-xl p-4 mb-4 d-flex align-items-center justify-content-between gap-3"
                                style="background:#f9fafb;border:1px solid #e5e7eb;">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold mb-1 text-uppercase" style="letter-spacing:.5px;">
                                        {{ __('messages.insurance_premium') }} (3%)
                                    </p>
                                    <p class="text-2xl font-bold text-gray-800 mb-0" id="prem_display">
                                        {{ number_format(($calculation['insurance_amount'] ?? 50000000) * 0.03, 0, '.', ' ') }} UZS
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-center rounded-2xl"
                                    style="width:48px;height:48px;min-width:48px;background:#e5e7eb;">
                                    <i class="bi bi-shield-check text-gray-600" style="font-size:20px;"></i>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        {{ __('messages.start_date') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="payment_start_date" id="start_date"
                                        min="{{ now()->format('Y-m-d') }}"
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('payment_start_date') border-red-400 @enderror"
                                        value="{{ $calculation['payment_start_date'] ?? now()->format('Y-m-d') }}"
                                        required>
                                    @error('payment_start_date')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="col-sm-6">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.end_date') }}</label>
                                    <input type="date" id="end_date" readonly
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-100 text-sm"
                                        style="background:#f9fafb;color:#9ca3af;"
                                        value="{{ $calculation['payment_end_date'] ?? '' }}">
                                    <p class="mt-1 text-xs text-gray-400">{{ __('messages.auto_calculated') }} (1 {{ __('messages.year') }})</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('kasko.index', ['locale' => getCurrentLocale()]) }}"
                        class="d-flex align-items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-decoration-none"
                        style="border:1.5px solid #d1d5db;background:#fff;color:#374151;">
                        <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                    <button type="submit" id="submit_btn"
                        class="d-flex align-items-center gap-2 px-5 py-2 text-white font-semibold rounded-xl text-sm border-0"
                        style="background:{{ !empty($vehicle['regnumber']) ? '#2563eb' : '#9ca3af' }};"
                        {{ empty($vehicle['regnumber']) ? 'disabled' : '' }}>
                        {{ __('messages.next_step') }} <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

            </form>
        </div>

        <div class="col-lg-4">
            <x-insurence.insurance-sidebar
                :title="__('insurance.kasko.product_name')"
                :description="__('insurance.kasko.subtitle')"
                :insuranceSum="!empty($calculation['insurance_amount']) ? number_format($calculation['insurance_amount'], 0, '.', ' ') . ' UZS' : '0 UZS'"
                :insurancePremium="!empty($calculation['insurance_premium']) ? number_format($calculation['insurance_premium'], 0, '.', ' ') . ' UZS' : '0 UZS'"
            />
        </div>
    </div>
</div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    var CSRF = document.querySelector('meta[name="csrf-token"]').content;
    function fmt(n) { return Number(n).toLocaleString('ru-RU'); }

    // ── Slider ────────────────────────────────────────────────────────────────
    var slider   = document.getElementById('amt_slider');
    var hAmt     = document.getElementById('h_insurance_amount');
    var amtEl    = document.getElementById('amt_display');
    var premEl   = document.getElementById('prem_display');
    var sideSum  = document.getElementById('sidebar_sum');
    var sidePrem = document.getElementById('sidebar_premium');

    function updateSlider() {
        var val  = parseInt(slider.value, 10);
        var prem = Math.round(val * 3 / 100);
        hAmt.value = val;
        amtEl.textContent  = fmt(val)  + ' UZS';
        premEl.textContent = fmt(prem) + ' UZS';
        if (sideSum)  sideSum.textContent  = fmt(val)  + ' UZS';
        if (sidePrem) sidePrem.textContent = fmt(prem) + ' UZS';
    }
    slider.addEventListener('input', updateSlider);
    updateSlider();

    // ── Dates ─────────────────────────────────────────────────────────────────
    document.getElementById('start_date').addEventListener('change', function () {
        if (!this.value) return;
        var d = new Date(this.value);
        d.setFullYear(d.getFullYear() + 1);
        d.setDate(d.getDate() - 1);
        document.getElementById('end_date').value = d.toISOString().split('T')[0];
    });
    document.getElementById('start_date').dispatchEvent(new Event('change'));

    // ── Vehicle search ────────────────────────────────────────────────────────
    var vBtn     = document.getElementById('vehicle_btn');
    var vBtnIcon = document.getElementById('vehicle_btn_icon');
    var vBtnText = document.getElementById('vehicle_btn_text');

    vBtn.addEventListener('click', async function () {
        var gov    = document.getElementById('gov_input').value.trim().toUpperCase();
        var seria  = document.getElementById('tp_seria_input').value.trim().toUpperCase();
        var number = document.getElementById('tp_number_input').value.trim();
        var errWrap = document.getElementById('vehicle_error');
        errWrap.style.display = 'none';

        if (!gov || !seria || !number) {
            errWrap.textContent = '{{ __('messages.fill_required_fields') ?? 'Barcha maydonlarni to\'ldiring' }}';
            errWrap.style.display = '';
            return;
        }

        vBtn.disabled = true;
        vBtnIcon.className = 'spinner-border spinner-border-sm';
        vBtnText.textContent = '...';

        try {
            var res  = await fetch('{{ route('kasko.findVehicle', ['locale' => getCurrentLocale()]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    gov_number: gov,
                    tp_seria:   seria,
                    tp_number:  number,
                }),
            });
            var json = await res.json();

            if (!json.success || !json.data) {
                errWrap.textContent = json.message ?? '{{ __('messages.vehicle_not_found') }}';
                errWrap.style.display = '';
                return;
            }

            var v = json.data;

            document.getElementById('disp_vehicle_name').textContent = (v.brand + ' ' + v.model).trim() || gov;
            document.getElementById('disp_regnumber').textContent    = v.regnumber;
            document.getElementById('disp_year').textContent         = v.year;
            document.getElementById('disp_body').textContent         = v.body_number;
            document.getElementById('disp_engine').textContent       = v.engine_number;

            document.getElementById('h_regnumber').value     = v.regnumber;
            document.getElementById('h_tp_seria').value      = v.tp_seria;
            document.getElementById('h_tp_number').value     = v.tp_number;
            document.getElementById('h_brand').value         = v.brand || gov;
            document.getElementById('h_model').value         = v.model;
            document.getElementById('h_year').value          = v.year;
            document.getElementById('h_body_number').value   = v.body_number;
            document.getElementById('h_engine_number').value = v.engine_number;
            document.getElementById('h_vehicle_type').value  = v.vehicle_type;

            document.getElementById('vehicle_result').style.display = '';
            document.getElementById('calc_section').style.display   = '';

            var btn = document.getElementById('submit_btn');
            btn.disabled = false;
            btn.style.background = '#2563eb';

            updateSlider();

        } catch (e) {
            errWrap.textContent = '{{ __('messages.error_occurred') }}';
            errWrap.style.display = '';
        } finally {
            vBtn.disabled = false;
            vBtnIcon.className = 'bi bi-search';
            vBtnText.textContent = '{{ __('messages.search') }}';
        }
    });
})();
</script>
@endpush
