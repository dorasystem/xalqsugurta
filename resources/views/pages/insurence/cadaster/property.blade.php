@extends('layouts.app')
@section('title', __('insurance.' . $flow['key'] . '.page_title'))

@php
    $hasProperty = !empty($property['cadasterNumber']);
    $amount      = (int) old('insurance_amount', $calculation['insurance_amount'] ?? $flow['default']);
    $startDate   = old('payment_start_date', $calculation['payment_start_date'] ?? now()->format('Y-m-d'));
    $premiumOf   = fn (int $sum): int => (int) round($sum * $flow['rate'] / 100);
@endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.' . $flow['key'] . '.page_title')"
    :subtitle="__('insurance.' . $flow['key'] . '.subtitle')"
    :steps="$flowSteps"
    :current="2"
    :stepUrls="$flowUrls"
>
    <form action="{{ route($flow['key'] . '.storeProperty', ['locale' => getCurrentLocale()]) }}" method="POST" id="prop_form" class="xf-panel">
        @csrf

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.property_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.flow.property_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">

            {{-- ── Cadaster search ── --}}
            <x-insurence.field
                name="cadaster_search"
                id="cad_input"
                :label="__('messages.cadaster_number')"
                :value="$property['cadasterNumber'] ?? null"
                :help="__('messages.cadaster_format')"
                placeholder="11:11:10:01:03:0499"
                autocomplete="off"
            >
                <x-slot:append>
                    <button type="button" id="cad_btn" class="xf-btn xf-btn--soft">
                        <i class="bi bi-search" id="cad_btn_icon"></i>
                        <span id="cad_btn_text">{{ __('messages.search') }}</span>
                    </button>
                </x-slot:append>
            </x-insurence.field>

            <p id="cad_error" class="xf-field__error" role="alert" @error('cadaster_number') @else hidden @enderror>@error('cadaster_number'){{ $message }}@enderror</p>

            <x-insurence.found
                id="prop_result"
                :title="$summaryItems['property'][1] ?? ''"
                :text="$property['shortAddress'] ?? ''"
                :hidden="!$hasProperty"
            />

            <p id="cad_hint" class="xf-note" @if ($hasProperty) hidden @endif>
                <i class="bi bi-info-circle"></i>
                <span>{{ __t('messages.flow.cadaster_hint') }}</span>
            </p>

            {{-- Values from the cadaster lookup --}}
            <input type="hidden" name="cadaster_number"         id="h_cadaster"           value="{{ $property['cadasterNumber'] ?? '' }}">
            <input type="hidden" name="short_address"           id="h_short_addr"         value="{{ $property['shortAddress'] ?? '' }}">
            <input type="hidden" name="object_area"             id="h_area"               value="{{ $property['objectArea'] ?? '' }}">
            <input type="hidden" name="prop_cost"               id="h_cost"               value="{{ $property['cost'] ?? '' }}">
            <input type="hidden" name="tip_text"                id="h_tip_text"           value="{{ $property['tipText'] ?? '' }}">
            <input type="hidden" name="vid_text"                id="h_vid_text"           value="{{ $property['vidText'] ?? '' }}">
            <input type="hidden" name="tip"                     id="h_tip"                value="{{ $property['tip'] ?? '' }}">
            <input type="hidden" name="vid"                     id="h_vid"                value="{{ $property['vid'] ?? '' }}">
            <input type="hidden" name="prop_region"             id="h_region"             value="{{ $property['region'] ?? '' }}">
            <input type="hidden" name="prop_region_id"          id="h_region_id"          value="{{ $property['regionId'] ?? '' }}">
            <input type="hidden" name="prop_district_id"        id="h_district_id"        value="{{ $property['districtId'] ?? '' }}">
            <input type="hidden" name="prop_district"           id="h_district"           value="{{ $property['district'] ?? '' }}">
            <input type="hidden" name="prop_building_type"      id="h_building_type"      value="{{ $property['buildingType'] ?? 1 }}">
            <input type="hidden" name="prop_cadastr_issue_date" id="h_cadastr_issue_date" value="{{ $property['cadastrIssueDate'] ?? '' }}">
            <input type="hidden" name="prop_street"             id="h_street"             value="{{ $property['street'] ?? '' }}">
            <input type="hidden" name="prop_dom_num"            id="h_dom_num"            value="{{ $property['domNum'] ?? '' }}">
            <input type="hidden" name="prop_kvartira"           id="h_kvartira"           value="{{ $property['kvartiraNum'] ?? '' }}">
            <input type="hidden" name="prop_neighborhood"       id="h_neighborhood"       value="{{ $property['neighborhood'] ?? '' }}">

            {{-- ── Sum + dates (after the property is found) ── --}}
            <div id="calc_section" class="xf-panel__body" style="padding: 0" @unless ($hasProperty) hidden @endunless>

                <div class="xf-field">
                    <span class="xf-field__label" id="amt_label">{{ __('messages.insurance_sum') }}</span>
                    <div class="xf-chips" role="group" aria-labelledby="amt_label">
                        @foreach ($flow['presets'] as $preset)
                            <button type="button" class="xf-chip" data-amount="{{ $preset }}"
                                    aria-pressed="{{ $preset === $amount ? 'true' : 'false' }}">
                                {{ $preset / 1000000 }} {{ __t('messages.flow.mln') }}
                            </button>
                        @endforeach
                    </div>
                    <span class="xf-amount" id="amt_display">{{ formatMoney($amount) }}</span>
                    <input type="range" id="amt_slider" class="xf-range" min="{{ $flow['min'] }}" max="{{ $flow['max'] }}" step="5000000"
                           value="{{ $amount }}" aria-labelledby="amt_label">
                    <input type="hidden" name="insurance_amount" id="h_insurance_amount" value="{{ $amount }}">
                    @error('insurance_amount')
                        <p class="xf-field__error">{{ $message }}</p>
                    @else
                        <p class="xf-field__help">{{ __t('messages.flow.amount_range', ['min' => $flow['min'] / 1000000, 'max' => $flow['max'] / 1000000]) }}</p>
                    @enderror
                </div>

                <div class="xf-row">
                    <x-insurence.field
                        name="payment_start_date"
                        id="start_date"
                        type="date"
                        :label="__('messages.start_date')"
                        :value="$startDate"
                        :min="now()->format('Y-m-d')"
                        required
                    />
                    <x-insurence.field
                        name="payment_end_date_display"
                        id="end_date"
                        type="date"
                        :label="__('messages.end_date')"
                        :value="$calculation['payment_end_date'] ?? null"
                        :help="__t('messages.flow.term_auto')"
                        readonly
                        tabindex="-1"
                    />
                </div>
            </div>

        </div>

        <x-insurence.actions
            :backUrl="route($flow['key'] . '.index', ['locale' => getCurrentLocale()])"
            :submit="__t('messages.next_step')"
            :total="$hasProperty ? $premiumOf($amount) : null"
            id="submit_btn"
            :disabled="!$hasProperty"
        />
    </form>

    <x-slot:summary>
        <x-insurence.summary
            :premium="$hasProperty ? $premiumOf($amount) : null"
            :rate="$flow['rateLabel']"
            :items="$summaryItems"
        />
    </x-slot:summary>
