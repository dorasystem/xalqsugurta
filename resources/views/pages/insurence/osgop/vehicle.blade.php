@extends('layouts.app')
@section('title', __('insurance.osgop.page_title'))

{{-- OSGOP step 2: the bus / taxi, found in the registry by plate + registration certificate --}}
@php $locale = getCurrentLocale(); @endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.osgop.page_title')"
    :subtitle="__('insurance.osgop.subtitle')"
    :steps="$flowSteps"
    :current="2"
    :stepUrls="$flowUrls"
>
    <form action="{{ route('osgop.storeVehicle', ['locale' => $locale]) }}" method="POST" class="xf-panel">
        @csrf

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.vehicle_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.flow.vehicle_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">
            <div class="xf-row" style="--xf-cols: 3">
                <x-insurence.field name="vehicle[gov_number]" id="f_gov" :label="__('messages.gov_number')" :value="$vehicle['gov_number'] ?? null"
                    placeholder="01A123BC" maxlength="10" autocomplete="off" style="text-transform: uppercase" />
                <x-insurence.field name="vehicle[tech_passport_seria]" id="f_tp_seria" :label="__('messages.tech_passport_series')" :value="$vehicle['tech_passport_seria'] ?? null"
                    placeholder="AAF" maxlength="3" autocomplete="off" style="text-transform: uppercase" />
                <x-insurence.field name="vehicle[tech_passport_number]" id="f_tp_number" :label="__('messages.tech_passport_number')" :value="$vehicle['tech_passport_number'] ?? null"
                    placeholder="1234567" maxlength="7" inputmode="numeric" autocomplete="off" />
            </div>

            @error('vehicle.gov_number')
                <p class="xf-field__error" role="alert">{{ $message }}</p>
            @enderror

            @if (!empty($vehicle['gov_number']))
                <x-insurence.found
                    :title="trim(($vehicle['model_custom_name'] ?? '') . ', ' . $vehicle['gov_number'], ', ')"
                    :text="collect([
                        !empty($vehicle['issue_year']) ? $vehicle['issue_year'] : null,
                        !empty($vehicle['number_of_seats']) ? $vehicle['number_of_seats'] . ' ' . __('messages.seats') : null,
                    ])->filter()->implode(' · ')"
                />
            @endif
        </div>

        <x-insurence.actions :backUrl="route('osgop.index', ['locale' => $locale])" :submit="__t('messages.next_step')" />
    </form>

    <x-slot:summary>
        <x-insurence.summary :premium="$premiumTotal" :items="$summaryItems" />
    </x-slot:summary>
</x-insurence.flow>
@endsection
