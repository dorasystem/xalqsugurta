@extends('layouts.app')
@section('title', __('insurance.' . $flow['key'] . '.page_title'))

@php
    $locale      = getCurrentLocale();
    $offertaPath = $product?->{'offerta_' . $locale};
    $offertaUrl  = $offertaPath ? \Illuminate\Support\Facades\Storage::url($offertaPath) : null;
@endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.' . $flow['key'] . '.page_title')"
    :subtitle="__('insurance.' . $flow['key'] . '.subtitle')"
    :steps="$flowSteps"
    :current="3"
    :stepUrls="$flowUrls"
>
    <form action="{{ route($flow['key'] . '.storeApplication', ['locale' => $locale]) }}" method="POST" class="xf-panel">
        @csrf

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.check_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.flow.check_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">
            <x-insurence.review
                :title="__t('messages.flow.applicant')"
                :editUrl="route($flow['key'] . '.index', ['locale' => $locale])"
                :items="[
                    __('messages.full_name')                                              => $applicantName,
                    __('insurance.passport.series') . ' / ' . __('insurance.passport.number') => $applicant['passport_seria'] . ' ' . $applicant['passport_number'],
                    __('insurance.passport.birth_date')                                   => \Carbon\Carbon::parse($applicant['birth_date'])->format('d.m.Y'),
                    __('messages.phone_number')                                           => formatPhone($applicant['phone']),
                ]"
            />

            <x-insurence.review
                :title="$objectTitle"
                :editUrl="$flowUrls[1]"
                :items="$objectReview"
            />

            <x-insurence.review
                :title="__t('messages.flow.policy_terms')"
                :editUrl="$flowUrls[1]"
                :items="[
                    __('messages.insurance_sum')     => formatMoney($calculation['insurance_amount']),
                    __t('messages.flow.period')      => $summaryItems['period'][1],
                    __('messages.insurance_premium') => formatMoney($calculation['insurance_premium']),
                ]"
            />

            <div>
                <label class="xf-agree" for="offerta_agreed">
                    <input type="checkbox" id="offerta_agreed" name="offerta_agreed" value="1"
                           @checked(old('offerta_agreed'))
                           @error('offerta_agreed') aria-invalid="true" aria-describedby="offerta_error" @enderror>
                    <span>
                        @if ($offertaUrl)
                            {!! __('messages.offerta_agree_with_link', [
                                'link' => '<a href="' . e($offertaUrl) . '" target="_blank" rel="noopener">' . e(__('messages.offerta_link_text')) . '</a>',
                            ]) !!}
                        @else
                            {{ __('messages.offerta_agree') }}
                        @endif
                    </span>
                </label>
                @error('offerta_agreed')
                    <p class="xf-field__error" id="offerta_error" style="margin-top: 6px">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <x-insurence.actions
            :backUrl="$flowUrls[1]"
            :submit="__('messages.proceed_to_payment')"
            :total="$calculation['insurance_premium']"
        />
    </form>

    <x-slot:summary>
        <x-insurence.summary
            :premium="$calculation['insurance_premium']"
            :rate="$flow['rateLabel']"
            :items="$summaryItems"
        />
    </x-slot:summary>
</x-insurence.flow>
@endsection
