@extends('layouts.app')
@section('title', __('insurance.travel.page_title'))

{{-- Travel step 2: the policyholder (passport + PINFL lookup), region and address for travel/sale --}}
@php
    $locale   = getCurrentLocale();
    $travels  = $applicant ? in_array($applicant['pinfl'], array_column($travelers, 'pinfl'), true) : true;
    $regionId = (int) old('region_id', $applicant['region_id'] ?? 0);
@endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.travel.page_title')"
    :subtitle="__('insurance.travel.subtitle')"
    :steps="$flowSteps"
    :current="2"
    :stepUrls="$flowUrls"
>
    <form action="{{ route('travel.storeApplicant', ['locale' => $locale]) }}" method="POST" class="xf-panel">
        @csrf

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.travel.applicant_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.travel.applicant_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">
            <div class="xf-row" style="--xf-cols: 2">
                <x-insurence.field name="passport_seria" :label="__('insurance.passport.series')" :value="$applicant['passport_seria'] ?? null"
                    :help="__t('messages.flow.passport_series_help')" maxlength="4" placeholder="AA" autocomplete="off"
                    style="text-transform: uppercase" required />
                <x-insurence.field name="passport_number" :label="__('insurance.passport.number')" :value="$applicant['passport_number'] ?? null"
                    inputmode="numeric" maxlength="7" placeholder="1234567" autocomplete="off" required />
            </div>

            <div class="xf-row">
                <x-insurence.field name="pinfl" :label="__t('messages.flow.pinfl')" :value="$applicant['pinfl'] ?? null"
                    :help="__t('messages.flow.pinfl_help')" inputmode="numeric" maxlength="14" placeholder="31234567890123" autocomplete="off" required />
                <x-insurence.field name="phone" type="tel" :label="__('messages.phone_number')" :value="$applicant['phone'] ?? null"
                    :help="__t('messages.flow.phone_help')" inputmode="tel" placeholder="+998 90 123 45 67" autocomplete="tel" required />
            </div>

            <div class="xf-row">
                <div class="xf-field">
                    <label for="region_id" class="xf-field__label">{{ __t('messages.travel.region') }}</label>
                    <select name="region_id" id="region_id" class="xf-input @error('region_id') is-invalid @enderror" required
                            @error('region_id') aria-invalid="true" aria-describedby="region_id_error" @enderror>
                        <option value="">{{ __t('messages.travel.region_placeholder') }}</option>
                        @foreach ($regions as $id => $name)
                            <option value="{{ $id }}" @selected($regionId === (int) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('region_id')
                        <p class="xf-field__error" id="region_id_error">{{ $message }}</p>
                    @enderror
                </div>
                <x-insurence.field name="email" type="email" :label="__t('messages.travel.email')" :value="$applicant['email'] ?? null"
                    autocomplete="email" maxlength="100" />
            </div>

            <x-insurence.field name="address" :label="__t('messages.travel.address')" :value="$applicant['address'] ?? null"
                :help="__t('messages.travel.address_help')" maxlength="255" autocomplete="street-address" />

            <label class="xf-agree" for="travels">
                <input type="hidden" name="travels" value="0">
                <input type="checkbox" id="travels" name="travels" value="1" @checked(old('travels', $travels))>
                <span>{{ __t('messages.travel.travels') }}</span>
            </label>

            @if ($applicant)
                <x-insurence.found :title="$applicantName" :text="$applicant['address'] ?? ''" />
            @endif
        </div>

        <x-insurence.actions :backUrl="route('travel.index', ['locale' => $locale])" :submit="__t('messages.next_step')" :total="$premiumTotal" />
    </form>

    <x-slot:summary>
        <x-insurence.summary :premium="$premiumTotal" :items="$summaryItems" />
    </x-slot:summary>
</x-insurence.flow>
@endsection
