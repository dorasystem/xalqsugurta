@extends('layouts.app')
@section('title', __('insurance.gas.page_title'))

@section('content')
<section class="py-8 bg-gray-50 min-h-screen">
<div class="container">

    <x-insurence.page-header icon="bi-balloon" :title="__('insurance.gas.page_title')" :subtitle="__('insurance.gas.subtitle')" />

    {{-- Steps --}}
    <div class="flex items-center gap-2 mb-5">
        @foreach ([1 => __('messages.applicant'), 2 => __('messages.property_info'), 3 => __('messages.confirm_details')] as $n => $lbl)
            <div class="flex items-center gap-2">
                @if ($n < 2)
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
            @if ($n < 3)
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

            <form action="{{ route('gas.storeProperty', ['locale' => getCurrentLocale()]) }}" method="POST" id="gas_prop_form">
                @csrf

                {{-- ── Cadaster Search ── --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                    <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                            <i class="bi bi-geo-alt text-blue-600"></i>
                        </div>
                        <span class="font-semibold text-gray-800">{{ __('messages.property_info') }}</span>
                    </div>
                    <div class="p-5">

                        {{-- Search row --}}
                        <div class="row g-3 align-items-end mb-3">
                            <div class="col-sm-8">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('messages.cadaster_number') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="cad_input" placeholder="11:11:10:01:03:0499"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                    value="{{ $property['cadasterNumber'] ?? '' }}">
                                <p class="mt-1 text-xs text-gray-400">{{ __('messages.cadaster_format') }}</p>
                            </div>
                            <div class="col-sm-4">
                                <button type="button" id="cad_btn"
                                    class="w-full d-flex align-items-center justify-content-center gap-2 py-2 text-white font-semibold rounded-xl text-sm border-0"
                                    style="background:#2563eb;"
                                    data-label="{{ __('messages.search') }}"
                                    data-loading="{{ __('messages.loading') ?? '...' }}">
                                    <i class="bi bi-search" id="cad_btn_icon"></i>
                                    <span id="cad_btn_text">{{ __('messages.search') }}</span>
                                </button>
                                <p class="text-white">.</p>
                            </div>
                        </div>

                        <div id="cad_error" class="mb-3 text-xs text-red-500" style="display:none;"></div>

                        {{-- Property result card --}}
                        <div id="prop_result" style="{{ !empty($property['cadasterNumber']) ? '' : 'display:none;' }}">
                            <div class="rounded-xl overflow-hidden" style="border:1.5px solid #e5e7eb;background:#fff;">
                                <div class="px-4 py-3 d-flex align-items-center gap-2" style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                                    <i class="bi bi-house-check-fill text-blue-600" style="font-size:16px;"></i>
                                    <span class="text-sm font-bold text-gray-800" id="disp_address">{{ $property['shortAddress'] ?? '' }}</span>
                                </div>
                                <div class="p-4">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <p class="text-xs text-gray-400 mb-1">{{ __('messages.cadaster_number') }}</p>
                                            <p class="text-sm font-semibold text-gray-800 mb-0" id="disp_cadaster">{{ $property['cadasterNumber'] ?? '' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-xs text-gray-400 mb-1">{{ __('messages.area') }}</p>
                                            <p class="text-sm font-semibold text-gray-800 mb-0"><span id="disp_area">{{ $property['objectArea'] ?? '' }}</span> m²</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-xs text-gray-400 mb-1">{{ __('messages.property_object_type') }}</p>
                                            <p class="text-sm font-semibold text-gray-800 mb-0" id="disp_type">{{ $property['tipText'] ?? '' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-xs text-gray-400 mb-1">{{ __('messages.property_object_view') }}</p>
                                            <p class="text-sm font-semibold text-gray-800 mb-0" id="disp_vid">{{ $property['vidText'] ?? '' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Hidden fields --}}
                            <input type="hidden" name="cadaster_number"  id="h_cadaster"    value="{{ $property['cadasterNumber'] ?? '' }}">
                            <input type="hidden" name="short_address"    id="h_short_addr"  value="{{ $property['shortAddress'] ?? '' }}">
                            <input type="hidden" name="object_area"      id="h_area"        value="{{ $property['objectArea'] ?? '' }}">
                            <input type="hidden" name="tip_text"         id="h_tip_text"    value="{{ $property['tipText'] ?? '' }}">
                            <input type="hidden" name="vid_text"         id="h_vid_text"    value="{{ $property['vidText'] ?? '' }}">
                            <input type="hidden" name="prop_region"      id="h_region"      value="{{ $property['region'] ?? '' }}">
                            <input type="hidden" name="prop_district_id" id="h_district_id" value="{{ $property['districtId'] ?? '' }}">
                            <input type="hidden" name="prop_district"    id="h_district"    value="{{ $property['district'] ?? '' }}">
                        </div>

                    </div>
                </div>

                {{-- ── Insurance Calculation ── --}}
                <div id="calc_section" style="{{ !empty($property['cadasterNumber']) ? '' : 'display:none;' }}">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                        <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                                <i class="bi bi-calculator text-blue-600"></i>
                            </div>
                            <p class="font-semibold text-gray-800 mb-0">{{ __('messages.insurance_calculation') }}</p>
                        </div>
                        <div class="p-5">

                            {{-- Insurance sum slider --}}
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="text-sm font-semibold text-gray-700 mb-0">{{ __('messages.insurance_sum') }}</label>
                                    <span class="text-base font-bold text-blue-600" id="amt_display">
                                        {{ number_format($calculation['insurance_amount'] ?? 50000000, 0, '.', ' ') }} UZS
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between text-xs text-gray-400 mb-2">
                                    <span>5 000 000</span><span>500 000 000</span>
                                </div>
                                <input type="range" id="amt_slider" min="5000000" max="500000000" step="5000000"
                                    value="{{ $calculation['insurance_amount'] ?? 50000000 }}" class="w-full">
                                <input type="hidden" name="insurance_amount" id="h_insurance_amount"
                                    value="{{ $calculation['insurance_amount'] ?? 50000000 }}">
                            </div>

                            {{-- Premium badge --}}
                            <div class="rounded-xl p-4 mb-4 d-flex align-items-center justify-content-between gap-3"
                                style="background:#f9fafb;border:1px solid #e5e7eb;">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold mb-1 text-uppercase" style="letter-spacing:.5px;">
                                        {{ __('messages.insurance_premium') }} (0.5%)
                                    </p>
                                    <p class="text-2xl font-bold text-gray-800 mb-0" id="prem_display">
                                        {{ number_format(($calculation['insurance_amount'] ?? 50000000) * 0.005, 0, '.', ' ') }} UZS
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-center rounded-2xl"
                                    style="width:48px;height:48px;min-width:48px;background:#e5e7eb;">
                                    <i class="bi bi-shield-check text-gray-600" style="font-size:20px;"></i>
                                </div>
                            </div>

                            {{-- Dates --}}
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
                    <a href="{{ route('gas.index', ['locale' => getCurrentLocale()]) }}"
                        class="d-flex align-items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-decoration-none"
                        style="border:1.5px solid #d1d5db;background:#fff;color:#374151;">
                        <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                    <button type="submit" id="submit_btn"
                        class="d-flex align-items-center gap-2 px-5 py-2 text-white font-semibold rounded-xl text-sm border-0"
                        style="background:{{ !empty($property['cadasterNumber']) ? '#2563eb' : '#9ca3af' }};"
                        {{ empty($property['cadasterNumber']) ? 'disabled' : '' }}>
                        {{ __('messages.next_step') }} <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

            </form>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <x-insurence.insurance-sidebar
                :title="__('insurance.gas.product_name')"
                :description="__('insurance.gas.subtitle')"
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

    // ── Amount slider ──────────────────────────────────────────────────────────
    var slider   = document.getElementById('amt_slider');
    var hAmt     = document.getElementById('h_insurance_amount');
    var amtEl    = document.getElementById('amt_display');
    var premEl   = document.getElementById('prem_display');
    var sideSum  = document.getElementById('sidebar_sum');
    var sidePrem = document.getElementById('sidebar_premium');

    function updateSlider() {
        var val  = parseInt(slider.value, 10);
        var prem = Math.round(val * 0.5 / 100);
        hAmt.value = val;
        amtEl.textContent  = fmt(val)  + ' UZS';
        premEl.textContent = fmt(prem) + ' UZS';
        if (sideSum)  sideSum.textContent  = fmt(val)  + ' UZS';
        if (sidePrem) sidePrem.textContent = fmt(prem) + ' UZS';
    }
    slider.addEventListener('input', updateSlider);
    updateSlider();

    // ── Start date → end date ──────────────────────────────────────────────────
    document.getElementById('start_date').addEventListener('change', function () {
        if (!this.value) return;
        var d = new Date(this.value);
        d.setFullYear(d.getFullYear() + 1);
        d.setDate(d.getDate() - 1);
        document.getElementById('end_date').value = d.toISOString().split('T')[0];
    });
    document.getElementById('start_date').dispatchEvent(new Event('change'));

    // ── Cadaster search ────────────────────────────────────────────────────────
    var cadBtn     = document.getElementById('cad_btn');
    var cadBtnText = document.getElementById('cad_btn_text');
    var cadBtnIcon = document.getElementById('cad_btn_icon');

    cadBtn.addEventListener('click', async function () {
        var cadNum  = document.getElementById('cad_input').value.trim();
        var errWrap = document.getElementById('cad_error');
        errWrap.style.display = 'none';

        if (!cadNum) {
            errWrap.textContent = '{{ __('messages.cadaster_number') }} {{ __('messages.required') }}';
            errWrap.style.display = '';
            return;
        }

        cadBtn.disabled = true;
        cadBtnIcon.className = 'spinner-border spinner-border-sm';
        cadBtnText.textContent = '...';

        try {
            var res  = await fetch('{{ route('fetch.cadaster.gas', ['locale' => getCurrentLocale()]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cadasterNumber: cadNum }),
            });
            var json = await res.json();

            if (!json.success) {
                errWrap.textContent = json.message || '{{ __('messages.cadaster_invalid') }}';
                errWrap.style.display = '';
                return;
            }

            var r = json.result;

            document.getElementById('disp_address').textContent  = r.shortAddress || r.address || '';
            document.getElementById('disp_cadaster').textContent = r.cadasterNumber || cadNum;
            document.getElementById('disp_area').textContent     = r.objectArea || '';
            document.getElementById('disp_type').textContent     = r.tipText || '';
            document.getElementById('disp_vid').textContent      = r.vidText || '';

            document.getElementById('h_cadaster').value     = r.cadasterNumber || cadNum;
            document.getElementById('h_short_addr').value   = r.shortAddress || '';
            document.getElementById('h_area').value         = r.objectArea || '';
            document.getElementById('h_tip_text').value     = r.tipText || '';
            document.getElementById('h_vid_text').value     = r.vidText || '';
            document.getElementById('h_region').value       = r.region || '';
            document.getElementById('h_district_id').value  = r.districtId || '';
            document.getElementById('h_district').value     = r.district || '';

            document.getElementById('prop_result').style.display  = '';
            document.getElementById('calc_section').style.display = '';

            var btn = document.getElementById('submit_btn');
            btn.disabled = false;
            btn.style.background = '#2563eb';

            updateSlider();

        } catch (e) {
            errWrap.textContent = '{{ __('messages.error_occurred') }}';
            errWrap.style.display = '';
        } finally {
            cadBtn.disabled = false;
            cadBtnIcon.className = 'bi bi-search';
            cadBtnText.textContent = '{{ __('messages.search') }}';
        }
    });
})();
</script>
@endpush
