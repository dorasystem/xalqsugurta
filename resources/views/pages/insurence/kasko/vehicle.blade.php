@extends('layouts.app')
@section('title', __('insurance.kasko.page_title'))

@php
    $hasVehicle = !empty($vehicle['regnumber']);
    $amount     = (int) old('insurance_amount', $calculation['insurance_amount'] ?? $flow['default']);
    $startDate  = old('payment_start_date', $calculation['payment_start_date'] ?? now()->format('Y-m-d'));
    $premiumOf  = fn (int $sum): int => (int) round($sum * $flow['rate'] / 100);

    // Validation errors on the hidden vehicle fields are shown under the search row
    $vehicleError = collect(['regnumber', 'tp_seria', 'tp_number', 'brand', 'model', 'year', 'body_number', 'engine_number', 'vehicle_type'])
        ->map(fn (string $field) => $errors->first($field))
        ->filter()
        ->first();
@endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.kasko.page_title')"
    :subtitle="__('insurance.kasko.subtitle')"
    :steps="$flowSteps"
    :current="2"
    :stepUrls="$flowUrls"
>
    <form action="{{ route('kasko.storeVehicle', ['locale' => getCurrentLocale()]) }}" method="POST" id="kasko_form" class="xf-panel">
        @csrf

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.vehicle_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.flow.vehicle_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">

            {{-- ── Vehicle search ── --}}
            <div class="xf-row" style="--xf-cols: 3">
                <x-insurence.field
                    name="gov_search"
                    id="gov_input"
                    :label="__('insurance.kasko.gov_number')"
                    :value="$vehicle['regnumber'] ?? null"
                    placeholder="01A123BC"
                    maxlength="10"
                    autocomplete="off"
                    style="text-transform: uppercase"
                />
                <x-insurence.field
                    name="tp_seria_search"
                    id="tp_seria_input"
                    :label="__('insurance.kasko.tp_seria')"
                    :value="$vehicle['tp_seria'] ?? null"
                    placeholder="AAF"
                    maxlength="3"
                    autocomplete="off"
                    style="text-transform: uppercase"
                />
                <x-insurence.field
                    name="tp_number_search"
                    id="tp_number_input"
                    :label="__('insurance.kasko.tp_number')"
                    :value="$vehicle['tp_number'] ?? null"
                    placeholder="1234567"
                    maxlength="7"
                    inputmode="numeric"
                    autocomplete="off"
                />
            </div>

            <div>
                <button type="button" id="vehicle_btn" class="xf-btn xf-btn--soft">
                    <i class="bi bi-search" id="vehicle_btn_icon"></i>
                    <span id="vehicle_btn_text">{{ __('messages.search') }}</span>
                </button>
            </div>

            <p id="vehicle_error" class="xf-field__error" role="alert" @unless ($vehicleError) hidden @endunless>{{ $vehicleError }}</p>

            <x-insurence.found
                id="vehicle_result"
                :title="$summaryItems['object'][1] ?? ''"
                :text="$hasVehicle ? __('insurance.kasko.year') . ': ' . $vehicle['year'] . ' · ' . __('insurance.kasko.body_number') . ': ' . $vehicle['body_number'] : ''"
                :hidden="!$hasVehicle"
            />

            <p id="vehicle_hint" class="xf-note" @if ($hasVehicle) hidden @endif>
                <i class="bi bi-info-circle"></i>
                <span>{{ __t('messages.flow.vehicle_hint') }}</span>
            </p>

            {{-- Values from the vehicle lookup --}}
            <input type="hidden" name="regnumber"     id="h_regnumber"     value="{{ $vehicle['regnumber']     ?? '' }}">
            <input type="hidden" name="tp_seria"      id="h_tp_seria"      value="{{ $vehicle['tp_seria']      ?? '' }}">
            <input type="hidden" name="tp_number"     id="h_tp_number"     value="{{ $vehicle['tp_number']     ?? '' }}">
            <input type="hidden" name="brand"         id="h_brand"         value="{{ $vehicle['brand']         ?? '' }}">
            <input type="hidden" name="model"         id="h_model"         value="{{ $vehicle['model']         ?? '' }}">
            <input type="hidden" name="year"          id="h_year"          value="{{ $vehicle['year']          ?? '' }}">
            <input type="hidden" name="body_number"   id="h_body_number"   value="{{ $vehicle['body_number']   ?? '' }}">
            <input type="hidden" name="engine_number" id="h_engine_number" value="{{ $vehicle['engine_number'] ?? '' }}">
            <input type="hidden" name="vehicle_type"  id="h_vehicle_type"  value="{{ $vehicle['vehicle_type']  ?? 2 }}">

            @include('pages.insurence.flow.partials.sum-dates', ['revealed' => $hasVehicle])

        </div>

        <x-insurence.actions
            :backUrl="route('kasko.index', ['locale' => getCurrentLocale()])"
            :submit="__t('messages.next_step')"
            :total="$hasVehicle ? $premiumOf($amount) : null"
            id="submit_btn"
            :disabled="!$hasVehicle"
        />
    </form>

    <x-slot:summary>
        <x-insurence.summary
            :premium="$hasVehicle ? $premiumOf($amount) : null"
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
    var CSRF = document.querySelector('meta[name="csrf-token"]').content;
    var calc = window.xfCalc;

    function $(id) { return document.getElementById(id); }

    // ── Vehicle search ─────────────────────────────────────────────────────────
    var btn = $('vehicle_btn');

    btn.addEventListener('click', async function () {
        var gov    = $('gov_input').value.trim().toUpperCase().replace(/\s+/g, '');
        var seria  = $('tp_seria_input').value.trim().toUpperCase();
        var number = $('tp_number_input').value.trim();
        var err    = $('vehicle_error');
        err.hidden = true;

        if (!gov || !seria || !number) {
            err.textContent = @json(__t('messages.flow.vehicle_fill'));
            err.hidden = false;
            return;
        }

        btn.disabled = true;
        $('vehicle_btn_icon').className = 'spinner-border spinner-border-sm';
        $('vehicle_btn_text').textContent = @json(__('messages.loading'));

        try {
            var res = await fetch(@json(route('kasko.findVehicle', ['locale' => getCurrentLocale()])), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ gov_number: gov, tp_seria: seria, tp_number: number }),
            });
            var json = await res.json();

            if (!json.success || !json.data) {
                err.textContent = json.message || @json(__('messages.vehicle_not_found'));
                err.hidden = false;
                return;
            }

            var v    = json.data;
            var name = ((v.brand || '') + ' ' + (v.model || '')).trim() || gov;

            var fields = {
                h_regnumber: v.regnumber, h_tp_seria: v.tp_seria, h_tp_number: v.tp_number,
                h_brand: v.brand || gov, h_model: v.model, h_year: v.year,
                h_body_number: v.body_number, h_engine_number: v.engine_number, h_vehicle_type: v.vehicle_type ?? 2,
            };
            Object.keys(fields).forEach(function (id) { $(id).value = fields[id] ?? ''; });

            var found = $('vehicle_result');
            found.querySelector('[data-found="title"]').textContent = name + ', ' + v.regnumber;
            found.querySelector('[data-found="text"]').textContent  =
                @json(__('insurance.kasko.year')) + ': ' + (v.year || '—') + ' · ' +
                @json(__('insurance.kasko.body_number')) + ': ' + (v.body_number || '—');
            found.hidden = false;
            $('vehicle_hint').hidden = true;

            calc.reveal(name + ', ' + v.regnumber);
        } catch (e) {
            err.textContent = @json(__('messages.error_occurred'));
            err.hidden = false;
        } finally {
            btn.disabled = false;
            $('vehicle_btn_icon').className = 'bi bi-search';
            $('vehicle_btn_text').textContent = @json(__('messages.search'));
        }
    });
})();
</script>
@endpush
