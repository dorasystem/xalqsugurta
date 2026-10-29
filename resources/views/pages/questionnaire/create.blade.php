@extends('layouts.app')
@section('title', __t('messages.survey.title'))

@php $locale = getCurrentLocale(); @endphp

{{-- Questionnaire. $reasons / $products / $criteria = SurveyResponse option keys --}}
@section('content')
<x-insurence.flow icon="bi-ui-checks" :title="__t('messages.survey.title')" :subtitle="__t('messages.survey.subtitle')">

    @if (session('status'))
        <x-insurence.found :title="session('status')" :text="__t('messages.survey.sent_text')" />
    @else
        <form action="{{ route('questionnaire.store', ['locale' => $locale]) }}" method="POST" class="xf-panel">
            @csrf
            <div class="visually-hidden" aria-hidden="true">
                <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>
            <div class="xf-panel__body xf-survey">

                <fieldset class="xf-field">
                    <legend class="xf-field__label">1. {{ __t('messages.survey.q_reason') }}</legend>
                    <div class="xf-survey__options">
                        @foreach ($reasons as $key)
                            <label class="xf-survey__option">
                                <input type="radio" name="reason" value="{{ $key }}" @checked(old('reason') === $key)>
                                <span>{{ __t('messages.survey.reasons.' . $key) }}</span>
                            </label>
                        @endforeach
                    </div>
                    <input type="text" name="reason_other" class="xf-input" maxlength="300" value="{{ old('reason_other') }}"
                        placeholder="{{ __t('messages.survey.other_placeholder') }}" aria-label="{{ __t('messages.survey.reasons.other') }}">
                    @error('reason')<p class="xf-field__error">{{ $message }}</p>@enderror
                    @error('reason_other')<p class="xf-field__error">{{ $message }}</p>@enderror
                </fieldset>

                <fieldset class="xf-field">
                    <legend class="xf-field__label">2. {{ __t('messages.survey.q_products') }}</legend>
                    <p class="xf-field__help">{{ __t('messages.survey.q_products_help') }}</p>
                    <div class="xf-survey__options xf-survey__options--grid">
                        @foreach ($products as $key)
                            <label class="xf-survey__option">
                                <input type="checkbox" name="products[]" value="{{ $key }}" @checked(in_array($key, (array) old('products', []), true))>
                                <span>{{ __t('messages.survey.products.' . $key) }}</span>
                            </label>
                        @endforeach
                    </div>
                    <input type="text" name="product_other" class="xf-input" maxlength="300" value="{{ old('product_other') }}"
                        placeholder="{{ __t('messages.survey.other_placeholder') }}" aria-label="{{ __t('messages.survey.products.other') }}">
                    @error('products')<p class="xf-field__error">{{ $message }}</p>@enderror
                    @error('product_other')<p class="xf-field__error">{{ $message }}</p>@enderror
                </fieldset>

                <fieldset class="xf-field">
                    <legend class="xf-field__label">3. {{ __t('messages.survey.q_criteria') }}</legend>
                    <p class="xf-field__help">{{ __t('messages.survey.q_criteria_help') }}</p>
                    <div class="xf-survey__ranks">
                        @foreach ($criteria as $key)
                            <label class="xf-survey__rank">
                                <select name="rank[{{ $key }}]" class="xf-input">
                                    <option value="">—</option>
                                    @for ($i = 1; $i <= count($criteria); $i++)
                                        <option value="{{ $i }}" @selected((string) old('rank.' . $key) === (string) $i)>{{ $i }}</option>
                                    @endfor
                                </select>
                                <span>{{ __t('messages.survey.criteria.' . $key) }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('rank')<p class="xf-field__error">{{ $message }}</p>@enderror
                </fieldset>
            </div>
            <x-insurence.actions :payment="false" :submit="__t('messages.survey.send')" />
        </form>
    @endif

</x-insurence.flow>
@endsection
