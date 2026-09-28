@extends('layouts.app')
@section('title', __('insurance.' . $flow['key'] . '.page_title'))

{{--
    Shared confirm step. Expects from the controller's flow trait:
    $flow, $flowSteps, $flowUrls (last = this page), $applicant, $applicantName,
    $confirmBlocks ([title, editUrl, items]), $premiumTotal, $summaryItems, $product
--}}
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
    :current="count($flowSteps) - 1"
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
                    __t('messages.flow.pinfl')                                            => $applicant['pinfl'] ?? null,
                    __('insurance.passport.birth_date')                                   => filled($applicant['birth_date'] ?? null) ? \Carbon\Carbon::parse($applicant['birth_date'])->format('d.m.Y') : null,
                    __('messages.phone_number')                                           => formatPhone($applicant['phone']),
                ]"
            />

            @foreach ($confirmBlocks as $block)
                <x-insurence.review
                    :title="$block['title']"
                    :editUrl="$block['editUrl'] ?? null"
                    :items="$block['items']"
                    :wide="$block['wide'] ?? false"
                />
            @endforeach

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
            :backUrl="$flowUrls[count($flowUrls) - 2]"
            :submit="__('messages.proceed_to_payment')"
            :total="$premiumTotal"
        />
    </form>

    <x-slot:summary>
        <x-insurence.summary
            :premium="$premiumTotal"
            :rate="$flow['rateLabel'] ?? null"
            :items="$summaryItems"
        />
    </x-slot:summary>
</x-insurence.flow>
@endsection
