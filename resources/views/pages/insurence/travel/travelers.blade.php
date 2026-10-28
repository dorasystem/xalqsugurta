@extends('layouts.app')
@section('title', __('insurance.travel.page_title'))

{{-- Travel step 3: each traveller by passport + PINFL (registry lookup on the server), in order --}}
@php
    $locale   = getCurrentLocale();
    $done     = count($travelers);
    $complete = $done === $expected;
    $typed    = $trip['birthdates'] ?? [];
@endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.travel.page_title')"
    :subtitle="__('insurance.travel.subtitle')"
    :steps="$flowSteps"
    :current="3"
    :stepUrls="$flowUrls"
>
    <div class="xf-panel">
        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.travel.travelers_title') }}</h2>
            <p class="xf-panel__subtitle">
                {{ __t('messages.travel.travelers_subtitle') }}
                <b>{{ __t('messages.travel.travelers_progress', ['done' => $done, 'total' => $expected]) }}</b>
            </p>
        </div>

        <div class="xf-panel__body">
            @if (($trip['travelers_type'] ?? 0) === 1)
                <p class="xf-note"><i class="bi bi-info-circle"></i><span>{{ __t('messages.travel.travelers_family') }}</span></p>
            @endif

            @if ($travelers)
                <ul class="xf-people">
                    @foreach ($travelers as $i => $t)
                        <li class="xf-person">
                            <span class="xf-person__avatar" aria-hidden="true">{{ mb_substr($t['lastname'], 0, 1) }}{{ mb_substr($t['firstname'], 0, 1) }}</span>
                            <div class="xf-person__main">
                                <span class="xf-person__name">{{ $i + 1 }}. {{ trim($t['lastname'] . ' ' . $t['firstname'] . ' ' . ($t['middlename'] ?? '')) }}</span>
                                <span class="xf-person__meta">
                                    {{ $t['passport_seria'] }} {{ $t['passport_number'] }} · {{ \Carbon\Carbon::parse($t['birth_date'])->format('d.m.Y') }}
                                    @if (isset($typed[$i]) && $typed[$i] !== $t['birth_date'])
                                        · <span class="xf-hint">{{ __t('messages.travel.birth_date_differs') }}</span>
                                    @endif
                                </span>
                            </div>
                            <form method="POST" action="{{ route('travel.removeTraveler', ['locale' => $locale, 'index' => $i]) }}">
                                @csrf
                                <button type="submit" class="xf-person__remove" title="{{ __t('messages.flow.remove') }}" aria-label="{{ __t('messages.flow.remove') }}">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($done < $expected)
                <form method="POST" action="{{ route('travel.addTraveler', ['locale' => $locale]) }}" class="xf-subpanel">
                    @csrf
                    <div class="xf-subpanel__head">
                        <h3 class="xf-subpanel__title"><i class="bi bi-person-plus"></i> {{ __t('messages.travel.add_traveler') }} {{ $done + 1 }}</h3>
                    </div>
                    <div class="xf-row" style="--xf-cols: 3">
                        <x-insurence.field name="passport_seria" :label="__('insurance.passport.series')" placeholder="AA" maxlength="4"
                            autocomplete="off" style="text-transform: uppercase" required />
                        <x-insurence.field name="passport_number" :label="__('insurance.passport.number')" placeholder="1234567" maxlength="7"
                            inputmode="numeric" autocomplete="off" required />
                        <x-insurence.field name="pinfl" :label="__t('messages.flow.pinfl')" placeholder="31234567890123" maxlength="14"
                            inputmode="numeric" autocomplete="off" required />
                    </div>
                    <div>
                        <button type="submit" class="xf-btn xf-btn--soft"><i class="bi bi-search"></i> {{ __t('messages.travel.add_traveler') }}</button>
                    </div>
                </form>
            @elseif ($done > $expected)
                <div class="xf-alert" role="alert"><i class="bi bi-exclamation-circle"></i><span>{{ __t('messages.travel.travelers_count', ['count' => $expected]) }}</span></div>
            @endif
        </div>

        <form method="POST" action="{{ route('travel.confirmTravelers', ['locale' => $locale]) }}">
            @csrf
            <x-insurence.actions :backUrl="route('travel.getApplicant', ['locale' => $locale])" :submit="__t('messages.next_step')"
                :total="$premiumTotal" :disabled="!$complete" />
        </form>
    </div>

    <x-slot:summary>
        <x-insurence.summary :premium="$premiumTotal" :items="$summaryItems" />
    </x-slot:summary>
</x-insurence.flow>
@endsection
