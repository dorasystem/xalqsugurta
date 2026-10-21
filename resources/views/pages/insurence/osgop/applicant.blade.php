@extends('layouts.app')
@section('title', __('insurance.osgop.page_title'))

{{-- OSGOP step 1: a person (passport + PINFL) or an organization (INN) --}}
@php
    $locale = getCurrentLocale();
    $type   = old('applicant_type', ($applicant['type'] ?? null) === 'organization' ? 'organization' : 'person');
    $person = $applicant['person'] ?? [];
    $org    = $applicant['organization'] ?? [];
@endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.osgop.page_title')"
    :subtitle="__('insurance.osgop.subtitle')"
    :steps="$flowSteps"
    :current="1"
    :stepUrls="$flowUrls"
>
    <div class="xf-panel">
        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.applicant_title') }}</h2>
            <div class="xf-chips" role="tablist" aria-label="{{ __t('messages.flow.applicant_type') }}">
                <button type="button" class="xf-chip" role="tab" data-type="person" aria-pressed="{{ $type === 'person' ? 'true' : 'false' }}">
                    <i class="bi bi-person"></i> {{ __t('messages.flow.person_tab') }}
                </button>
                <button type="button" class="xf-chip" role="tab" data-type="organization" aria-pressed="{{ $type === 'organization' ? 'true' : 'false' }}">
                    <i class="bi bi-building"></i> {{ __t('messages.flow.company_tab') }}
                </button>
            </div>
        </div>

        {{-- ── Person ── --}}
        <form action="{{ route('osgop.storeIndividualApplicant', ['locale' => $locale]) }}" method="POST"
              data-applicant="person" @if ($type !== 'person') hidden @endif>
            @csrf
            <input type="hidden" name="applicant_type" value="person">

            <div class="xf-panel__body">
                <div class="xf-row" style="--xf-cols: 2">
                    <x-insurence.field name="passport_seria" :label="__('insurance.passport.series')" :value="$person['passport_seria'] ?? null"
                        :help="__t('messages.flow.passport_series_help')" maxlength="4" placeholder="AA" autocomplete="off" style="text-transform: uppercase" />
                    <x-insurence.field name="passport_number" :label="__('insurance.passport.number')" :value="$person['passport_number'] ?? null"
                        inputmode="numeric" maxlength="7" placeholder="1234567" autocomplete="off" />
                </div>
                <div class="xf-row">
                    <x-insurence.field name="pinfl" :label="__t('messages.flow.pinfl')" :value="$person['pinfl'] ?? null"
                        :help="__t('messages.flow.pinfl_help')" inputmode="numeric" maxlength="14" placeholder="31234567890123" autocomplete="off" />
                    <x-insurence.field name="phone" id="f_phone_person" type="tel" :label="__('messages.phone_number')" :value="$person['phone'] ?? null"
                        :help="__t('messages.flow.phone_help')" inputmode="tel" placeholder="+998 90 123 45 67" autocomplete="tel" />
                </div>

                @if (!empty($person['lastname']))
                    <x-insurence.found :title="trim($person['lastname'] . ' ' . $person['firstname'] . ' ' . ($person['middlename'] ?? ''))" :text="$person['address'] ?? ''" />
                @endif
            </div>

            <x-insurence.actions :submit="__t('messages.next_step')" />
        </form>

        {{-- ── Organization ── --}}
        <form action="{{ route('osgop.storeCompanyApplicant', ['locale' => $locale]) }}" method="POST"
              data-applicant="organization" @if ($type !== 'organization') hidden @endif>
            @csrf
            <input type="hidden" name="applicant_type" value="organization">

            <div class="xf-panel__body">
                <div class="xf-row">
                    <x-insurence.field name="inn" :label="__t('messages.inn')" :value="$org['inn'] ?? null"
                        :help="__t('messages.flow.inn_help')" inputmode="numeric" maxlength="9" placeholder="123456789" autocomplete="off" />
                    <x-insurence.field name="phone" id="f_phone_org" type="tel" :label="__('messages.phone_number')" :value="$org['phone'] ?? null"
                        :help="__t('messages.flow.phone_help')" inputmode="tel" placeholder="+998 90 123 45 67" autocomplete="tel" />
                </div>

                @if (!empty($org['name']))
                    <x-insurence.found :title="$org['name']" :text="trim(($org['representativeName'] ?? '') . ' · ' . ($org['address'] ?? ''), ' ·')" />
                @else
                    <p class="xf-note">
                        <i class="bi bi-info-circle"></i>
                        <span>{{ __t('messages.flow.org_subtitle') }}</span>
                    </p>
                @endif
            </div>

            <x-insurence.actions :submit="__t('messages.next_step')" />
        </form>
    </div>

    <x-slot:summary>
        <x-insurence.summary :premium="null" :items="$summaryItems" />
    </x-slot:summary>
</x-insurence.flow>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-type]').forEach(function (tab) {
    tab.addEventListener('click', function () {
        var type = this.dataset.type;
        document.querySelectorAll('[data-type]').forEach(function (t) {
            t.setAttribute('aria-pressed', t.dataset.type === type ? 'true' : 'false');
        });
        document.querySelectorAll('[data-applicant]').forEach(function (form) {
            form.hidden = form.dataset.applicant !== type;
        });
    });
});
</script>
@endpush
