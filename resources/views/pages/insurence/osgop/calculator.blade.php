@extends('layouts.app')
@section('title', __('insurance.osgop.page_title'))

{{--
    OSGOP step 3: term + start date. Type and seats come from the registry (session);
    the preview uses osgop.calculate and storeCalculation recalculates on the server.
--}}
@php
    $locale    = getCurrentLocale();
    $termId    = old('insurance_term_id', $calculation['insurance_term_id'] ?? null);
    $startDate = old('start_date', $calculation['start_date'] ?? now()->format('Y-m-d'));
@endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.osgop.page_title')"
    :subtitle="__('insurance.osgop.subtitle')"
    :steps="$flowSteps"
    :current="3"
    :stepUrls="$flowUrls"
>
    <form action="{{ route('osgop.storeCalculation', ['locale' => $locale]) }}" method="POST" class="xf-panel">
        @csrf

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.term_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.flow.osgop_term_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">
            <x-insurence.found
                :title="trim(($vehicle['model_custom_name'] ?? '') . ', ' . ($vehicle['gov_number'] ?? ''), ', ')"
                :text="collect([
                    $vehicleType?->name,
                    !empty($vehicle['number_of_seats']) ? $vehicle['number_of_seats'] . ' ' . __('messages.seats') : null,
                ])->filter()->implode(' · ')"
            />

            <div class="xf-field">
                <span class="xf-field__label" id="term_label">{{ __('messages.insurance_period') }}</span>
                <div class="xf-chips" role="radiogroup" aria-labelledby="term_label">
                    @foreach ($terms as $term)
                        <label class="xf-chip">
                            <input type="radio" name="insurance_term_id" value="{{ $term->provider_term_id }}" data-months="{{ $term->months }}"
                                   @checked((string) $termId === (string) $term->provider_term_id) class="visually-hidden">
                            {{ $term->name }}
                        </label>
                    @endforeach
                </div>
                @error('insurance_term_id')
                    <p class="xf-field__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="xf-row">
                <x-insurence.field name="start_date" id="start_date" type="date" :label="__('messages.start_date')" :value="$startDate"
                    :min="now()->format('Y-m-d')" required />
                <x-insurence.field name="end_date_display" id="end_date" type="date" :label="__('messages.end_date')"
                    :value="$calculation['end_date'] ?? null" readonly tabindex="-1" />
            </div>

            <div class="xf-premium-line">
                <span>{{ __('messages.insurance_premium') }}</span>
                <b id="osgop_premium">{{ $premiumTotal ? formatMoney($premiumTotal) : '—' }}</b>
            </div>
            <p id="osgop_error" class="xf-field__error" role="alert" hidden></p>
        </div>

        <x-insurence.actions :backUrl="route('osgop.getVehicle', ['locale' => $locale])" :submit="__t('messages.next_step')" :total="$premiumTotal" />
    </form>

    <x-slot:summary>
        <x-insurence.summary :premium="$premiumTotal" :items="$summaryItems" />
    </x-slot:summary>
</x-insurence.flow>
@endsection

@push('scripts')
<script>
(function () {
    var CSRF     = document.querySelector('meta[name="csrf-token"]').content;
    var CURRENCY = @json(__t('messages.currency'));
    var start    = document.getElementById('start_date');
    var end      = document.getElementById('end_date');
    var out      = document.getElementById('osgop_premium');
    var err      = document.getElementById('osgop_error');

    function money(n) { return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + CURRENCY; }
    function pad(n) { return ('0' + n).slice(-2); }
    function term() { return document.querySelector('input[name="insurance_term_id"]:checked'); }

    function setPremium(text) {
        out.textContent = text;
        document.querySelectorAll('[data-summary="total"], #sidebar_premium').forEach(function (el) {
            el.textContent = text;
            el.classList.remove('is-empty');
        });
    }

    function sync() {
        document.querySelectorAll('input[name="insurance_term_id"]').forEach(function (r) {
            r.closest('.xf-chip').setAttribute('aria-pressed', r.checked ? 'true' : 'false');
        });

        var t = term();
        if (!t || !start.value) return;

        var d = new Date(start.value + 'T00:00:00');
        d.setMonth(d.getMonth() + parseInt(t.dataset.months, 10));
        d.setDate(d.getDate() - 1);
        end.value = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());

        err.hidden = true;
        setPremium(@json(__t('messages.flow.calculating')));
        fetch(@json(route('osgop.calculate', ['locale' => $locale])), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ insurance_term_id: t.value, start_date: start.value }),
        })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                if (json.success) {
                    setPremium(money(json.data.insurance_premium));
                } else {
                    setPremium('—');
                    err.textContent = json.message || @json(__('messages.error_occurred'));
                    err.hidden = false;
                }
            })
            .catch(function () { setPremium('—'); });
    }

    document.querySelectorAll('input[name="insurance_term_id"]').forEach(function (r) { r.addEventListener('change', sync); });
    start.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
