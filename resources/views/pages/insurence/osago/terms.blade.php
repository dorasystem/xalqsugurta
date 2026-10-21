@extends('layouts.app')
@section('title', __('insurance.osago.page_title'))

{{--
    OSAGO step 3: start date + who may drive. $premiums (unlimited / limited) are computed
    on the server from the registry vehicle; storeTerms() recalculates the chosen one.
    Drivers are added / removed by their own forms (addDriver / removeDriver).
--}}
@php
    $locale    = getCurrentLocale();
    $limit     = old('driver_limit', $terms['driver_limit'] ?? ($drivers ? 'limited' : 'unlimited'));
    $startDate = old('start_date', $terms['start_date'] ?? now()->format('Y-m-d'));
    $current   = $premiums[$limit];
@endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.osago.page_title')"
    :subtitle="__('insurance.osago.subtitle')"
    :steps="$flowSteps"
    :current="3"
    :stepUrls="$flowUrls"
>
    <div class="xf-panel">
        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.term_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.flow.osago_term_subtitle') }}</p>
        </div>

        <form action="{{ route('osago.storeTerms', ['locale' => $locale]) }}" method="POST" id="terms_form" class="xf-panel__body">
            @csrf

            <x-insurence.found
                :title="trim($vehicle['model'] . ', ' . $vehicle['gov_number'], ', ')"
                :text="collect([$vehicleType, formatMoney($sumInsured)])->filter()->implode(' · ')"
            />

            <div class="xf-row">
                <x-insurence.field name="start_date" type="date" :label="__('messages.start_date')" :value="$startDate"
                    :min="now()->format('Y-m-d')" required />
                <x-insurence.field name="end_date_display" id="end_date" type="date" :label="__('messages.end_date')"
                    :value="$terms['end_date'] ?? null" readonly tabindex="-1" />
            </div>

            <div class="xf-field">
                <span class="xf-field__label" id="limit_label">{{ __t('messages.flow.drivers') }}</span>
                <div class="xf-chips" role="radiogroup" aria-labelledby="limit_label">
                    @foreach (['unlimited', 'limited'] as $option)
                        <label class="xf-chip" aria-pressed="{{ $limit === $option ? 'true' : 'false' }}">
                            <input type="radio" name="driver_limit" value="{{ $option }}" data-premium="{{ $premiums[$option] }}"
                                   @checked($limit === $option) class="visually-hidden">
                            {{ __t('messages.flow.drivers_' . $option) }} · {{ formatMoney($premiums[$option]) }}
                        </label>
                    @endforeach
                </div>
                <p class="xf-field__help" id="limit_help">{{ __t('messages.flow.drivers_' . $limit . '_help', ['max' => $maxDrivers]) }}</p>
            </div>

            <div class="xf-premium-line">
                <span>{{ __('messages.insurance_premium') }}</span>
                <b id="osago_premium">{{ formatMoney($current) }}</b>
            </div>
        </form>

        {{-- ── Drivers (limited only) ── --}}
        <div class="xf-panel__body" id="drivers_block" @if ($limit !== 'limited') hidden @endif>
            @error('drivers')
                <p class="xf-field__error" role="alert">{{ $message }}</p>
            @enderror

            @if ($drivers)
                <ul class="xf-people">
                    @foreach ($drivers as $i => $driver)
                        <li class="xf-person">
                            <span class="xf-person__avatar" aria-hidden="true"><i class="bi bi-person-vcard"></i></span>
                            <div class="xf-person__main">
                                <span class="xf-person__name">{{ $driver['name'] }}</span>
                                <span class="xf-person__meta">{{ __t('messages.flow.driver_license') }}: {{ $driver['license'] }}</span>
                            </div>
                            <form method="POST" action="{{ route('osago.removeDriver', ['locale' => $locale, 'index' => $i]) }}">
                                @csrf
                                <button type="submit" class="xf-person__remove" title="{{ __t('messages.flow.remove') }}" aria-label="{{ __t('messages.flow.remove') }}">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="xf-note">
                    <i class="bi bi-info-circle"></i>
                    <span>{{ __t('messages.flow.drivers_empty') }}</span>
                </p>
            @endif

            @if (count($drivers) < $maxDrivers)
                <form method="POST" action="{{ route('osago.addDriver', ['locale' => $locale]) }}" class="xf-subpanel" aria-labelledby="add_driver_title">
                    @csrf
                    <div class="xf-subpanel__head">
                        <h3 class="xf-subpanel__title" id="add_driver_title">
                            <i class="bi bi-person-plus"></i> {{ __t('messages.flow.add_driver') }}
                        </h3>
                    </div>
                    <div class="xf-row" style="--xf-cols: 3">
                        <x-insurence.field name="driver_seria" :label="__('insurance.passport.series')"
                            maxlength="4" placeholder="AA" autocomplete="off" style="text-transform: uppercase" />
                        <x-insurence.field name="driver_number" :label="__('insurance.passport.number')"
                            inputmode="numeric" maxlength="7" placeholder="1234567" autocomplete="off" />
                        <x-insurence.field name="driver_pinfl" :label="__t('messages.flow.pinfl')"
                            inputmode="numeric" maxlength="14" placeholder="31234567890123" autocomplete="off" />
                    </div>
                    <div>
                        <button type="submit" class="xf-btn xf-btn--soft">
                            <i class="bi bi-search"></i> {{ __t('messages.flow.add_driver') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>

        <x-insurence.actions form="terms_form" :backUrl="route('osago.getOwner', ['locale' => $locale])" :submit="__t('messages.next_step')" :total="$current" />
    </div>

    <x-slot:summary>
        <x-insurence.summary :premium="$current" :items="$summaryItems" />
    </x-slot:summary>
</x-insurence.flow>
@endsection

@push('scripts')
<script>
(function () {
    var CURRENCY = @json(__t('messages.currency'));
    var HELP     = {
        unlimited: @json(__t('messages.flow.drivers_unlimited_help', ['max' => $maxDrivers])),
        limited:   @json(__t('messages.flow.drivers_limited_help', ['max' => $maxDrivers])),
    };
    var start = document.getElementById('f_start_date');
    var end   = document.getElementById('end_date');

    function money(n) { return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + CURRENCY; }
    function pad(n) { return ('0' + n).slice(-2); }

    function syncEnd() {
        if (!start.value) return;
        var d = new Date(start.value + 'T00:00:00');
        d.setFullYear(d.getFullYear() + 1);
        d.setDate(d.getDate() - 1);
        end.value = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }

    function syncLimit() {
        var checked = document.querySelector('input[name="driver_limit"]:checked');
        document.querySelectorAll('input[name="driver_limit"]').forEach(function (r) {
            r.closest('.xf-chip').setAttribute('aria-pressed', r.checked ? 'true' : 'false');
        });
        document.getElementById('drivers_block').hidden = checked.value !== 'limited';
        document.getElementById('limit_help').textContent = HELP[checked.value];

        var text = money(checked.dataset.premium);
        document.querySelectorAll('#osago_premium, [data-summary="total"], #sidebar_premium').forEach(function (el) {
            el.textContent = text;
            el.classList.remove('is-empty');
        });
    }

    document.querySelectorAll('input[name="driver_limit"]').forEach(function (r) { r.addEventListener('change', syncLimit); });
    start.addEventListener('change', syncEnd);
    syncEnd();
})();
</script>
@endpush
