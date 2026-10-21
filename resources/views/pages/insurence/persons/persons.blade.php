@extends('layouts.app')
@section('title', __('insurance.' . $flow['key'] . '.page_title'))

{{--
    Step 2 of the persons flow (accident; tourist-ready).
    Expects PersonsFlow::flowViewData(): $flow, $persons, $applicant, $premiumTotal, ...
--}}
@php
    $locale        = getCurrentLocale();
    $key           = $flow['key'];
    $hasPersons    = !empty($persons);
    $sumDefault    = (int) old('sum_insured', $flow['default']);
    $applicantIn   = $applicant && in_array($applicant['pinfl'] ?? null, array_column($persons, 'pinfl'), true);
    $addError      = $errors->first('person') ?: $errors->first('sum_insured') ?: $errors->first('pinfl');
@endphp

@section('content')
<x-insurence.flow
    :icon="$flow['icon']"
    :title="__('insurance.' . $key . '.page_title')"
    :subtitle="__('insurance.' . $key . '.subtitle')"
    :steps="$flowSteps"
    :current="2"
    :stepUrls="$flowUrls"
>
    <div class="xf-panel">
        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.flow.persons_title') }}</h2>
            <p class="xf-panel__subtitle">{{ __t('messages.flow.persons_subtitle') }}</p>
        </div>

        <div class="xf-panel__body">

            {{-- ── Current list ── --}}
            @if ($hasPersons)
                <ul class="xf-people">
                    @foreach ($persons as $i => $person)
                        <li class="xf-person">
                            <span class="xf-person__avatar" aria-hidden="true">
                                {{ mb_substr($person['lastname'], 0, 1) }}{{ mb_substr($person['firstname'], 0, 1) }}
                            </span>
                            <div class="xf-person__main">
                                <span class="xf-person__name">{{ trim($person['lastname'] . ' ' . $person['firstname'] . ' ' . ($person['middlename'] ?? '')) }}</span>
                                <span class="xf-person__meta">{{ $person['passport_seria'] }} {{ $person['passport_number'] }} · {{ __t('messages.flow.pinfl') }}: {{ $person['pinfl'] }}</span>
                            </div>
                            <div class="xf-person__sum">
                                {{ formatMoney($person['sum_insured']) }}
                                <small>{{ __('messages.insurance_premium') }}: {{ formatMoney($person['insurance_premium']) }}</small>
                            </div>
                            <form method="POST" action="{{ route($key . '.removePerson', ['locale' => $locale, 'index' => $i]) }}">
                                @csrf
                                <button type="submit" class="xf-person__remove" title="{{ __t('messages.flow.remove') }}" aria-label="{{ __t('messages.flow.remove') }}">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="xf-note">
                    <i class="bi bi-info-circle"></i>
                    <span>{{ __t('messages.flow.persons_empty') }}</span>
                </p>
            @endif

            {{-- ── Add a person ── --}}
            <section class="xf-subpanel" aria-labelledby="add_title">
                <div class="xf-subpanel__head">
                    <h3 class="xf-subpanel__title" id="add_title">
                        <i class="bi bi-person-plus"></i> {{ __t('messages.flow.add_person') }}
                    </h3>
                    @if ($applicant && !$applicantIn)
                        <button type="button" class="xf-link-btn" id="add_myself"
                                data-seria="{{ $applicant['passport_seria'] }}"
                                data-number="{{ $applicant['passport_number'] }}"
                                data-pinfl="{{ $applicant['pinfl'] ?? '' }}">
                            <i class="bi bi-person-check"></i> {{ __t('messages.flow.add_myself') }}
                        </button>
                    @endif
                </div>

                <div class="xf-row" style="--xf-cols: 3">
                    <x-insurence.field
                        name="finder_seria"
                        id="find_seria"
                        :label="__('insurance.passport.series')"
                        :value="old('passport_seria')"
                        placeholder="AA"
                        maxlength="4"
                        autocomplete="off"
                        style="text-transform: uppercase"
                    />
                    <x-insurence.field
                        name="finder_number"
                        id="find_number"
                        :label="__('insurance.passport.number')"
                        :value="old('passport_number')"
                        placeholder="1234567"
                        maxlength="7"
                        inputmode="numeric"
                        autocomplete="off"
                    />
                    <x-insurence.field
                        name="finder_pinfl"
                        id="find_pinfl"
                        :label="__t('messages.flow.pinfl')"
                        :value="old('pinfl')"
                        placeholder="31234567890123"
                        maxlength="14"
                        inputmode="numeric"
                        autocomplete="off"
                    />
                </div>

                <div>
                    <button type="button" id="find_btn" class="xf-btn xf-btn--soft">
                        <i class="bi bi-search" id="find_btn_icon"></i>
                        <span id="find_btn_text">{{ __('messages.search') }}</span>
                    </button>
                </div>

                <p id="find_error" class="xf-field__error" role="alert" @unless ($addError) hidden @endunless>{{ $addError }}</p>

                <form method="POST" action="{{ route($key . '.addPerson', ['locale' => $locale]) }}" id="add_form" hidden>
                    @csrf
                    @foreach (['pinfl', 'passport_seria', 'passport_number', 'passport_issue_date', 'passport_issued_by', 'birth_date', 'firstname', 'lastname', 'middlename', 'address', 'region_id', 'district_id', 'phone'] as $field)
                        <input type="hidden" name="{{ $field }}" id="h_{{ $field }}">
                    @endforeach

                    <div class="xf-panel__body" style="padding: 0">
                        <x-insurence.found id="found_person" title="" text="" />

                        <div class="xf-field">
                            <span class="xf-field__label" id="sum_label">{{ __t('messages.flow.sum_per_person') }}</span>
                            <div class="xf-chips" role="group" aria-labelledby="sum_label">
                                @foreach ($flow['presets'] as $preset)
                                    <button type="button" class="xf-chip" data-sum="{{ $preset }}"
                                            aria-pressed="{{ $preset === $sumDefault ? 'true' : 'false' }}">
                                        {{ formatMoney($preset) }}
                                    </button>
                                @endforeach
                            </div>
                            <span class="xf-amount" id="sum_display">{{ formatMoney($sumDefault) }}</span>
                            <input type="range" name="sum_insured" id="sum_slider" class="xf-range"
                                   min="{{ $flow['min'] }}" max="{{ $flow['max'] }}" step="{{ $flow['step'] }}"
                                   value="{{ $sumDefault }}" aria-labelledby="sum_label">
                            <p class="xf-field__help">
                                {{ __t('messages.flow.amount_range_uzs', ['min' => formatMoney($flow['min']), 'max' => formatMoney($flow['max'])]) }}
                            </p>
                        </div>

                        <div class="xf-premium-line">
                            <span>{{ __t('messages.flow.premium_for_person') }}</span>
                            <b id="person_premium">—</b>
                        </div>

                        <div>
                            <button type="submit" class="xf-btn xf-btn--primary">
                                <i class="bi bi-plus-lg"></i> {{ __t('messages.flow.add_to_list') }}
                            </button>
                        </div>
                    </div>
                </form>
            </section>
        </div>

        <form method="POST" action="{{ route($key . '.confirmPersons', ['locale' => $locale]) }}">
            @csrf
            <x-insurence.actions
                :backUrl="route($key . '.index', ['locale' => $locale])"
                :submit="__t('messages.next_step')"
                :total="$premiumTotal"
                :disabled="!$hasPersons"
            />
        </form>
    </div>

    <x-slot:summary>
        <x-insurence.summary :premium="$premiumTotal" :items="$summaryItems" />
    </x-slot:summary>
