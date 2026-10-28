@extends('layouts.app')
@section('title', __('insurance.travel.page_title'))

{{--
    Travel step 1: countries, dates, purpose, travellers' birth dates → travel.calculate prices every
    available programme; the chosen one is priced again on the server by storeTrip.
    $countries: [id => [id, name, schengen]] in the current locale (TravelController::countryOptions)
--}}
@php
    $locale        = getCurrentLocale();
    $policyType    = (int) old('policy_type', $trip['policy_type'] ?? 0);
    $multiDays     = (int) old('multi_days', $trip['multi_days'] ?? 1);
    $startDate     = old('start_date', $trip['start_date'] ?? now()->addDay()->format('Y-m-d'));
    $endDate       = old('end_date', $trip['end_date'] ?? null);
    $purpose       = (int) old('trip_purpose', $trip['trip_purpose'] ?? 0);
    $travelersType = (int) old('travelers_type', $trip['travelers_type'] ?? 0);
    $chosen        = array_map('intval', (array) old('countries', $trip['countries'] ?? []));
    $birthdates    = array_values((array) old('birthdates', $trip['birthdates'] ?? ['']));
    $program       = (int) old('program', $trip['program'] ?? 0);
    $tripError     = $errors->first('program') ?: $errors->first('countries') ?: $errors->first('birthdates') ?: $errors->first('birthdates.*');
@endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.travel.page_title')"
    :subtitle="__('insurance.travel.subtitle')"
    :steps="$flowSteps"
    :current="1"
    :stepUrls="$flowUrls"
