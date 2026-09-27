{{--
    Sum chips + slider + start/end dates, shared by flow step 2 pages.
    Expects: $flow, $amount, $startDate, $calculation, $revealed (bool)
--}}
            {{-- ── Sum + dates (shown once the insured object is found) ── --}}
            <div id="calc_section" class="xf-panel__body" style="padding: 0" @unless ($revealed) hidden @endunless>

                <div class="xf-field">
                    <span class="xf-field__label" id="amt_label">{{ __('messages.insurance_sum') }}</span>
                    <div class="xf-chips" role="group" aria-labelledby="amt_label">
                        @foreach ($flow['presets'] as $preset)
                            <button type="button" class="xf-chip" data-amount="{{ $preset }}"
                                    aria-pressed="{{ $preset === $amount ? 'true' : 'false' }}">
                                {{ $preset / 1000000 }} {{ __t('messages.flow.mln') }}
                            </button>
                        @endforeach
                    </div>
                    <span class="xf-amount" id="amt_display">{{ formatMoney($amount) }}</span>
                    <input type="range" id="amt_slider" class="xf-range" min="{{ $flow['min'] }}" max="{{ $flow['max'] }}" step="{{ $flow['step'] ?? 5000000 }}"
                           value="{{ $amount }}" aria-labelledby="amt_label">
                    <input type="hidden" name="insurance_amount" id="h_insurance_amount" value="{{ $amount }}">
                    @error('insurance_amount')
                        <p class="xf-field__error">{{ $message }}</p>
                    @else
                        <p class="xf-field__help">{{ __t('messages.flow.amount_range', ['min' => $flow['min'] / 1000000, 'max' => $flow['max'] / 1000000]) }}</p>
                    @enderror
                </div>

                <div class="xf-row">
                    <x-insurence.field
                        name="payment_start_date"
                        id="start_date"
                        type="date"
                        :label="__('messages.start_date')"
                        :value="$startDate"
                        :min="now()->format('Y-m-d')"
                        required
                    />
                    <x-insurence.field
                        name="payment_end_date_display"
                        id="end_date"
                        type="date"
                        :label="__('messages.end_date')"
                        :value="$calculation['payment_end_date'] ?? null"
                        :help="__t('messages.flow.term_auto')"
                        readonly
                        tabindex="-1"
                    />
                </div>
            </div>
