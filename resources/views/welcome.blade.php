@extends('layouts.app')
@section('title', __('messages.welcome'))

@push('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Unbounded:wght@600;800&display=swap">
    <link rel="stylesheet" href="{{ assetVersioned('assets/css/home.css') }}">
@endpush

@section('content')
@php
    $lang  = app()->getLocale();
    $phone = '(+998 71) 202-19-66';
    $tel   = 'tel:+998712021966';
    $name  = fn ($p) => $p->{'name_' . $lang} ?: $p->name_uz;
    $desc  = fn ($p) => $p->{'desc_' . $lang} ?: $p->desc_uz;
    $price = fn ($p) => isset($rates[$p->route])
        ? __t('messages.homepage.rate', ['rate' => $rates[$p->route]])
        : __t('messages.homepage.online_price');
    $tints = ['#f3f1fb', '#eef6f1', '#fdf3e6', '#eaf2fb', '#fbeef1', '#f1f5e9'];
@endphp

<div class="hp">

    {{-- ─── Hero ─────────────────────────────────────────────────────────── --}}
    <section class="hp-hero">
        <span class="hp-ring hp-ring--1" aria-hidden="true"></span>
        <span class="hp-ring hp-ring--2" aria-hidden="true"></span>
        <span class="hp-ring hp-ring--accent" aria-hidden="true"></span>
        <span class="hp-blob" aria-hidden="true"></span>

        <div class="hp-wrap hp-hero__grid">
            <div class="hp-hero__copy">
                <span class="hp-pill"><span class="hp-pill__dot"></span>{{ __t('messages.homepage.badge') }}</span>
                <h1 class="hp-hero__title">
                    {{ __t('messages.homepage.title_1') }}<br>
                    <span>{{ __t('messages.homepage.title_2') }}</span>
                </h1>
                <p class="hp-hero__lead">{{ __t('messages.homepage.lead') }}</p>
                <div class="hp-hero__actions">
                    <a href="#hp-quick" class="hp-btn hp-btn--accent hp-btn--lg">
                        {{ __t('messages.homepage.cta_buy') }}
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <a href="{{ route('claims.create', ['locale' => $lang]) }}" class="hp-btn hp-btn--ghost hp-btn--lg">{{ __t('messages.homepage.cta_claim') }}</a>
                </div>
            </div>

            {{-- Sample e-policy: illustration, the price is the site's own OSAGO calculation --}}
            <div class="hp-mock" aria-hidden="true">
                <div class="hp-mock__card">
                    <div class="hp-mock__row">
                        <span class="hp-mock__kind">{{ __t('messages.homepage.mock.kind') }}</span>
                        <span class="hp-chip hp-chip--ok">{{ __t('messages.homepage.mock.active') }}</span>
                    </div>
                    <div class="hp-mock__field">
                        <span>{{ __t('messages.homepage.mock.vehicle') }}</span>
                        <strong class="hp-mock__plate">01 A 123 BC</strong>
                    </div>
                    <div class="hp-mock__two">
                        <div class="hp-mock__field"><span>{{ __t('messages.homepage.mock.term') }}</span><b>{{ __t('messages.homepage.mock.months') }}</b></div>
                        <div class="hp-mock__field"><span>{{ __t('messages.homepage.mock.drivers') }}</span><b>{{ __t('messages.homepage.mock.limited') }}</b></div>
                    </div>
                    <div class="hp-mock__foot">
                        <div class="hp-mock__field"><span>{{ __t('messages.homepage.mock.sum') }}</span><strong>{{ __t('messages.homepage.mock.sum_value') }}</strong></div>
                        <span class="hp-mock__qr">@for ($i = 0; $i < 25; $i++)<i class="{{ in_array($i, [2, 6, 8, 10, 14, 16, 18, 22], true) ? 'off' : '' }}"></i>@endfor</span>
                    </div>
                </div>
                <div class="hp-mock__price">
                    <span>{{ __t('messages.homepage.mock.price') }}</span>
                    <strong>{{ formatMoney($mock) }}</strong>
                </div>
                <div class="hp-mock__toast">
                    <span class="hp-mock__check"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                    <span><b>{{ __t('messages.homepage.mock.paid') }}</b><small>{{ __t('messages.homepage.mock.paid_sub') }}</small></span>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── Quick quote ──────────────────────────────────────────────────── --}}
    @if ($quick->isNotEmpty())
        <section class="hp-wrap hp-quick-wrap" id="hp-quick">
            <div class="hp-quick" data-hp-tabs>
                <div class="hp-quick__tabs" role="tablist" aria-label="{{ __t('messages.homepage.quick_label') }}">
                    @foreach ($quick as $p)
                        <button type="button" role="tab" id="hp-tab-{{ $p->route }}" aria-controls="hp-panel-{{ $p->route }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? '0' : '-1' }}" class="hp-quick__tab">
                            <i class="{{ $p->icon }}" aria-hidden="true"></i>{{ $name($p) }}
                        </button>
                    @endforeach
                </div>

                @foreach ($quick as $p)
                    <div class="hp-quick__panel" role="tabpanel" id="hp-panel-{{ $p->route }}" aria-labelledby="hp-tab-{{ $p->route }}" @unless ($loop->first) hidden @endunless>
                        @if ($p->route === 'osago')
                            <form class="hp-quick__form" method="get" action="{{ $p->url() }}">
                                <label class="hp-field hp-field--wide">{{ __t('messages.gov_number') }}
                                    <input type="text" name="gov_number" placeholder="01A123BC" maxlength="10" autocomplete="off" required class="hp-input hp-input--plate">
                                </label>
                                <label class="hp-field">{{ __t('messages.tech_passport_series') }}
                                    <input type="text" name="tech_passport_seria" placeholder="AAF" maxlength="3" autocomplete="off" required class="hp-input hp-input--upper">
                                </label>
                                <label class="hp-field">{{ __t('messages.tech_passport_number') }}
                                    <input type="text" name="tech_passport_number" placeholder="1234567" maxlength="7" inputmode="numeric" autocomplete="off" required class="hp-input">
                                </label>
                                <button type="submit" class="hp-btn hp-btn--brand hp-quick__submit">{{ __t('messages.homepage.calc') }} →</button>
                            </form>
                        @else
                            <div class="hp-quick__info">
                                <p>{{ $desc($p) }}</p>
                                <span class="hp-quick__price">{{ $price($p) }}</span>
                                <a href="{{ $p->url() }}" class="hp-btn hp-btn--brand hp-quick__submit">{{ __t('messages.homepage.apply') }} →</a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Figures (admin: Tizim → Sayt sozlamalari) ────────────────────── --}}
    @if ($stats)
        <section class="hp-wrap hp-stats" style="--hp-stats: {{ count($stats) }}">
            @foreach ($stats as $stat)
                <div class="hp-stat">
                    <strong>{{ $stat['value'] }}</strong>
                    <span>{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </section>
    @endif

    {{-- ─── Products ─────────────────────────────────────────────────────── --}}
    <section class="hp-wrap hp-section" id="products">
        <h2 class="hp-h2">{{ __t('messages.homepage.products_title') }}</h2>

        <div class="hp-bento">
            @if ($osago)
                <a href="{{ $osago->cardUrl() }}" class="hp-tile hp-tile--hero">
                    <span class="hp-tile__ring" aria-hidden="true"></span>
                    <span class="hp-tile__top">
                        <span class="hp-tag">{{ __t('messages.homepage.mandatory') }}</span>
                        <i class="{{ $osago->icon }} hp-tile__big-icon" aria-hidden="true"></i>
                    </span>
                    <span class="hp-tile__body">
                        <span class="hp-tile__name">{{ $name($osago) }}</span>
                        <span class="hp-tile__text">{{ $desc($osago) }}</span>
                        <span class="hp-tile__cta-row">
                            <span class="hp-tile__price">
                                {{ __t('messages.homepage.from', ['amount' => number_format($osagoFrom, 0, '.', ' ')]) }}
                                <small>{{ __t('messages.homepage.from_note') }}</small>
                            </span>
                            <span class="hp-btn hp-btn--white">{{ __t('messages.homepage.apply') }} →</span>
                        </span>
                    </span>
                </a>
            @endif

            @php
                // Tiles left over in the last row of the 4-column grid stretch to fill it
                $free   = $osago ? 4 : 0;
                $rest   = max(0, $tiles->count() - $free) % 4;
                $spans  = [1 => [4], 2 => [2, 2], 3 => [2, 1, 1]][$rest] ?? [];
                $spanAt = fn (int $i) => $spans[$i - ($tiles->count() - $rest)] ?? 1;
            @endphp
            @foreach ($tiles as $p)
                <a href="{{ $p->cardUrl() }}" class="hp-tile hp-tile--span-{{ $spanAt($loop->index) }} {{ $loop->last && $loop->count % 2 ? 'hp-tile--odd-last' : '' }}" style="--hp-tint: {{ $tints[$loop->index % count($tints)] }}">
                    <span class="hp-tile__top">
                        <span class="hp-tile__icon"><i class="{{ $p->icon }}" aria-hidden="true"></i></span>
                        <span class="hp-tile__rate">{{ $price($p) }}</span>
                    </span>
                    <span class="hp-tile__body">
                        <span class="hp-tile__name">{{ $name($p) }}</span>
                        <span class="hp-tile__text">{{ $desc($p) }}</span>
                        <span class="hp-tile__go">{{ $p->hasInfo() ? __t('messages.product_page.more') : __t('messages.homepage.apply') }} →</span>
                    </span>
                </a>
            @endforeach

            @if ($business->isNotEmpty())
                <div class="hp-tile hp-tile--business">
                    <div class="hp-tile__body">
                        <span class="hp-kicker">{{ __t('messages.homepage.business') }}</span>
                        <span class="hp-tile__name">{{ $business->map($name)->join(' · ') }}</span>
                        <span class="hp-tile__text">{{ __t('messages.homepage.business_text') }}</span>
                    </div>
                    <div class="hp-tile__buttons">
                        @foreach ($business as $p)
                            <a href="{{ $p->cardUrl() }}" class="hp-btn {{ $loop->first ? 'hp-btn--accent' : 'hp-btn--ghost' }}">{{ $name($p) }} →</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- ─── How it works ─────────────────────────────────────────────────── --}}
    <section class="hp-steps">
        <div class="hp-wrap">
            <h2 class="hp-h2">{{ __t('messages.homepage.steps_title') }}</h2>
            <ol class="hp-steps__list">
                @foreach (__('messages.homepage.steps') as $i => $step)
                    <li class="{{ $loop->last ? 'is-last' : '' }}">
                        <span class="hp-steps__num">0{{ $i }}</span>
                        <strong>{{ $step['title'] }}</strong>
                        <span>{{ $step['text'] }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ─── Claims ───────────────────────────────────────────────────────── --}}
    <section class="hp-claims">
        <span class="hp-ring hp-ring--claims" aria-hidden="true"></span>
        <div class="hp-wrap hp-claims__grid">
            <div class="hp-claims__copy">
                <span class="hp-kicker">{{ __t('messages.homepage.claims_kicker') }}</span>
                <h2 class="hp-h2 hp-h2--light">{{ __t('messages.homepage.claims_title_1') }}<br>{{ __t('messages.homepage.claims_title_2') }}</h2>
                <p>{{ __t('messages.homepage.claims_text') }}</p>
                <div class="hp-hero__actions">
                    <a href="{{ route('claims.create', ['locale' => $lang]) }}" class="hp-btn hp-btn--accent hp-btn--lg">{{ __t('messages.homepage.claims_cta') }}</a>
                    <a href="{{ $tel }}" class="hp-btn hp-btn--ghost hp-btn--lg">{{ $phone }}</a>
                </div>
            </div>

            <div class="hp-claim-mock" aria-hidden="true">
                <div class="hp-mock__row">
                    <strong class="hp-claim-mock__title">{{ __t('messages.homepage.claim_mock.title') }}</strong>
                    <span class="hp-chip hp-chip--wait">{{ __t('messages.homepage.claim_mock.status') }}</span>
                </div>
                <ol>
                    <li class="is-done"><i>✓</i><span><b>{{ __t('messages.homepage.claim_mock.s1') }}</b><small>{{ __t('messages.homepage.claim_mock.s1_sub') }}</small></span></li>
                    <li class="is-now"><i>2</i><span><b>{{ __t('messages.homepage.claim_mock.s2') }}</b><small>{{ __t('messages.homepage.claim_mock.s2_sub') }}</small></span></li>
                    <li><i>3</i><span><b>{{ __t('messages.homepage.claim_mock.s3') }}</b><small>{{ __t('messages.homepage.claim_mock.s3_sub') }}</small></span></li>
                </ol>
            </div>
        </div>
    </section>

    {{-- ─── Self-service ─────────────────────────────────────────────────── --}}
    <section class="hp-wrap hp-self">
        <a href="{{ route('my-policies', ['locale' => $lang]) }}" class="hp-self__card hp-self__card--accent">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="3"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
            <strong>{{ __t('messages.homepage.self.policies') }}</strong>
            <span>{{ __t('messages.homepage.self.policies_text') }}</span>
        </a>
        <a href="{{ route('claims.status', ['locale' => $lang]) }}" class="hp-self__card">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <strong>{{ __t('messages.homepage.self.status') }}</strong>
            <span>{{ __t('messages.homepage.self.status_text') }}</span>
        </a>
        <a href="{{ route('callback', ['locale' => $lang]) }}" class="hp-self__card">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
            <strong>{{ __t('messages.homepage.self.callback') }}</strong>
            <span>{{ __t('messages.homepage.self.callback_text') }}</span>
        </a>
    </section>

    {{-- ─── FAQ + documents ──────────────────────────────────────────────── --}}
    <section class="hp-wrap hp-faq">
        <div class="hp-faq__side">
            <h2 class="hp-h2">{{ __t('messages.homepage.faq_title') }}</h2>
            <p>{{ __t('messages.homepage.faq_text') }}</p>
            <div class="hp-docs">
                @foreach ($documents as $key => $url)
                    <a href="{{ $url }}">{{ __t('messages.homepage.docs.' . $key) }} <span aria-hidden="true">→</span></a>
                @endforeach
            </div>
        </div>
        <div class="hp-faq__list">
            @foreach (__('messages.homepage.faq') as $item)
                <details @if ($loop->first) open @endif>
                    <summary>{{ $item['q'] }}</summary>
                    <p>{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    {{-- Phones: the two main actions stay under the thumb --}}
    <div class="hp-sticky">
        <a href="#hp-quick" class="hp-btn hp-btn--brand">{{ __t('messages.homepage.cta_buy') }}</a>
        <a href="{{ route('claims.create', ['locale' => $lang]) }}" class="hp-btn hp-btn--soft">{{ __t('messages.homepage.claims_cta') }}</a>
    </div>
</div>

@push('scripts')
<script>
    // Quick-quote tabs: click / arrow keys switch the panel (WAI-ARIA tabs pattern)
    document.querySelectorAll('[data-hp-tabs]').forEach(function (root) {
        var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
        function select(tab, focus) {
            tabs.forEach(function (t) {
                var on = t === tab;
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.tabIndex = on ? 0 : -1;
                document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
            });
            if (focus) tab.focus();
        }
        tabs.forEach(function (tab, i) {
            tab.addEventListener('click', function () { select(tab, false); });
            tab.addEventListener('keydown', function (e) {
                var next = e.key === 'ArrowRight' ? i + 1 : e.key === 'ArrowLeft' ? i - 1 : null;
                if (next === null) return;
                e.preventDefault();
                select(tabs[(next + tabs.length) % tabs.length], true);
            });
        });
    });
</script>
@endpush
@endsection
