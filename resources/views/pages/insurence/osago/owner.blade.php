@extends('layouts.app')
@section('title', __('insurance.osago.page_title'))

{{--
    OSAGO step 2: the vehicle owner and the applicant (passport + PINFL each).
    The applicant is the owner unless the box is unticked. With $legalEntities on, the owner
    can also be an organization (INN); it is then the applicant too.
--}}
@php
    $locale    = getCurrentLocale();
    $orgSaved  = ($owner['type'] ?? null) === 'organization';
    $ownerType = $legalEntities
        ? old('owner_type', $orgSaved || (!$owner && !empty($vehicle['owner_inn']) && empty($vehicle['owner_pinfl'])) ? 'organization' : 'person')
        : 'person';
    $person    = $orgSaved ? [] : $owner;
    $isOwner   = (bool) old('applicant_is_owner', $applicant && !$orgSaved ? ($applicant['is_owner'] ?? true) : true);
    $other     = $applicant && !$orgSaved && !($applicant['is_owner'] ?? true) ? $applicant : [];
@endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.osago.page_title')"
    :subtitle="__('insurance.osago.subtitle')"
    :steps="$flowSteps"
    :current="2"
    :stepUrls="$flowUrls"
>
    <form action="{{ route('osago.storeOwner', ['locale' => $locale]) }}" method="POST" class="xf-panel">
        @csrf

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.owner_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.flow.owner_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">
            @if (!empty($vehicle['owner_name']))
                <p class="xf-note">
                    <i class="bi bi-car-front"></i>
                    <span>{{ __t('messages.flow.owner_by_registry', ['name' => $vehicle['owner_name']]) }}</span>
                </p>
            @endif

            @if ($legalEntities)
                <div class="xf-field">
                    <span class="xf-field__label" id="owner_type_label">{{ __t('messages.flow.owner_type') }}</span>
                    <div class="xf-chips" role="radiogroup" aria-labelledby="owner_type_label">
                        @foreach (['person' => __('messages.person'), 'organization' => __('messages.organization')] as $option => $label)
                            <label class="xf-chip" aria-pressed="{{ $ownerType === $option ? 'true' : 'false' }}">
                                <input type="radio" name="owner_type" value="{{ $option }}" @checked($ownerType === $option) class="visually-hidden">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div id="org_owner" @if ($ownerType !== 'organization') hidden @endif>
                    <x-insurence.field name="owner_inn" :label="__('messages.inn')" :value="$orgSaved ? $owner['inn'] : ($vehicle['owner_inn'] ?? null)"
                        :help="__t('messages.flow.owner_org_note')" inputmode="numeric" maxlength="9" placeholder="123456789" autocomplete="off" />
                    @if ($orgSaved)
                        <x-insurence.found :title="$owner['name']" :text="$owner['address']" />
                    @endif
                </div>
            @endif

            <div id="person_owner" @if ($ownerType === 'organization') hidden @endif>
            <div class="xf-row" style="--xf-cols: 3">
                <x-insurence.field name="owner_seria" :label="__('insurance.passport.series')" :value="$person['passport_seria'] ?? null"
                    maxlength="4" placeholder="AA" autocomplete="off" style="text-transform: uppercase" />
                <x-insurence.field name="owner_number" :label="__('insurance.passport.number')" :value="$person['passport_number'] ?? null"
                    inputmode="numeric" maxlength="7" placeholder="1234567" autocomplete="off" />
                <x-insurence.field name="owner_pinfl" :label="__t('messages.flow.pinfl')" :value="$person['pinfl'] ?? ($vehicle['owner_pinfl'] ?: null)"
                    :help="__t('messages.flow.pinfl_help')" inputmode="numeric" maxlength="14" placeholder="31234567890123" autocomplete="off" />
            </div>

            @if (!empty($person['lastname']))
                <x-insurence.found :title="trim($person['lastname'] . ' ' . $person['firstname'] . ' ' . $person['middlename'])" :text="$person['address']" />
            @endif

            <label class="xf-agree" for="applicant_is_owner">
                <input type="hidden" name="applicant_is_owner" value="0">
                <input type="checkbox" id="applicant_is_owner" name="applicant_is_owner" value="1" @checked($isOwner)>
                <span>{{ __t('messages.flow.applicant_is_owner') }}</span>
            </label>

            <section class="xf-subpanel" id="applicant_block" @if ($isOwner) hidden @endif aria-labelledby="applicant_title">
                <div class="xf-subpanel__head">
                    <h3 class="xf-subpanel__title" id="applicant_title">
                        <i class="bi bi-person"></i> {{ __t('messages.flow.applicant') }}
                    </h3>
                </div>
                <div class="xf-row" style="--xf-cols: 3">
                    <x-insurence.field name="applicant_seria" :label="__('insurance.passport.series')" :value="$other['passport_seria'] ?? null"
                        maxlength="4" placeholder="AA" autocomplete="off" style="text-transform: uppercase" />
                    <x-insurence.field name="applicant_number" :label="__('insurance.passport.number')" :value="$other['passport_number'] ?? null"
                        inputmode="numeric" maxlength="7" placeholder="1234567" autocomplete="off" />
                    <x-insurence.field name="applicant_pinfl" :label="__t('messages.flow.pinfl')" :value="$other['pinfl'] ?? null"
                        inputmode="numeric" maxlength="14" placeholder="31234567890123" autocomplete="off" />
                </div>
            </section>
            </div>

            <div class="xf-row">
                <x-insurence.field name="phone" type="tel" :label="__('messages.phone_number')" :value="$applicant['phone'] ?? null"
                    :help="__t('messages.flow.phone_help')" inputmode="tel" placeholder="+998 90 123 45 67" autocomplete="tel" />
                <x-insurence.field name="email" type="email" :label="__t('messages.flow.email_optional')" :value="$applicant['email'] ?? null"
                    placeholder="name@example.com" autocomplete="email" />
            </div>
        </div>

        <x-insurence.actions :backUrl="route('osago.index', ['locale' => $locale])" :submit="__t('messages.next_step')" />
    </form>

    <x-slot:summary>
        <x-insurence.summary :premium="$premiumTotal" :items="$summaryItems" />
    </x-slot:summary>
</x-insurence.flow>
@endsection

@push('scripts')
<script>
document.getElementById('applicant_is_owner').addEventListener('change', function () {
    document.getElementById('applicant_block').hidden = this.checked;
});
document.querySelectorAll('input[name="owner_type"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        document.querySelectorAll('input[name="owner_type"]').forEach(function (r) {
            r.closest('.xf-chip').setAttribute('aria-pressed', r.checked ? 'true' : 'false');
        });
        document.getElementById('org_owner').hidden = this.value !== 'organization';
        document.getElementById('person_owner').hidden = this.value === 'organization';
    });
});
</script>
@endpush
