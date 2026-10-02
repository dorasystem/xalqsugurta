@extends('layouts.app')
@section('title', __('insurance.' . $flow['key'] . '.page_title'))

@php
    $hasProperty = !empty($property['cadasterNumber']);
    $amount      = (int) old('insurance_amount', $calculation['insurance_amount'] ?? $flow['default']);
    $startDate   = old('payment_start_date', $calculation['payment_start_date'] ?? $flow['start_min']);
    $premiumOf   = fn (int $sum): int => \App\Services\ProductSettings::premium($flow, $sum);
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
                placeholder="XX:XX:XX:XX:XX:XXXX:XXXX:XXX"
                inputmode="decimal"
                maxlength="40"
                autocomplete="off"
                spellcheck="false"
            >
                <x-slot:append>
                    <button type="button" id="cad_btn" class="xf-btn xf-btn--soft">
                        <i class="bi bi-search" id="cad_btn_icon"></i>
                        <span id="cad_btn_text">{{ __('messages.search') }}</span>
                    </button>
                </x-slot:append>
            </x-insurence.field>

            <p id="cad_error" class="xf-field__error" role="alert" @error('cadaster_number') @else hidden @enderror>@error('cadaster_number'){{ $message }}@enderror</p>

            <details class="xf-hint">
                <summary><i class="bi bi-question-circle"></i> {{ __t('messages.flow.cadaster_where') }}</summary>
                <p>{{ __t('messages.flow.cadaster_where_text') }}</p>
            </details>

            <x-insurence.found
                id="prop_result"
                :title="$summaryItems['object'][1] ?? ''"
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

            @include('pages.insurence.flow.partials.sum-dates', ['revealed' => $hasProperty])

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
@include('pages.insurence.flow.partials.calc-script')
<script>
(function () {
    var CSRF     = document.querySelector('meta[name="csrf-token"]').content;

    function $(id) { return document.getElementById(id); }
    var calc = window.xfCalc;

    // ── Cadaster mask ──────────────────────────────────────────────────────────
    // XX:XX:XX:XX:XX:XXXX is fixed (the colons are added while typing); after it the number
    // goes on as :0001:011 or /0001/011. On phones "." "," "-" type "/" (the numeric pad has no "/").
    var HEAD = [2, 2, 2, 2, 2, 4];

    function formatCadaster(raw) {
        var head = '', digits = 0, need = HEAD.reduce(function (a, b) { return a + b; }, 0), i = 0;

        for (; i < raw.length && digits < need; i++) {
            if (!/\d/.test(raw[i])) continue;
            var group = 0, sum = 0;
            while (sum + HEAD[group] <= digits) { sum += HEAD[group]; group++; }
            if (digits > 0 && digits === sum) head += ':';
            head += raw[i];
            digits++;
        }

        if (digits < need) return head;

        var tail = raw.slice(i)
            .replace(/[.,\-]/g, '/')
            .replace(/[^0-9:\/]/g, '')
            .replace(/([:\/])[:\/]+/g, '$1');
        if (/^\d/.test(tail)) tail = ':' + tail;
        // 0001 is the 4-digit block, then a new block with the same separator: :0001:011, /0001/011
        tail = tail.replace(/^([:\/])(\d{4})(\d)/, '$1$2$1$3');

        return head + tail;
    }

    var cadInput = $('cad_input');
    cadInput.addEventListener('input', function () {
        var atEnd = this.selectionStart === this.value.length;
        var value = formatCadaster(this.value);
        if (value !== this.value) {
            this.value = value;
            if (atEnd) this.setSelectionRange(value.length, value.length);
        }
    });
    cadInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); cadBtn.click(); }
    });

    // ── Cadaster search ────────────────────────────────────────────────────────
    var cadBtn = $('cad_btn');

    cadBtn.addEventListener('click', async function () {
        var cadNum = formatCadaster($('cad_input').value.trim()).replace(/[:\/]$/, '');
        var err    = $('cad_error');
        err.hidden = true;

        if (!cadNum) {
            err.textContent = @json(__('messages.cadaster_number') . ' ' . __('messages.required'));
            err.hidden = false;
            return;
        }

        // Same format the server checks (CadasterFlow::cadasterRule)
        if (!/^\d{2}(:\d{2}){4}:\d{4}([:\/]\d{1,4})*$/.test(cadNum)) {
            err.textContent = @json(__t('messages.flow.cadaster_format_error'));
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

            calc.reveal(title);
        } catch (e) {
            err.textContent = @json(__('messages.error_occurred'));
            err.hidden = false;
        } finally {
            cadBtn.disabled = false;
            $('cad_btn_icon').className = 'bi bi-search';
            $('cad_btn_text').textContent = @json(__('messages.search'));
        }
    });
})();
</script>
@endpush