</x-insurence.flow>
@endsection

@push('scripts')
<script>
(function () {
    var CSRF     = document.querySelector('meta[name="csrf-token"]').content;
    var RATE     = {{ $flow['rate'] / 100 }};
    var CURRENCY = @json(__t('messages.currency'));

    function $(id) { return document.getElementById(id); }
    function money(n) { return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + CURRENCY; }
    function dmy(d) { return ('0' + d.getDate()).slice(-2) + '.' + ('0' + (d.getMonth() + 1)).slice(-2) + '.' + d.getFullYear(); }
    function setSummary(key, text) {
        document.querySelectorAll('[data-summary="' + key + '"]').forEach(function (el) {
            el.textContent = text;
            el.classList.remove('is-pending');
        });
    }

    // ── Sum: chips + slider ────────────────────────────────────────────────────
    var slider = $('amt_slider');

    function setAmount(val) {
        val = parseInt(val, 10);
        slider.value = val;
        $('h_insurance_amount').value = val;
        $('amt_display').textContent = money(val);
        document.querySelectorAll('[data-amount]').forEach(function (chip) {
            chip.setAttribute('aria-pressed', parseInt(chip.dataset.amount, 10) === val ? 'true' : 'false');
        });

        if ($('calc_section').hidden) return;

        var premium = $('sidebar_premium');
        premium.textContent = money(val * RATE);
        premium.classList.remove('is-empty');
        setSummary('sum', money(val));
        setSummary('total', money(val * RATE));
    }

    slider.addEventListener('input', function () { setAmount(this.value); });
    document.querySelectorAll('[data-amount]').forEach(function (chip) {
        chip.addEventListener('click', function () { setAmount(this.dataset.amount); });
    });

    // ── Start date → end date ──────────────────────────────────────────────────
    function updateDates() {
        var start = $('start_date').value;
        if (!start) return;
        var s = new Date(start + 'T00:00:00');
        var e = new Date(s);
        e.setFullYear(e.getFullYear() + 1);
        e.setDate(e.getDate() - 1);
        $('end_date').value = e.getFullYear() + '-' + ('0' + (e.getMonth() + 1)).slice(-2) + '-' + ('0' + e.getDate()).slice(-2);
        if (!$('calc_section').hidden) setSummary('period', dmy(s) + ' – ' + dmy(e));
    }
    $('start_date').addEventListener('change', updateDates);

    // ── Cadaster search ────────────────────────────────────────────────────────
    var cadBtn = $('cad_btn');

    cadBtn.addEventListener('click', async function () {
        var cadNum = $('cad_input').value.trim();
        var err    = $('cad_error');
        err.hidden = true;

        if (!cadNum) {
            err.textContent = @json(__('messages.cadaster_number') . ' ' . __('messages.required'));
            err.hidden = false;
            return;
        }

        cadBtn.disabled = true;
        $('cad_btn_icon').className = 'spinner-border spinner-border-sm';
        $('cad_btn_text').textContent = @json(__('messages.loading'));

        try {
            var res = await fetch(@json(route($flow['cadasterRoute'], ['locale' => getCurrentLocale()])), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ cadasterNumber: cadNum }),
            });
            var json = await res.json();

            if (!json.success) {
                err.textContent = json.message || @json(__('messages.cadaster_invalid'));
                err.hidden = false;
                return;
            }

            var r     = json.result;
            var title = (r.vidText || r.tipText || r.cadasterNumber || cadNum) + (r.objectArea ? ', ' + r.objectArea + ' m²' : '');

            var fields = {
                h_cadaster: r.cadasterNumber || cadNum, h_short_addr: r.shortAddress, h_area: r.objectArea,
                h_cost: r.cost, h_tip_text: r.tipText, h_vid_text: r.vidText, h_tip: r.tip, h_vid: r.vid,
                h_region: r.region, h_region_id: r.regionId, h_district_id: r.districtId, h_district: r.district,
                h_building_type: r.buildingType ?? 1, h_cadastr_issue_date: r.cadastrIssueDate, h_street: r.street,
                h_dom_num: r.domNum, h_kvartira: r.kvartiraNum, h_neighborhood: r.neighborhood,
            };
            Object.keys(fields).forEach(function (id) { $(id).value = fields[id] ?? ''; });
            $('cad_input').value = fields.h_cadaster;

            var found = $('prop_result');
            found.querySelector('[data-found="title"]').textContent = title;
            found.querySelector('[data-found="text"]').textContent  = r.shortAddress || r.address || '';
            found.hidden = false;
            $('cad_hint').hidden = true;
            $('calc_section').hidden = false;
            $('submit_btn').disabled = false;

            setSummary('property', title);
            setAmount(slider.value);
            updateDates();
        } catch (e) {
            err.textContent = @json(__('messages.error_occurred'));
            err.hidden = false;
        } finally {
            cadBtn.disabled = false;
            $('cad_btn_icon').className = 'bi bi-search';
            $('cad_btn_text').textContent = @json(__('messages.search'));
        }
    });

    setAmount(slider.value);
    updateDates();
})();
</script>
@endpush
