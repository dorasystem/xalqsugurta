@extends('layouts.app')
@section('title', __('insurance.osago.page_title'))

{{-- OSAGO step 1: the vehicle, found in the registry by plate + registration certificate --}}
@php $locale = getCurrentLocale(); @endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.osago.page_title')"
    :subtitle="__('insurance.osago.subtitle')"
    :steps="$flowSteps"
    :current="1"
    :stepUrls="$flowUrls"
>
    <form action="{{ route('osago.storeVehicle', ['locale' => $locale]) }}" method="POST" class="xf-panel">
        @csrf

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.vehicle_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.flow.vehicle_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">
            <div class="xf-row" style="--xf-cols: 3">
                <x-insurence.field name="gov_number" :label="__('messages.gov_number')" :value="$vehicle['gov_number'] ?? null"
                    placeholder="01A123BC" maxlength="10" autocomplete="off" style="text-transform: uppercase" />
                <x-insurence.field name="tech_passport_seria" :label="__('messages.tech_passport_series')" :value="$vehicle['tech_passport_seria'] ?? null"
                    placeholder="AAF" maxlength="3" autocomplete="off" style="text-transform: uppercase" />
                <x-insurence.field name="tech_passport_number" :label="__('messages.tech_passport_number')" :value="$vehicle['tech_passport_number'] ?? null"
                    placeholder="1234567" maxlength="7" inputmode="numeric" autocomplete="off" />
            </div>

            @if (!empty($vehicle['gov_number']))
                <x-insurence.found
                    :title="trim($vehicle['model'] . ', ' . $vehicle['gov_number'], ', ')"
                    :text="collect([$vehicleType, $vehicle['issue_year'] ?: null, $vehicle['owner_name'] ?: null])->filter()->implode(' · ')"
                />
            @else
                <p class="xf-note">
                    <i class="bi bi-info-circle"></i>
                    <span>{{ __t('messages.flow.osago_vehicle_hint') }}</span>
                </p>
            @endif
        </div>

        <x-insurence.actions :submit="__t('messages.next_step')" />
    </form>

    <x-slot:summary>
        <x-insurence.summary :premium="$premiumTotal" :items="$summaryItems" />
    </x-slot:summary>
</x-insurence.flow>
@endsection
