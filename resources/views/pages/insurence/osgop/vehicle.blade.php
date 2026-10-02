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

            {{-- Carrier licence: the registry does not have it, the insurer needs it --}}
            <section class="xf-subpanel" aria-labelledby="license_title">
                <div class="xf-subpanel__head">
                    <h3 class="xf-subpanel__title" id="license_title">
                        <i class="bi bi-file-earmark-check"></i> {{ __t('messages.flow.license_title') }}
                    </h3>
                </div>
                <p class="xf-field__help" style="margin: 0">{{ __t('messages.flow.license_help') }}</p>
                <div class="xf-row" style="--xf-cols: 2">
                    <x-insurence.field name="vehicle[license_seria]" id="f_license_seria" :label="__t('messages.flow.license_seria')"
                        :value="old('vehicle.license_seria', $vehicle['license']['seria'] ?? null)"
                        placeholder="AT" maxlength="2" autocomplete="off" style="text-transform: uppercase" />
                    <x-insurence.field name="vehicle[license_number]" id="f_license_number" :label="__t('messages.flow.license_number')"
                        :value="old('vehicle.license_number', $vehicle['license']['number'] ?? null)"
                        placeholder="1234567" maxlength="7" inputmode="numeric" autocomplete="off" />
                </div>
                <div class="xf-row" style="--xf-cols: 2">
                    <x-insurence.field name="vehicle[license_begin]" id="f_license_begin" type="date" :label="__t('messages.flow.license_begin')"
                        :value="old('vehicle.license_begin', $vehicle['license']['beginDate'] ?? null)" :max="now()->format('Y-m-d')" />
                    <x-insurence.field name="vehicle[license_end]" id="f_license_end" type="date" :label="__t('messages.flow.license_end')"
                        :value="old('vehicle.license_end', $vehicle['license']['endDate'] ?? null)" :min="now()->format('Y-m-d')" />
                </div>
                @foreach (['license_seria', 'license_number', 'license_begin', 'license_end'] as $field)
                    @error('vehicle.' . $field)
                        <p class="xf-field__error" role="alert">{{ $message }}</p>
                    @enderror
                @endforeach
            </section>

            @if (!empty($vehicle['gov_number']))
                <x-insurence.found
                    :title="trim(($vehicle['model_custom_name'] ?? '') . ', ' . $vehicle['gov_number'], ', ')"
                    :text="collect([
                        $vehicleType ?? null,
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
