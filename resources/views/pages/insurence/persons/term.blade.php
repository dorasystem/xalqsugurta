@extends('layouts.app')
@section('title', __('insurance.' . $flow['key'] . '.page_title'))

{{-- Step 3 of the persons flow: policy start date (term length comes from the product settings) --}}
@php
    $locale    = getCurrentLocale();
    $startDate = old('start_date', $calculation['start_date'] ?? $flow['start_min']);
@endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.' . $flow['key'] . '.page_title')"
    :subtitle="__('insurance.' . $flow['key'] . '.subtitle')"
    :steps="$flowSteps"
    :current="3"
    :stepUrls="$flowUrls"
>
    <form action="{{ route($flow['key'] . '.storeCalculation', ['locale' => $locale]) }}" method="POST" class="xf-panel">
        @csrf

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.term_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.flow.term_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">
            <div class="xf-row">
                <x-insurence.field
                    name="start_date"
                    id="start_date"
                    type="date"
                    :label="__('messages.start_date')"
                    :value="$startDate"
                    :min="$flow['start_min']"
                    :max="$flow['start_max']"
                    required
                />
                <x-insurence.field
                    name="end_date_display"
                    id="end_date"
                    type="date"
                    :label="__('messages.end_date')"
                    :value="$calculation['end_date'] ?? null"
                    :help="__t('messages.flow.term_auto', ['months' => $flow['term_months']])"
                    readonly
                    tabindex="-1"
                />
            </div>

            <div class="xf-premium-line">
                <span>{{ __t('messages.flow.persons') }}: {{ __t('messages.flow.persons_count', ['count' => count($persons)]) }} · {{ __t('messages.flow.total_sum') }}: {{ formatMoney($totalSum) }}</span>
                <b>{{ formatMoney($premiumTotal) }}</b>
            </div>
        </div>

        <x-insurence.actions
            :backUrl="route($flow['key'] . '.getPersons', ['locale' => $locale])"
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
    var start = document.getElementById('start_date');
    var end   = document.getElementById('end_date');
    var TERM_MONTHS = {{ (int) $flow['term_months'] }};

    function pad(n) { return ('0' + n).slice(-2); }

    function update() {
        if (!start.value) return;
        var d = new Date(start.value + 'T00:00:00');
        d.setMonth(d.getMonth() + TERM_MONTHS);
        d.setDate(d.getDate() - 1);
        end.value = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());

        var s = new Date(start.value + 'T00:00:00');
        document.querySelectorAll('[data-summary="period"]').forEach(function (el) {
            el.textContent = pad(s.getDate()) + '.' + pad(s.getMonth() + 1) + '.' + s.getFullYear()
                + ' – ' + pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear();
            el.classList.remove('is-pending');
        });
    }

    start.addEventListener('change', update);
    update();
})();
</script>
@endpush
