@props([
    'premium' => null,   // int (UZS) or null when not yet calculated
    'rate'    => null,   // e.g. '0,5'
    'items'   => [],     // [key => [label, value|null]]
])

<aside class="xf-panel xf-summary" aria-label="{{ __t('messages.flow.your_policy') }}">
    <div class="xf-summary__top">
        <small>{{ __t('messages.flow.to_pay') }}</small>
        <div id="sidebar_premium" class="xf-summary__premium {{ $premium ? '' : 'is-empty' }}"
             data-empty="{{ __t('messages.flow.premium_pending') }}">
            {{ $premium ? formatMoney($premium) : __t('messages.flow.premium_pending') }}
        </div>
        @if ($rate)
            <div class="xf-summary__rate">{{ __t('messages.flow.rate_of_sum', ['rate' => $rate]) }}</div>
        @endif
    </div>

    <ul class="xf-summary__list">
        @foreach ($items as $key => [$label, $value])
            <li>
                <span>{{ $label }}</span>
                <b data-summary="{{ $key }}" class="{{ filled($value) ? '' : 'is-pending' }}">
                    {{ filled($value) ? $value : __t('messages.flow.not_entered') }}
                </b>
            </li>
        @endforeach
    </ul>

    <div class="xf-summary__help">
        <i class="bi bi-telephone"></i>
        <span>{{ __t('messages.flow.help') }} <a href="tel:+998712021966">(+998 71) 202-19-66</a></span>
    </div>
</aside>
