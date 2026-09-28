@extends('layouts.app')
@section('title', __('insurance.osgor.page_title'))

{{-- OSGOR step 1: organization by INN (OsgorController::storeApplicant) --}}
@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.osgor.page_title')"
    :subtitle="__('insurance.osgor.subtitle')"
    :steps="$flowSteps"
    :current="1"
    :stepUrls="$flowUrls"
>
    <form action="{{ route('osgor.storeApplicant', ['locale' => getCurrentLocale()]) }}" method="POST" class="xf-panel">
        @csrf

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.org_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.flow.org_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">
            <div class="xf-row">
                <x-insurence.field
                    name="inn"
                    :label="__t('messages.inn')"
                    :value="$applicant['inn'] ?? null"
                    :help="__t('messages.flow.inn_help')"
                    inputmode="numeric"
                    maxlength="9"
                    placeholder="123456789"
                    autocomplete="off"
                    required
                />
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
                    :title="$applicant['name']"
                    :text="trim(($applicant['representativeName'] ?? '') . ' · ' . ($applicant['address'] ?? ''), ' ·')"
                />
            @else
                <p class="xf-note">
                    <i class="bi bi-info-circle"></i>
                    <span>{{ __t('messages.flow.org_subtitle') }}</span>
                </p>
            @endif
        </div>

        <x-insurence.actions :submit="__t('messages.next_step')" />
    </form>

    <x-slot:summary>
        <x-insurence.summary :premium="null" :items="$summaryItems" />
    </x-slot:summary>
</x-insurence.flow>
@endsection
