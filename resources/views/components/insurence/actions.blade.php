@props([
    'backUrl' => null,
    'submit',
    'total'   => null,   // int (UZS); shown in the mobile sticky bar
    'form'    => null,   // id of the form the submit button belongs to
])

<div class="xf-actions">
    @if ($backUrl)
        <a href="{{ $backUrl }}" class="xf-btn xf-btn--ghost">
            <i class="bi bi-arrow-left"></i> {{ __t('messages.back') }}
        </a>
    @else
        <span></span>
    @endif

    <div class="xf-actions__total">
        <small>{{ __t('messages.flow.to_pay') }}</small>
        <b data-summary="total">{{ $total ? formatMoney($total) : '—' }}</b>
    </div>

    <button type="submit" @if ($form) form="{{ $form }}" @endif {{ $attributes->class('xf-btn xf-btn--primary') }}>
        {{ $submit }} <i class="bi bi-arrow-right"></i>
    </button>
</div>