</x-insurence.flow>
@endsection

@push('scripts')
<script>
(function () {
    var CSRF     = document.querySelector('meta[name="csrf-token"]').content;
    var CURRENCY = @json(__t('messages.currency'));

    function $(id) { return document.getElementById(id); }
    function money(n) { return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ' + CURRENCY; }

    // ── Find person ────────────────────────────────────────────────────────────
    var findBtn = $('find_btn');

    findBtn.addEventListener('click', async function () {
        var seria  = $('find_seria').value.trim().toUpperCase();
        var number = $('find_number').value.trim();
        var pinfl  = $('find_pinfl').value.trim();
        var err    = $('find_error');
        err.hidden = true;

        if (!seria || !number || pinfl.length !== 14) {
            err.textContent = @json(__t('messages.flow.person_fill'));
            err.hidden = false;
            return;
        }

        findBtn.disabled = true;
        $('find_btn_icon').className = 'spinner-border spinner-border-sm';
        $('find_btn_text').textContent = @json(__('messages.loading'));

        try {
            var res = await fetch(@json(route($key . '.findPerson', ['locale' => $locale])), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ passport_seria: seria, passport_number: number, pinfl: pinfl }),
            });
            var json = await res.json();

            if (!json.success) {
                err.textContent = json.message || @json(__('messages.person_not_found'));
                err.hidden = false;
                $('add_form').hidden = true;
                return;
            }

            var d = json.data;
            ['pinfl', 'passport_seria', 'passport_number', 'passport_issue_date', 'passport_issued_by', 'birth_date',
             'firstname', 'lastname', 'middlename', 'address', 'region_id', 'district_id', 'phone'].forEach(function (f) {
                $('h_' + f).value = d[f] ?? '';
            });

            var found = $('found_person');
            found.querySelector('[data-found="title"]').textContent = [d.lastname, d.firstname, d.middlename].filter(Boolean).join(' ');
            found.querySelector('[data-found="text"]').textContent  = d.passport_seria + ' ' + d.passport_number + ' · ' + (d.address || '');

            $('add_form').hidden = false;
            updatePremium();
        } catch (e) {
            err.textContent = @json(__('messages.error_occurred'));
            err.hidden = false;
        } finally {
            findBtn.disabled = false;
            $('find_btn_icon').className = 'bi bi-search';
            $('find_btn_text').textContent = @json(__('messages.search'));
        }
    });

    // ── "Add myself" fills the finder with the applicant's documents ──────────
    var myself = $('add_myself');
    if (myself) {
        myself.addEventListener('click', function () {
            $('find_seria').value  = this.dataset.seria;
            $('find_number').value = this.dataset.number;
            $('find_pinfl').value  = this.dataset.pinfl;
            findBtn.click();
        });
    }

    // ── Sum per person + live premium from the insurer's calculator ───────────
    var slider = $('sum_slider');
    var timer  = null;

    function setSum(val) {
        val = parseInt(val, 10);
        slider.value = val;
        $('sum_display').textContent = money(val);
        document.querySelectorAll('[data-sum]').forEach(function (chip) {
            chip.setAttribute('aria-pressed', parseInt(chip.dataset.sum, 10) === val ? 'true' : 'false');
        });
        updatePremium();
    }

    function updatePremium() {
        if ($('add_form').hidden) return;
        $('person_premium').textContent = @json(__t('messages.flow.calculating'));
        clearTimeout(timer);
        timer = setTimeout(async function () {
            try {
                var res = await fetch(@json(route($key . '.calculatePremium', ['locale' => $locale])), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ sum_insured: parseInt(slider.value, 10) }),
                });
                var json = await res.json();
                $('person_premium').textContent = json.success ? money(json.premium) : '—';
            } catch (e) {
                $('person_premium').textContent = '—';
            }
        }, 400);
    }

    slider.addEventListener('input', function () { setSum(this.value); });
    document.querySelectorAll('[data-sum]').forEach(function (chip) {
        chip.addEventListener('click', function () { setSum(this.dataset.sum); });
    });
})();
</script>
@endpush
