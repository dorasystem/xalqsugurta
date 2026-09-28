@extends('layouts.app')
@section('title', __('insurance.osgor.page_title'))

{{--
    OSGOR step 2: salary fund + start date. The premium shown here is a preview from
    osgor.calculate; storeCalculation recalculates it on the server.
--}}
@php
    $locale    = getCurrentLocale();
    $fot       = old('fot', $calculation['fot'] ?? null);
    $startDate = old('start_date', $calculation['start_date'] ?? now()->addDay()->format('Y-m-d'));
@endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.osgor.page_title')"
    :subtitle="__('insurance.osgor.subtitle')"
    :steps="$flowSteps"
    :current="2"
    :stepUrls="$flowUrls"
>
    <form action="{{ route('osgor.storeCalculation', ['locale' => $locale]) }}" method="POST" class="xf-panel" id="osgor_form">
        @csrf

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.fot_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.flow.fot_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">
            <x-insurence.found
                :title="$applicant['name']"
                :text="__t('messages.inn') . ': ' . $applicant['inn'] . ($applicant['oked'] ? ' · ' . __t('messages.oked') . ': ' . $applicant['oked'] : '')"
            />

            <div class="xf-row">
                <x-insurence.field
                    name="fot"
                    id="fot"
                    type="number"
                    :label="__t('messages.fond_oplaty_truda')"
                    :value="$fot ? (int) $fot : null"
                    :help="__t('messages.flow.fot_help')"
                    min="1"
                    step="1"
                    inputmode="numeric"
                    placeholder="500000000"
                    required
                />
                <x-insurence.field
                    name="start_date"
                    id="start_date"
                    type="date"
                    :label="__('messages.start_date')"
                    :value="$startDate"
                    :min="now()->format('Y-m-d')"
                    :help="__t('messages.flow.term_auto', ['months' => 12])"
                    required
                />
            </div>

            <div class="xf-premium-line">
                <span>{{ __('messages.insurance_premium') }}</span>
                <b id="osgor_premium">{{ $premiumTotal ? formatMoney($premiumTotal) : '—' }}</b>
            </div>
            <p id="osgor_error" class="xf-field__error" role="alert" hidden></p>
        </div>

        <x-insurence.actions
            :backUrl="route('osgor.index', ['locale' => $locale])"
            :submit="__t('messages.next_step')"
            :total="$premiumTotal"
        />
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
    var fot      = document.getElementById('fot');
    var start    = document.getElementById('start_date');
    var out      = document.getElementById('osgor_premium');
    var err      = document.getElementById('osgor_error');
    var timer    = null;

    function money(n) { return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + CURRENCY; }

    function setPremium(text) {
        out.textContent = text;
        document.querySelectorAll('[data-summary="total"], #sidebar_premium').forEach(function (el) {
            el.textContent = text;
            el.classList.remove('is-empty');
        });
    }

    function recalc() {
        err.hidden = true;
        if (!fot.value || Number(fot.value) < 1 || !start.value) return;

        setPremium(@json(__t('messages.flow.calculating')));
        clearTimeout(timer);
        timer = setTimeout(async function () {
            try {
                var res  = await fetch(@json(route('osgor.calculate', ['locale' => $locale])), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ fot: fot.value, start_date: start.value }),
                });
                var json = await res.json();
                if (json.success) {
                    setPremium(money(json.data.insurance_premium));
                } else {
                    setPremium('—');
                    err.textContent = json.message || @json(__('messages.error_occurred'));
                    err.hidden = false;
                }
            } catch (e) {
                setPremium('—');
            }
        }, 500);
    }

    fot.addEventListener('input', recalc);
    start.addEventListener('change', recalc);
})();
</script>
@endpush
