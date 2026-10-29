@extends('layouts.app')
@section('title', __t('messages.survey.title'))

@php $locale = getCurrentLocale(); @endphp

{{--
    Questionnaire, design "B": one question per screen with a progress bar. Without JS all three questions
    show at once and the criteria are ranked with selects (<noscript>); with JS they are ranked by tapping
    chips, which fill the same rank[key] inputs. $reasons / $products / $criteria = SurveyResponse option keys
--}}
@section('content')
<div class="in">
    <x-inner.head :title="__t('messages.survey.title')" :lead="__t('messages.survey.subtitle')" />

    <div class="in-wrap">
        @if (session('status'))
            <div class="in-wizard">
                <div class="in-thanks" role="status">
                    <span class="in-thanks__icon"><i class="bi bi-check-lg" aria-hidden="true"></i></span>
                    <h2 class="in-panel__title">{{ session('status') }}</h2>
                    <p class="in-meta">{{ __t('messages.survey.sent_text') }}</p>
                    <a class="in-btn in-btn--line" href="{{ route('home', ['locale' => $locale]) }}">{{ __t('messages.error_page.home') }}</a>
                </div>
            </div>
        @else
            <form action="{{ route('questionnaire.store', ['locale' => $locale]) }}" method="POST" class="in-wizard" data-wizard>
                @csrf
                <div class="in-sr" aria-hidden="true">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="in-progress" data-wizard-progress hidden>
                    <span>{{ __t('messages.survey.title') }}</span>
                    <span class="in-progress__bar"><i data-wizard-bar></i></span>
                    <b data-wizard-count>1 / 3</b>
                </div>

                <fieldset class="in-step" data-step @if ($errors->hasAny(['reason', 'reason_other'])) data-error @endif>
                    <legend>{{ __t('messages.survey.q_reason') }}</legend>
                    <div class="in-tiles in-tiles--one">
                        @foreach ($reasons as $key)
                            <label class="in-tile">
                                <input type="radio" name="reason" value="{{ $key }}" @checked(old('reason') === $key)>
                                <span>{{ __t('messages.survey.reasons.' . $key) }}</span>
                            </label>
                        @endforeach
                    </div>
                    <input type="text" name="reason_other" class="in-input" maxlength="300" value="{{ old('reason_other') }}"
                        placeholder="{{ __t('messages.survey.other_placeholder') }}" aria-label="{{ __t('messages.survey.reasons.other') }}">
                    @error('reason')<p class="in-error">{{ $message }}</p>@enderror
                    @error('reason_other')<p class="in-error">{{ $message }}</p>@enderror
                </fieldset>

                <fieldset class="in-step" data-step @if ($errors->hasAny(['products', 'product_other'])) data-error @endif>
                    <legend>{{ __t('messages.survey.q_products') }}</legend>
                    <p class="in-help">{{ __t('messages.survey.q_products_help') }}</p>
                    <div class="in-tiles">
                        @foreach ($products as $key)
                            <label class="in-tile">
                                <input type="checkbox" name="products[]" value="{{ $key }}" @checked(in_array($key, (array) old('products', []), true))>
                                <span>{{ __t('messages.survey.products.' . $key) }}</span>
                            </label>
                        @endforeach
                    </div>
                    <input type="text" name="product_other" class="in-input" maxlength="300" value="{{ old('product_other') }}"
                        placeholder="{{ __t('messages.survey.other_placeholder') }}" aria-label="{{ __t('messages.survey.products.other') }}">
                    @error('products')<p class="in-error">{{ $message }}</p>@enderror
                    @error('product_other')<p class="in-error">{{ $message }}</p>@enderror
                </fieldset>

                <fieldset class="in-step" data-step @error('rank') data-error @enderror>
                    <legend>{{ __t('messages.survey.q_criteria') }}</legend>
                    <p class="in-help" data-rank-help hidden>{{ __t('messages.survey.rank_hint') }}</p>
                    <div class="in-ranks" data-ranks hidden>
                        @foreach ($criteria as $key)
                            <button type="button" class="in-rank" data-rank="{{ $key }}" data-initial="{{ old('rank.' . $key) }}" aria-pressed="false">
                                <span class="in-rank__num"></span>{{ __t('messages.survey.criteria.' . $key) }}
                            </button>
                        @endforeach
                    </div>
                    <noscript>
                        <p class="in-help">{{ __t('messages.survey.q_criteria_help') }}</p>
                        @foreach ($criteria as $key)
                            <label class="in-rank-select">
                                <select name="rank[{{ $key }}]" class="in-input">
                                    <option value="">—</option>
                                    @for ($i = 1; $i <= count($criteria); $i++)
                                        <option value="{{ $i }}" @selected((string) old('rank.' . $key) === (string) $i)>{{ $i }}</option>
                                    @endfor
                                </select>
                                <span>{{ __t('messages.survey.criteria.' . $key) }}</span>
                            </label>
                        @endforeach
                    </noscript>
                    @error('rank')<p class="in-error">{{ $message }}</p>@enderror
                </fieldset>

                <div class="in-wizard__nav">
                    <button type="button" class="in-wizard__back" data-wizard-back hidden>← {{ __t('messages.survey.back') }}</button>
                    <button type="button" class="in-submit in-wizard__next" data-wizard-next hidden>{{ __t('messages.survey.next') }} →</button>
                    <button type="submit" class="in-submit in-wizard__next" data-wizard-submit>{{ __t('messages.survey.send') }} →</button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Questionnaire wizard: one step at a time; criteria ranked by tapping chips (1 = first tapped)
    (function () {
        var form = document.querySelector('[data-wizard]');
        if (!form) return;
        var steps = Array.prototype.slice.call(form.querySelectorAll('[data-step]'));
        var back = form.querySelector('[data-wizard-back]');
        var next = form.querySelector('[data-wizard-next]');
        var submit = form.querySelector('[data-wizard-submit]');
        var bar = form.querySelector('[data-wizard-bar]');
        var count = form.querySelector('[data-wizard-count]');
        var current = Math.max(0, steps.findIndex(function (s) { return s.hasAttribute('data-error'); }));

        form.classList.add('is-js');
        form.querySelector('[data-wizard-progress]').hidden = false;
        form.querySelector('[data-ranks]').hidden = false;
        form.querySelector('[data-rank-help]').hidden = false;

        function show(i) {
            current = i;
            steps.forEach(function (s, k) { s.classList.toggle('is-current', k === i); });
            back.hidden = i === 0;
            next.hidden = i === steps.length - 1;
            submit.hidden = i !== steps.length - 1;
            bar.style.width = Math.round(i / steps.length * 100) + '%';
            count.textContent = (i + 1) + ' / ' + steps.length;
        }
        back.addEventListener('click', function () { show(Math.max(0, current - 1)); });
        next.addEventListener('click', function () {
            show(Math.min(steps.length - 1, current + 1));
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        // Ranking chips write rank[key] hidden inputs in the order they were tapped
        var chips = Array.prototype.slice.call(form.querySelectorAll('[data-rank]'));
        var order = chips.filter(function (c) { return c.dataset.initial; })
            .sort(function (a, b) { return a.dataset.initial - b.dataset.initial; })
            .map(function (c) { return c.dataset.rank; });
        var holder = document.createElement('div');
        form.appendChild(holder);

        function paint() {
            holder.innerHTML = '';
            chips.forEach(function (c) {
                var at = order.indexOf(c.dataset.rank);
                c.classList.toggle('is-picked', at !== -1);
                c.setAttribute('aria-pressed', at !== -1 ? 'true' : 'false');
                c.querySelector('.in-rank__num').textContent = at !== -1 ? at + 1 : '';
            });
            order.forEach(function (key, i) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'rank[' + key + ']';
                input.value = i + 1;
                holder.appendChild(input);
            });
        }
        chips.forEach(function (c) {
            c.addEventListener('click', function () {
                var at = order.indexOf(c.dataset.rank);
                if (at === -1) order.push(c.dataset.rank); else order.splice(at, 1);
                paint();
            });
        });

        paint();
        show(current);
    })();
</script>
@endpush
