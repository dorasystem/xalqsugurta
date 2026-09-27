@extends('layouts.app')
@section('title', __('insurance.gas.page_title'))

@section('content')
<x-insurence.flow
    icon="bi-fire"
    :title="__('insurance.gas.page_title')"
    :subtitle="__('insurance.gas.subtitle')"
    :steps="$flowSteps"
    :current="1"
    :stepUrls="$flowUrls"
>
    <form action="{{ route('gas.storeApplicant', ['locale' => getCurrentLocale()]) }}" method="POST" class="xf-panel">
        @csrf

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.applicant_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.flow.applicant_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">
            <div class="xf-row" style="--xf-cols: 3">
                <x-insurence.field
                    name="passport_seria"
                    :label="__('insurance.passport.series')"
                    :value="$applicant['passport_seria'] ?? null"
                    :help="__t('messages.flow.passport_series_help')"
                    maxlength="4"
                    placeholder="AA"
                    autocomplete="off"
                    style="text-transform: uppercase"
                    required
                />
                <x-insurence.field
                    name="passport_number"
                    :label="__('insurance.passport.number')"
                    :value="$applicant['passport_number'] ?? null"
                    inputmode="numeric"
                    maxlength="7"
                    placeholder="1234567"
                    autocomplete="off"
                    required
                />
                <x-insurence.field
                    name="birth_date"
                    type="date"
                    :label="__('insurance.passport.birth_date')"
                    :value="$applicant['birth_date'] ?? null"
                    :max="now()->subDay()->format('Y-m-d')"
                    required
                />
            </div>

            <div class="xf-row">
                <x-insurence.field
                    name="phone"
                    type="tel"
                    :label="__('messages.phone_number')"
                    :value="$applicant['phone'] ?? null"
                    :help="__t('messages.flow.phone_help')"
                    inputmode="tel"
                    placeholder="+998 90 123 45 67"
                    autocomplete="tel"
                    required
                />
            </div>

            @if ($applicant)
                <x-insurence.found
                    :title="$applicantName"
                    :text="$applicant['address'] ?? ''"
                />
            @else
                <p class="xf-note">
                    <i class="bi bi-info-circle"></i>
                    <span>{{ __t('messages.flow.applicant_subtitle') }}</span>
                </p>
            @endif
        </div>

        <x-insurence.actions :submit="__t('messages.next_step')" />
    </form>

    <x-slot:summary>
        <x-insurence.summary :premium="null" rate="0,5" :items="$summaryItems" />
    </x-slot:summary>
</x-insurence.flow>
@endsection