>
    <form action="{{ route('travel.storeTrip', ['locale' => $locale]) }}" method="POST" class="xf-panel" id="trip_form" novalidate>
        @csrf

        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.travel.trip_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.travel.trip_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">
            @if ($referenceError)
                <div class="xf-alert" role="alert">
                    <i class="bi bi-exclamation-circle"></i>
                    <span>{{ $referenceError }}</span>
                </div>
            @endif

            {{-- ── Policy type ── --}}
            <div class="xf-field">
                <span class="xf-field__label" id="type_label">{{ __t('messages.travel.policy_type') }}</span>
                <div class="xf-chips" role="radiogroup" aria-labelledby="type_label">
                    @foreach ([0, 1] as $option)
                        <label class="xf-chip" aria-pressed="{{ $policyType === $option ? 'true' : 'false' }}">
                            <input type="radio" name="policy_type" value="{{ $option }}" @checked($policyType === $option) class="visually-hidden" data-recalc>
                            {{ __t('messages.travel.policy_types.' . $option) }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ── Countries ── --}}
            <div class="xf-field xf-picker" id="countries_field">
                <label for="country_search" class="xf-field__label">{{ __t('messages.travel.countries') }}</label>
                <div class="xf-picker__chosen" id="country_chips" aria-live="polite"></div>
                <div class="xf-field__control">
                    <input type="text" id="country_search" class="xf-input @error('countries') is-invalid @enderror" autocomplete="off"
                           placeholder="{{ __t('messages.travel.countries_placeholder') }}" role="combobox" aria-expanded="false"
                           aria-controls="country_list" aria-autocomplete="list">
                </div>
                <ul class="xf-picker__list" id="country_list" role="listbox" hidden></ul>
                <div id="country_inputs"></div>
                <p class="xf-field__help">{{ __t('messages.travel.countries_help') }}</p>
            </div>

            {{-- ── Dates ── --}}
            <div class="xf-row">
                <x-insurence.field name="start_date" id="start_date" type="date" :label="__t('messages.travel.start_date')"
                    :value="$startDate" :min="now()->format('Y-m-d')" required data-recalc />
                <div data-show="0" @if ($policyType === 1) hidden @endif>
                    <x-insurence.field name="end_date" id="end_date" type="date" :label="__t('messages.travel.end_date')"
                        :value="$endDate" :min="now()->format('Y-m-d')" data-recalc />
                </div>
                <div class="xf-field" data-show="1" @if ($policyType === 0) hidden @endif>
                    <label for="multi_days" class="xf-field__label">{{ __t('messages.travel.multi_days_label') }}</label>
                    <select name="multi_days" id="multi_days" class="xf-input" data-recalc>
                        @foreach (array_keys(\App\Http\Controllers\Insurence\TravelController::MULTI_DAYS) as $option)
                            <option value="{{ $option }}" @selected($multiDays === $option)>{{ __t('messages.travel.multi_days.' . $option) }}</option>
                        @endforeach
                    </select>
                    <p class="xf-field__help">{{ __t('messages.travel.multi_days_help') }}</p>
                </div>
            </div>

            {{-- ── Purpose, travellers ── --}}
            <div class="xf-row">
                <div class="xf-field">
                    <label for="trip_purpose" class="xf-field__label">{{ __t('messages.travel.purpose') }}</label>
                    <select name="trip_purpose" id="trip_purpose" class="xf-input" data-recalc>
                        @foreach (\App\Http\Controllers\Insurence\TravelController::PURPOSES as $option)
                            <option value="{{ $option }}" @selected($purpose === $option)>{{ __t('messages.travel.purposes.' . $option) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="xf-field">
                    <label for="travelers_type" class="xf-field__label">{{ __t('messages.travel.travelers_type') }}</label>
                    <select name="travelers_type" id="travelers_type" class="xf-input" data-recalc>
                        @foreach (array_keys(\App\Http\Controllers\Insurence\TravelController::TRAVELER_TYPES) as $option)
                            <option value="{{ $option }}" @selected($travelersType === $option)>{{ __t('messages.travel.travelers_types.' . $option) }}</option>
                        @endforeach
                    </select>
                    <p class="xf-field__help" id="travelers_type_help">{{ __t('messages.travel.travelers_type_help.' . $travelersType) }}</p>
                </div>
            </div>

            {{-- ── Birth dates ── --}}
            <div class="xf-field">
                <span class="xf-field__label" id="birthdates_label">{{ __t('messages.travel.birthdates') }}</span>
                <ol class="xf-birthdates" id="birthdates" aria-labelledby="birthdates_label">
                    @foreach ($birthdates as $i => $date)
                        <li class="xf-birthdates__row">
                            <span class="xf-birthdates__no">{{ $i + 1 }}</span>
                            <input type="date" name="birthdates[]" value="{{ $date }}" max="{{ now()->format('Y-m-d') }}" class="xf-input" required data-recalc
                                   aria-label="{{ __t('messages.travel.birthdates') }} {{ $i + 1 }}">
                            <button type="button" class="xf-person__remove" data-remove title="{{ __t('messages.travel.remove') }}" aria-label="{{ __t('messages.travel.remove') }}">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </li>
                    @endforeach
                </ol>
                <div>
                    <button type="button" class="xf-link-btn" id="add_birthdate"><i class="bi bi-person-plus"></i> {{ __t('messages.travel.add_birthdate') }}</button>
                </div>
                <p class="xf-field__help">{{ __t('messages.travel.birthdates_help') }}</p>
            </div>

            <div>
                <button type="button" class="xf-btn xf-btn--soft" id="calc_btn">
                    <i class="bi bi-calculator"></i> {{ __t('messages.travel.calculate') }}
                </button>
            </div>

            {{-- ── Programmes (filled by travel.calculate) ── --}}
            <section class="xf-programs-wrap" id="programs_wrap" hidden aria-live="polite">
                <h3 class="xf-subpanel__title">{{ __t('messages.travel.programs_title') }}</h3>
                <p class="xf-field__help" id="trip_facts"></p>
                <div class="xf-programs" id="programs" role="radiogroup" aria-label="{{ __t('messages.travel.programs_title') }}"></div>
                <p class="xf-field__help">{{ __t('messages.travel.programs_hint') }}</p>
            </section>

            <p id="trip_error" class="xf-field__error" role="alert" @unless ($tripError) hidden @endunless>{{ $tripError }}</p>
        </div>

        <x-insurence.actions :submit="__t('messages.next_step')" :total="$premiumTotal" id="trip_submit" />
    </form>

    <x-slot:summary>
        <x-insurence.summary :premium="$premiumTotal" :items="$summaryItems" />
    </x-slot:summary>
</x-insurence.flow>
@endsection

@push('scripts')
<script>
(function () {
    var CSRF      = document.querySelector('meta[name="csrf-token"]').content;
    var COUNTRIES = @json(array_values($countries));
    var CHOSEN    = @json($chosen);
    var PROGRAM   = @json($program);
    var T = {
        empty: @json(__t('messages.travel.countries_empty')),
        remove: @json(__t('messages.travel.remove')),
        liability: @json(__t('messages.travel.liability')),
        unavailable: @json(__t('messages.travel.program_unavailable')),
        days: @json(__t('messages.travel.trip_days')),
        schengen: @json(__t('messages.travel.schengen_note')),
        calculating: @json(__t('messages.flow.calculating')),
        error: @json(__('messages.error_occurred')),
        chooseProgram: @json(__t('messages.travel.program_required')),
        typeHelp: @json(__t('messages.travel.travelers_type_help')),
        birthdates: @json(__t('messages.travel.birthdates')),
    };

    var form      = document.getElementById('trip_form');
    var search    = document.getElementById('country_search');
    var list      = document.getElementById('country_list');
    var chips     = document.getElementById('country_chips');
    var inputs    = document.getElementById('country_inputs');
    var dates     = document.getElementById('birthdates');
    var wrap      = document.getElementById('programs_wrap');
    var programs  = document.getElementById('programs');
    var facts     = document.getElementById('trip_facts');
    var err       = document.getElementById('trip_error');
    var calcBtn   = document.getElementById('calc_btn');
    var byId      = {};
    var chosen    = [];
    var dirty     = true;

    COUNTRIES.forEach(function (c) { byId[c.id] = c; });

    function norm(s) { return String(s).toLowerCase().replace(/[‘’ʻʼ`']/g, "'"); }

    function setTotal(text) {
        document.querySelectorAll('[data-summary="total"], #sidebar_premium').forEach(function (el) {
            el.textContent = text;
            el.classList.toggle('is-empty', text === '—');
        });
    }

    function showError(text) {
        err.textContent = text || '';
        err.hidden = !text;
    }

    // Prices are stale once the trip changes
    function stale() {
        dirty = true;
        wrap.hidden = true;
        programs.innerHTML = '';
        setTotal('—');
    }

    // ── Countries ──
    function renderChosen() {
        chips.innerHTML = '';
        inputs.innerHTML = '';
        chosen.forEach(function (id) {
            var c = byId[id];
            if (!c) return;
            var chip = document.createElement('span');
            chip.className = 'xf-picker__chip';
            chip.textContent = c.name;
            var x = document.createElement('button');
            x.type = 'button';
            x.setAttribute('aria-label', T.remove + ': ' + c.name);
            x.innerHTML = '<i class="bi bi-x"></i>';
            x.addEventListener('click', function () { chosen = chosen.filter(function (v) { return v !== id; }); renderChosen(); stale(); });
            chip.appendChild(x);
            chips.appendChild(chip);

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'countries[]';
            input.value = id;
            inputs.appendChild(input);
        });
    }

    function closeList() { list.hidden = true; search.setAttribute('aria-expanded', 'false'); }

    function renderList() {
        var q = norm(search.value.trim());
        list.innerHTML = '';
        if (q === '') { closeList(); return; }

        var found = COUNTRIES.filter(function (c) { return chosen.indexOf(c.id) < 0 && norm(c.name).indexOf(q) >= 0; }).slice(0, 8);
        if (!found.length) {
            var none = document.createElement('li');
            none.className = 'xf-picker__none';
            none.textContent = T.empty;
            list.appendChild(none);
        }
        found.forEach(function (c, i) {
            var li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.dataset.id = c.id;
            li.textContent = c.name;
            if (i === 0) li.classList.add('is-active');
            li.addEventListener('mousedown', function (e) { e.preventDefault(); pick(c.id); });
            list.appendChild(li);
        });
        list.hidden = false;
        search.setAttribute('aria-expanded', 'true');
    }

    function pick(id) {
        if (chosen.indexOf(id) < 0) chosen.push(id);
        search.value = '';
        closeList();
        renderChosen();
        stale();
        search.focus();
    }

    search.addEventListener('input', renderList);
    search.addEventListener('blur', closeList);
    search.addEventListener('keydown', function (e) {
        var items = Array.prototype.slice.call(list.querySelectorAll('[role="option"]'));
        var at = items.findIndex(function (li) { return li.classList.contains('is-active'); });
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            if (!items.length) return;
            items[at] && items[at].classList.remove('is-active');
            at = (at + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
            items[at].classList.add('is-active');
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (items[at]) pick(Number(items[at].dataset.id));
        } else if (e.key === 'Escape') {
            closeList();
        }
    });

    CHOSEN.forEach(function (id) { if (byId[id]) chosen.push(id); });
    renderChosen();

    // ── Policy type: single trip has a return date, multiple trips a period ──
    form.querySelectorAll('input[name="policy_type"]').forEach(function (r) {
        r.addEventListener('change', function () {
            form.querySelectorAll('input[name="policy_type"]').forEach(function (o) {
                o.closest('.xf-chip').setAttribute('aria-pressed', o.checked ? 'true' : 'false');
            });
            form.querySelectorAll('[data-show]').forEach(function (el) { el.hidden = el.dataset.show !== r.value; });
        });
    });

    document.getElementById('travelers_type').addEventListener('change', function () {
        document.getElementById('travelers_type_help').textContent = T.typeHelp[this.value] || '';
    });

    // ── Birth dates ──
    function renumber() {
        dates.querySelectorAll('.xf-birthdates__row').forEach(function (row, i) {
            row.querySelector('.xf-birthdates__no').textContent = i + 1;
            row.querySelector('input').setAttribute('aria-label', T.birthdates + ' ' + (i + 1));
            row.querySelector('[data-remove]').hidden = dates.children.length === 1;
        });
    }

    dates.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-remove]');
        if (!btn) return;
        btn.closest('.xf-birthdates__row').remove();
        renumber();
        stale();
    });

    document.getElementById('add_birthdate').addEventListener('click', function () {
        var row = dates.lastElementChild.cloneNode(true);
        row.querySelector('input').value = '';
        dates.appendChild(row);
        renumber();
        stale();
        row.querySelector('input').focus();
    });

    renumber();

    form.addEventListener('change', function (e) { if (e.target.matches('[data-recalc]')) stale(); });

    // ── Prices of every programme ──
    function money(n) { return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + @json(__t('messages.currency')); }

    function renderPrograms(json) {
        programs.innerHTML = '';
        var t = json.trip || {};
        facts.textContent = t.days ? T.days.replace(':days', t.days).replace(':date', t.policy_end_date || '') + (t.schengen ? ' ' + T.schengen : '') : '';

        json.programs.forEach(function (p) {
            var label = document.createElement('label');
            label.className = 'xf-program' + (p.available ? '' : ' is-disabled');

            var radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'program';
            radio.value = p.id;
            radio.className = 'visually-hidden';
            radio.disabled = !p.available;
            radio.checked = p.available && p.id === PROGRAM;
            radio.addEventListener('change', function () {
                PROGRAM = p.id;
                programs.querySelectorAll('.xf-program').forEach(function (l) { l.classList.toggle('is-selected', l.contains(radio)); });
                setTotal(p.price);
                showError('');
            });

            label.appendChild(radio);
            label.insertAdjacentHTML('beforeend',
                '<span class="xf-program__name"></span>' +
                '<span class="xf-program__sum"></span>' +
                '<span class="xf-program__price"></span>');
            label.querySelector('.xf-program__name').textContent = p.name;
            label.querySelector('.xf-program__sum').textContent = T.liability + ': ' + String(p.liability).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' €';
            label.querySelector('.xf-program__price').textContent = p.available ? p.price : (p.reason || T.unavailable);
            if (radio.checked) { label.classList.add('is-selected'); setTotal(p.price); }
            programs.appendChild(label);
        });

        wrap.hidden = false;
        dirty = false;
    }

    async function calculate() {
        showError('');
        var data = new FormData(form);
        data.delete('program');
        calcBtn.disabled = true;
        setTotal(T.calculating);

        try {
            var res  = await fetch(@json(route('travel.calculate', ['locale' => $locale])), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: data,
            });
            var json = await res.json();
            if (res.ok && json.success) {
                renderPrograms(json);
                if (!programs.querySelector('input:checked')) setTotal('—');
            } else {
                stale();
                var first = json.errors ? Object.values(json.errors)[0][0] : null;
                showError(json.message && !first ? json.message : (first || json.message || T.error));
            }
        } catch (e) {
            stale();
            showError(T.error);
        } finally {
            calcBtn.disabled = false;
        }
    }

    calcBtn.addEventListener('click', calculate);

    form.addEventListener('submit', function (e) {
        if (dirty || !programs.querySelector('input:checked')) {
            e.preventDefault();
            if (dirty) { calculate(); } else { showError(T.chooseProgram); }
        }
    });

    // Back on this step with a trip already chosen: show its prices again
    if (chosen.length && dates.querySelector('input').value) calculate();
})();
</script>
@endpush
